<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Services\DokuService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DokuProductionConfigTest extends TestCase
{
    public function test_production_checkout_uses_production_host_and_never_logs_signing_trace(): void
    {
        config(['doku.env' => 'production', 'doku.base_url' => 'https://api.doku.com',
            'doku.client_id' => 'test-production-client', 'doku.secret_key' => 'test-production-secret',
            'doku.signature_debug' => true]);
        Http::preventStrayRequests();
        Log::spy();
        $user = new User(['name' => 'Test Owner', 'email' => 'owner@example.com']);
        $user->id = 1;
        $user->setRelation('business', new Business());
        $order = new SubscriptionOrder(['order_id' => 'SUB-PRODUCTION-TEST', 'request_id' => 'fixed-request',
            'plan_id' => 1, 'plan_name' => 'Premium', 'billing_cycle' => 'monthly', 'amount' => 49000]);
        Http::fake(function ($request) use ($order) {
            $this->assertSame('https://api.doku.com/checkout/v1/payment', $request->url());
            $this->assertSame(['test-production-client'], $request->header('Client-Id'));
            $this->assertSame(['fixed-request'], $request->header('Request-Id'));
            $timestamp = $request->header('Request-Timestamp')[0];
            $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/', $timestamp);
            $digest = base64_encode(hash('sha256', $request->body(), true));
            $canonical = "Client-Id:test-production-client\nRequest-Id:fixed-request\nRequest-Timestamp:{$timestamp}\nRequest-Target:/checkout/v1/payment\nDigest:{$digest}";
            $expected = 'HMACSHA256='.base64_encode(hash_hmac('sha256', $canonical, 'test-production-secret', true));
            $this->assertSame($expected, $request->header('Signature')[0]);
            return Http::response(['response' => [
                'order' => ['invoice_number' => $order->order_id, 'amount' => 49000],
                'payment' => ['url' => 'https://jokul.doku.com/checkout-link-v2/test',
                    'expired_date' => now()->addHour()->setTimezone('Asia/Jakarta')->format('YmdHis')],
            ]]);
        });
        $result = app(DokuService::class)->createPayment($order, $user);
        $this->assertSame('https://jokul.doku.com/checkout-link-v2/test', $result['payment_url']);
        Http::assertSentCount(1);
        Log::shouldNotHaveReceived('debug');
    }

    public function test_production_rejects_sandbox_base_url_before_sending_request(): void
    {
        config(['doku.env' => 'production', 'doku.base_url' => 'https://api-sandbox.doku.com',
            'doku.client_id' => 'test-client', 'doku.secret_key' => 'test-secret']);
        Http::preventStrayRequests();
        $this->expectException(ValidationException::class);
        app(DokuService::class)->ensureConfigured();
    }

    public function test_sandbox_can_be_selected_in_local_environment_without_production_request(): void
    {
        $this->app->instance('env', 'local');
        config(['doku.env'=>'sandbox', 'doku.base_url'=>'https://api-sandbox.doku.com',
            'doku.client_id'=>'test-sandbox-client', 'doku.secret_key'=>'test-sandbox-secret']);
        Http::fake();
        app(DokuService::class)->ensureConfigured();
        Http::assertNothingSent();
    }

    public function test_placeholder_and_api_keys_are_rejected_without_a_request_or_secret_in_errors(): void
    {
        Http::preventStrayRequests();
        Http::fake();
        foreach (['', '   ', 'ISI_SECRET_KEY_PRODUCTION', 'YOUR_SECRET_KEY', 'doku_key_not_a_checkout_secret'] as $secret) {
            config(['doku.env'=>'production', 'doku.base_url'=>'https://api.doku.com',
                'doku.client_id'=>'test-production-client', 'doku.secret_key'=>$secret]);
            try {
                app(DokuService::class)->ensureConfigured();
                $this->fail('Invalid credentials must be rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('payment', $exception->errors());
                if (trim($secret) !== '') $this->assertStringNotContainsString($secret, $exception->getMessage());
            }
        }
        Http::assertNothingSent();
    }
}
