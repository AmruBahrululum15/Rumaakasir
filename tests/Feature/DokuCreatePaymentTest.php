<?php

namespace Tests\Feature;

use App\Models\SubscriptionOrder;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class DokuCreatePaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['doku.subscription_gateway' => 'doku', 'doku.env' => 'sandbox', 'doku.base_url' => 'https://api-sandbox.doku.com',
            'doku.client_id' => 'private-client', 'doku.secret_key' => 'private-secret']);
        Http::preventStrayRequests();
        $owner = User::factory()->create();
        $owner->assignRole(Role::findOrCreate('Atasan', 'web'));
        $this->actingAs($owner);
    }

    public function test_real_signature_error_is_logged_safely_and_explained_to_owner(): void
    {
        Log::spy();
        Http::fake(['api-sandbox.doku.com/checkout/v1/payment' => Http::response([
            'error' => ['code' => 'invalid_signature', 'message' => 'Invalid Header Signature'],
            'client_id' => 'private-client', 'secret_key' => 'private-secret',
            'headers' => ['Signature' => 'HMACSHA256=sensitive'],
            'customer' => ['email' => 'private@example.com'],
        ], 400)]);
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'yearly'])->assertRedirect();
        $order = SubscriptionOrder::firstOrFail();
        $this->assertSame('failed', $order->status);
        $this->assertNull($order->payment_url);
        $this->assertNull($order->paid_at);
        $this->assertSame('free', $order->business->subscription_plan);
        $this->get(route('subscriptions.result', $order->order_id))->assertOk()
            ->assertSee('Pembayaran belum dapat diproses.')->assertDontSee('log aplikasi')
            ->assertDontSee('private-secret')->assertDontSee('invalid_signature');
        Log::shouldHaveReceived('warning')->once()->withArgs(function ($message, $context) use ($order) {
            $serialized = json_encode($context);
            return $message === 'DOKU subscription checkout error'
                && $context['http_status'] === 400 && $context['error_code'] === 'invalid_signature'
                && $context['error_message'] === 'Invalid Header Signature'
                && $context['order_id'] === $order->order_id && $context['request_id'] === $order->request_id
                && str_contains($context['response_body'], 'invalid_signature')
                && ! str_contains($serialized, 'private-secret') && ! str_contains($serialized, 'private-client')
                && ! str_contains($serialized, 'HMACSHA256=sensitive') && ! str_contains($serialized, 'private@example.com');
        });
    }

    public function test_order_is_creating_until_doku_returns_a_valid_checkout_url(): void
    {
        config(['doku.signature_debug' => true]);
        Log::spy();
        Http::fake(function ($request) {
            $this->assertSame('creating', SubscriptionOrder::firstOrFail()->status);
            $body = $request->body();
            $id = $request->header('Request-Id')[0];
            $time = $request->header('Request-Timestamp')[0];
            $canonical = "Client-Id:private-client\nRequest-Id:{$id}\nRequest-Timestamp:{$time}\nRequest-Target:/checkout/v1/payment\nDigest:".base64_encode(hash('sha256', $body, true));
            $this->assertSame('HMACSHA256='.base64_encode(hash_hmac('sha256', $canonical, 'private-secret', true)), $request->header('Signature')[0]);
            Log::shouldHaveReceived('debug')->once()->withArgs(function ($message, $context) use ($request, $body, $canonical) {
                return $message === 'DOKU Sandbox signing trace'
                    && $context['raw_request_body'] === $body
                    && $context['body_digest'] === base64_encode(hash('sha256', $body, true))
                    && $context['canonical'] === str_replace('private-client', '[MASKED]', $canonical)
                    && $context['signature'] === $request->header('Signature')[0]
                    && ! str_contains(json_encode($context), 'private-secret');
            });
            $this->assertSame('https://api-sandbox.doku.com/checkout/v1/payment', $request->url());
            $this->assertSame(499000, $request['order']['amount']);
            return Http::response(['response' => [
                'order' => ['invoice_number' => $request['order']['invoice_number'], 'amount' => '499000', 'session_id' => 'checkout-ref'],
                'payment' => ['url' => 'https://sandbox.doku.com/checkout-link-v2/test', 'expired_date' => now()->addHour()->setTimezone('Asia/Jakarta')->format('YmdHis')],
            ]]);
        });
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'yearly'])
            ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/test');
        $order = SubscriptionOrder::firstOrFail();
        $this->assertSame('pending', $order->status);
        $this->assertSame('checkout-ref', $order->doku_reference);
        $this->assertNotNull($order->payment_url);
        $this->assertNull($order->paid_at);
        $this->assertSame('free', $order->business->subscription_plan);
    }

    public function test_uncertain_response_never_becomes_pending_or_duplicates_a_remote_order(): void
    {
        Http::fake(['*' => Http::response('Upstream unavailable', 503)]);
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        $order = SubscriptionOrder::firstOrFail();
        $this->assertSame('create_unknown', $order->status);
        $this->get(route('subscriptions.result', $order->order_id))->assertOk()->assertSee('Pembayaran sedang diperiksa.');
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        Http::assertSentCount(1);
        $this->assertDatabaseCount('subscription_orders', 1);
    }

    public function test_bad_expiry_is_logged_and_does_not_create_a_pending_payment(): void
    {
        Log::spy();
        Http::fake(fn ($request) => Http::response(['response' => [
            'order' => ['invoice_number' => $request['order']['invoice_number'], 'amount' => $request['order']['amount']],
            'payment' => ['url' => 'https://sandbox.doku.com/checkout-link-v2/test', 'expired_date' => 'invalid'],
        ]]));
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        $this->assertSame('create_unknown', SubscriptionOrder::firstOrFail()->status);
        Log::shouldHaveReceived('warning')->withArgs(fn ($message, $context) =>
            $context['reason'] === 'invalid_checkout_expiry' && $context['http_status'] === 200)->once();
    }
}
