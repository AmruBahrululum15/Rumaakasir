<?php

namespace App\Services;

use App\Exceptions\DokuCheckoutException;
use App\Models\SubscriptionOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class DokuService
{
    public function ensureConfigured(): void
    {
        $base = rtrim((string) config('doku.base_url'), '/');
        $clientId = (string) config('doku.client_id');
        $secretKey = (string) config('doku.secret_key');
        $expected = match (config('doku.env')) {
            'sandbox' => 'https://api-sandbox.doku.com',
            'production' => 'https://api.doku.com',
            default => null,
        };
        if (! $expected || $base !== $expected || trim($clientId) === '' || trim($secretKey) === ''
            || preg_match('/^(ISI_|YOUR_|CHANGE_ME)/i', trim($clientId))
            || preg_match('/^(ISI_|YOUR_|CHANGE_ME|doku_key_)/i', trim($secretKey))) {
            throw ValidationException::withMessages(['payment' => 'Pembayaran DOKU belum dikonfigurasi. Hubungi pengelola platform.']);
        }
    }

    public function signature(string $body, string $requestId, string $timestamp, string $target): string
    {
        return $this->signingComponents($body, $requestId, $timestamp, $target)['signature'];
    }

    private function signingComponents(string $body, string $requestId, string $timestamp, string $target): array
    {
        $digest = base64_encode(hash('sha256', $body, true));
        $components = 'Client-Id:'.config('doku.client_id')."\nRequest-Id:{$requestId}\nRequest-Timestamp:{$timestamp}\nRequest-Target:{$target}\nDigest:{$digest}";
        return ['digest' => $digest, 'canonical' => $components,
            'signature' => 'HMACSHA256='.base64_encode(hash_hmac('sha256', $components, (string) config('doku.secret_key'), true))];
    }

    public function verifyNotification(Request $request): bool
    {
        if (! config('doku.secret_key') || ! config('doku.client_id')) {
            return false;
        }
        $id = (string) $request->header('Request-Id');
        $timestamp = (string) $request->header('Request-Timestamp');
        if (! preg_match('/^[^\r\n]{1,128}$/D', $id)
            || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D', $timestamp)
            || ! hash_equals((string) config('doku.client_id'), (string) $request->header('Client-Id'))) {
            return false;
        }
        // Raw bytes, including whitespace, are signed. Retried notifications may
        // legitimately be old; paid_at + row locks prevent replayed activation.
        return hash_equals($this->signature($request->getContent(), $id, $timestamp, $request->getRequestUri()), (string) $request->header('Signature'));
    }

    public function validCheckoutUrl(?string $url): bool
    {
        $parts = parse_url($url ?? '');
        return $parts && ($parts['scheme'] ?? '') === 'https'
            && ! isset($parts['user']) && ! isset($parts['pass']) && ! isset($parts['port'])
            && str_ends_with(strtolower($parts['host'] ?? ''), '.doku.com');
    }

    public function createPayment(SubscriptionOrder $order, User $user): array
    {
        $this->ensureConfigured();
        $target = '/checkout/v1/payment';
        $returnUrl = route('subscriptions.result', $order->order_id);
        $phone = preg_replace('/\D/', '', $user->no_telepon ?: $user->business?->phone ?: '');
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }
        $body = json_encode([
            'order' => [
                'amount' => $order->amount, 'invoice_number' => $order->order_id, 'currency' => 'IDR',
                'callback_url' => $returnUrl, 'callback_url_result' => $returnUrl,
                'callback_url_cancel' => $returnUrl, 'auto_redirect' => true,
                'line_items' => [['id' => (string) $order->plan_id, 'name' => $order->plan_name.' '.$order->billing_cycle, 'quantity' => 1, 'price' => $order->amount]],
            ],
            'payment' => ['payment_due_date' => (int) config('doku.payment_due_minutes'), 'type' => 'SALE'],
            'customer' => array_filter(['id' => (string) $user->id, 'name' => $user->name, 'email' => $user->email, 'phone' => $phone, 'country' => 'ID']),
            'additional_info' => ['override_notification_url' => config('doku.notification_url') ?: route('doku.subscription.notification')],
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        // Generate once, after serialization; sign and send this exact UTC timestamp.
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');
        $signing = $this->signingComponents($body, $order->request_id, $timestamp, $target);
        $trace = [
            'client_id'=>'[MASKED]', 'request_timestamp'=>$timestamp, 'request_target'=>$target,
            'body_digest'=>$signing['digest'],
            'canonical'=>'Client-Id:[MASKED]'.substr($signing['canonical'], strlen('Client-Id:'.config('doku.client_id'))),
            'signature'=>'[MASKED]',
        ];
        // Opt-in local Sandbox diagnostics only. Never include the shared key.
        if (config('doku.signature_debug') && config('doku.env') === 'sandbox' && app()->environment('local', 'testing')) {
            Log::debug('DOKU Sandbox signing trace', [
                'client_id' => '[MASKED]', 'request_id' => $order->request_id,
                'request_timestamp' => $timestamp, 'request_target' => $target,
                'raw_request_body' => $body, 'body_digest' => $signing['digest'],
                'canonical' => 'Client-Id:[MASKED]'.substr($signing['canonical'], strlen('Client-Id:'.config('doku.client_id'))),
                'signature' => $signing['signature'],
            ]);
        }
        try {
            $response = Http::connectTimeout(10)->timeout(30)->withoutRedirecting()
            ->withHeaders([
                'Client-Id' => config('doku.client_id'), 'Request-Id' => $order->request_id,
                'Request-Timestamp' => $timestamp,
                'Signature' => $signing['signature'],
            ])->withBody($body, 'application/json')->post(rtrim(config('doku.base_url'), '/').$target);
        } catch (ConnectionException $exception) {
            $this->logCheckoutError($order, 'connection_error', trace: $trace);
            throw new DokuCheckoutException();
        }
        $redact = [$user->name, $user->email, $phone];
        if (! $response->successful()) {
            $this->logCheckoutError($order, 'api_rejected', $response, $redact, $trace);
            // Inspect the structured DOKU error as well as HTTP status. A proxy
            // failure, conflict or timeout does not prove the remote order failed.
            $code = $response->json('error.code') ?? $response->json('error_code');
            throw new DokuCheckoutException(is_string($code) && $code !== ''
                && in_array($response->status(), [400, 401, 403, 422], true));
        }
        $url = $response->json('response.payment.url');
        if (! is_string($url) || ! $this->validCheckoutUrl($url)
            || $response->json('response.order.invoice_number') !== $order->order_id
            || ! self::amountMatches($response->json('response.order.amount'), $order->amount)) {
            $this->logCheckoutError($order, 'invalid_checkout_response', $response, $redact, $trace);
            throw new DokuCheckoutException();
        }
        try {
            $expiry = Carbon::createFromFormat('!YmdHis', (string) $response->json('response.payment.expired_date'), 'Asia/Jakarta');
        } catch (\Throwable $exception) {
            $expiry = null;
        }
        if (! $expiry || ! $expiry->isFuture()) {
            $this->logCheckoutError($order, 'invalid_checkout_expiry', $response, $redact, $trace);
            throw new DokuCheckoutException();
        }
        Log::info('DOKU subscription checkout created', [
            'order_id' => $order->order_id, 'request_id' => $order->request_id,
            'http_status' => $response->status(),
        ]);
        return [
            'payment_url' => $url,
            'doku_reference' => is_string($response->json('response.order.session_id')) ? $response->json('response.order.session_id') : null,
            'payment_due_at' => $expiry->setTimezone(config('app.timezone')),
        ];
    }

    private function logCheckoutError(SubscriptionOrder $order, string $reason, ?Response $response = null, array $redact = [], array $trace = []): void
    {
        $redact = array_filter([config('doku.client_id'), config('doku.secret_key'), ...$redact], fn ($value) => is_string($value) && $value !== '');
        $sanitize = function ($value, string $key = '') use (&$sanitize, $redact) {
            if (preg_match('/secret|client.?id|signature|authorization|password|token|customer|email|phone|address|url|headers/i', $key)) {
                return '[REDACTED]';
            }
            if (is_array($value)) {
                foreach ($value as $childKey => $child) {
                    $value[$childKey] = $sanitize($child, (string) $childKey);
                }
                return $value;
            }
            return is_string($value)
                ? substr(preg_replace('/HMACSHA256=[A-Za-z0-9+\/=]+/', '[REDACTED]', str_replace($redact, '[REDACTED]', $value)), 0, 2000)
                : $value;
        };
        $body = $response ? $sanitize($response->json()) : null;
        Log::warning('DOKU subscription checkout error', [
            ...$trace,
            'order_id' => $order->order_id, 'request_id' => $order->request_id,
            'http_status' => $response?->status(), 'reason' => $reason,
            'error_code' => data_get($body, 'error.code') ?? data_get($body, 'error_code'),
            'error_message' => data_get($body, 'error.message') ?? data_get($body, 'message'),
            // Never dump arbitrary HTML, transport exceptions, or request headers.
            'response_body' => $response ? ($body === null ? '[Non-JSON body omitted]' : substr(json_encode($body, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE), 0, 8000)) : null,
        ]);
    }

    public static function amountMatches(mixed $amount, int $expected): bool
    {
        return (is_string($amount) || is_int($amount) || is_float($amount))
            && preg_match('/^\d{1,12}(?:\.0{1,2})?$/D', (string) $amount)
            && (int) $amount === $expected;
    }
}
