<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Services\DokuService;
use App\Services\PackageAccess;
use App\Tenancy\TenantContext;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class DokuSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['doku.subscription_gateway'=>'doku', 'doku.client_id' => 'test-client', 'doku.secret_key' => 'test-secret', 'doku.env' => 'sandbox', 'doku.base_url' => 'https://api-sandbox.doku.com']);
        Http::preventStrayRequests();
        Http::fake(['api-sandbox.doku.com/*' => fn (ClientRequest $request) => Http::response([
            'response' => [
                'order' => ['invoice_number' => $request['order']['invoice_number'], 'amount' => (string) $request['order']['amount'], 'session_id' => 'session-test'],
                'payment' => ['url' => 'https://sandbox.doku.com/checkout-link-v2/test', 'expired_date' => now()->addHour()->setTimezone('Asia/Jakarta')->format('YmdHis')],
            ],
        ])]);
    }

    private function owner(string $role = 'Atasan'): User
    {
        $user = User::factory()->create(['no_telepon' => '081234567890']);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        return $user;
    }

    private function purchase(User $user, string $cycle = 'monthly'): SubscriptionOrder
    {
        $this->actingAs($user)->post(route('subscriptions.store'), ['billing_cycle' => $cycle])->assertRedirect('https://sandbox.doku.com/checkout-link-v2/test');
        return SubscriptionOrder::withoutGlobalScope('business')->latest('id')->firstOrFail();
    }

    private function notification(SubscriptionOrder $order, array $overrides = [], bool $valid = true)
    {
        $data = array_replace_recursive([
            'order' => ['invoice_number' => $order->order_id, 'amount' => (string) $order->amount],
            'transaction' => ['status' => 'SUCCESS', 'original_request_id' => $order->request_id],
            'channel' => ['id' => 'VIRTUAL_ACCOUNT_BCA'],
        ], $overrides);
        $body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $target = '/api/doku/subscription/notification';
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');
        // Independent signing calculation, over the exact transmitted bytes.
        $canonical = "Client-Id:test-client\nRequest-Id:notify-test\nRequest-Timestamp:{$timestamp}\nRequest-Target:{$target}\nDigest:".base64_encode(hash('sha256', $body, true));
        $signature = 'HMACSHA256='.base64_encode(hash_hmac('sha256', $canonical, 'test-secret', true));
        return $this->call('POST', $target, [], [], [], [
            'CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json',
            'HTTP_CLIENT_ID' => 'test-client', 'HTTP_REQUEST_ID' => 'notify-test',
            'HTTP_REQUEST_TIMESTAMP' => $timestamp, 'HTTP_SIGNATURE' => $valid ? $signature : 'invalid',
        ], $body);
    }

    public function test_owner_page_and_server_owned_checkout_data(): void
    {
        $owner = $this->owner();
        Package::where('code', 'premium')->update(['price_monthly' => 75000]);
        $this->actingAs($owner)->get(route('subscriptions.index'))->assertOk()->assertSee('75.000');
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly', 'amount' => 1, 'business_id' => 999, 'plan_id' => 999])
            ->assertRedirect('https://sandbox.doku.com/checkout-link-v2/test');
        $order = SubscriptionOrder::firstOrFail();
        $this->assertSame($owner->business_id, $order->business_id);
        $this->assertSame(75000, $order->amount);
        $this->assertSame('pending', $order->status);
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
        $this->assertStringStartsWith('SUB-', $order->order_id);
        $this->assertSame(30, strlen($order->order_id));
        Http::assertSent(function (ClientRequest $request) use ($order) {
            $timestamp = $request->header('Request-Timestamp')[0];
            $raw = 'Client-Id:test-client'."\nRequest-Id:{$order->request_id}\nRequest-Timestamp:{$timestamp}\nRequest-Target:/checkout/v1/payment\nDigest:".base64_encode(hash('sha256', $request->body(), true));
            return $request['order']['amount'] === 75000
                && $request['customer']['phone'] === '6281234567890'
                && ! isset($request['payment']['payment_method_types'])
                && $request->header('Signature')[0] === 'HMACSHA256='.base64_encode(hash_hmac('sha256', $raw, 'test-secret', true));
        });
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_details', 0);
    }

    public static function forbiddenRoles(): array
    {
        return [['Kasir'], ['Admin'], ['Gudang'], ['Super Admin']];
    }

    #[DataProvider('forbiddenRoles')]
    public function test_other_roles_cannot_purchase(string $role): void
    {
        $user = $this->owner($role);
        $this->actingAs($user)->withSession(['super_admin_business_id' => 1])->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertForbidden();
        Http::assertNothingSent();
        $this->assertDatabaseCount('subscription_orders', 0);
    }

    public function test_invalid_cycle_missing_config_and_inactive_plan_do_not_create_orders(): void
    {
        $this->actingAs($this->owner());
        $this->postJson(route('subscriptions.store'), ['billing_cycle' => 'weekly'])->assertUnprocessable();
        $this->postJson(route('subscriptions.store'), [])->assertUnprocessable();
        Package::where('code', 'premium')->update(['is_active' => false]);
        $this->postJson(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertUnprocessable();
        config(['doku.secret_key' => '']);
        $this->postJson(route('subscriptions.store'), ['billing_cycle' => 'yearly'])->assertUnprocessable();
        Http::assertNothingSent();
        $this->assertDatabaseCount('subscription_orders', 0);
    }

    public static function cycles(): array
    {
        return [['monthly', '2026-02-28 10:00:00'], ['yearly', '2027-01-31 10:00:00']];
    }

    #[DataProvider('cycles')]
    public function test_valid_payment_activates_the_correct_calendar_term(string $cycle, string $end): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-01-31 10:00:00'));
        $owner = $this->owner();
        $order = $this->purchase($owner, $cycle);
        $this->assertSame($cycle === 'monthly' ? 49000 : 499000, $order->amount);
        $this->notification($order)->assertOk();
        $business = $owner->business->fresh();
        $this->assertSame('premium', $business->effectiveSubscriptionPlan());
        $this->assertSame('active', $business->subscription_status);
        $this->assertSame($end, $business->subscription_ends_at->toDateTimeString());
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertTrue(app(PackageAccess::class)->allows('export_excel', $business));
        $this->get(route('subscriptions.invoice', $order->order_id))->assertOk()->assertSee($order->order_id)->assertSee('DOKU');
    }

    public function test_duplicate_late_and_failed_notifications_do_not_extend_twice(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $this->notification($order)->assertOk();
        $before = $owner->business->fresh()->subscription_ends_at->toDateTimeString();
        $this->travel(2)->days();
        $this->notification($order)->assertOk();
        $this->notification($order, ['transaction' => ['status' => 'FAILED']])->assertOk();
        $this->assertSame($before, $owner->business->fresh()->subscription_ends_at->toDateTimeString());
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_renewal_extends_from_existing_expiry_and_does_not_depend_on_scheduler(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-24 10:00:00'));
        $owner = $this->owner();
        $first = $this->purchase($owner);
        $this->notification($first)->assertOk();
        $this->travelTo(\Carbon\Carbon::parse('2026-10-20 10:00:00'));
        $second = $this->purchase($owner);
        $this->notification($second)->assertOk();
        $this->assertSame('2026-11-24 10:00:00', $owner->business->fresh()->subscription_ends_at->toDateTimeString());
        $this->assertSame('2026-10-24 10:00:00', $second->fresh()->started_at->toDateTimeString());
        $this->travelTo(\Carbon\Carbon::parse('2026-12-01 10:00:00'));
        $this->assertSame('free', $owner->business->fresh()->effectiveSubscriptionPlan());
        $third = $this->purchase($owner);
        $this->notification($third)->assertOk();
        $this->assertSame('2027-01-01 10:00:00', $owner->business->fresh()->subscription_ends_at->toDateTimeString());
    }

    public function test_expiry_keeps_store_and_existing_data_accessible_under_free_limits(): void
    {
        $owner = $this->owner();
        foreach (['products.view', 'products.create', 'reports.view'] as $permission) {
            $owner->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $order = $this->purchase($owner);
        $this->notification($order)->assertOk();
        $category = Category::create(['name' => 'Retained', 'slug' => 'retained']);
        for ($i = 0; $i < 101; $i++) {
            Product::create(['name' => 'Retained product '.$i, 'category_id' => $category->id, 'price' => 1000, 'stock' => 1, 'status' => 'active', 'is_active' => true]);
        }
        $this->travelTo($order->fresh()->expires_at->copy());
        $this->get(route('products.index'))->assertOk()->assertSee('Retained product');
        $this->postJson(route('products.store'), [])->assertUnprocessable()->assertJsonPath('errors.name.0', 'Batas 100 produk untuk paket Free telah tercapai.');
        $this->get(route('reports.export.excel'))->assertForbidden();
        $this->assertDatabaseCount('products', 101);
        $this->assertTrue($owner->business->fresh()->is_active);
        $this->artisan('subscriptions:expire')->assertSuccessful();
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('subscriptions.index'))->assertOk();
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
        $this->assertSame('expired', $owner->business->fresh()->subscription_status);
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_signature_amount_currency_and_request_mismatch_never_activate(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $this->notification($order, [], false)->assertUnauthorized();
        $this->notification($order, ['order' => ['amount' => '1']])->assertUnprocessable();
        $this->notification($order, ['order' => ['currency' => 'USD']])->assertUnprocessable();
        $this->notification($order, ['transaction' => ['original_request_id' => 'wrong']])->assertUnprocessable();
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_failure_is_retryable_and_return_query_cannot_activate(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        foreach (['FAILED', 'PENDING', 'EXPIRED', 'CANCELLED', 'NEW_UNKNOWN_STATUS'] as $status) {
            $this->notification($order, ['transaction' => ['status' => $status]])->assertOk();
        }
        $this->assertSame('pending', $order->fresh()->status);
        $this->get(route('subscriptions.result', $order->order_id).'?status=SUCCESS&amount=1')->assertOk()->assertSee('MENUNGGU PEMBAYARAN');
        $this->get(route('subscriptions.invoice', $order->order_id))->assertNotFound();
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
        $this->notification($order)->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_refresh_and_duplicate_checkout_reuse_pending_order(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $this->get(route('subscriptions.index'))->assertOk();
        $this->purchase($owner, 'yearly');
        $this->assertDatabaseCount('subscription_orders', 1);
        Http::assertSentCount(1);
        $this->assertSame('monthly', $order->fresh()->billing_cycle);
        $this->travel(61)->minutes();
        $this->get(route('subscriptions.result', $order->order_id))->assertOk()->assertDontSee('Lanjutkan Pembayaran');
        $this->purchase($owner, 'yearly');
        $this->assertDatabaseCount('subscription_orders', 2);
        $this->assertSame('expired', $order->fresh()->status);
        // A delayed authentic success for an expired local order is still settled.
        $this->notification($order)->assertOk();
        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_cross_tenant_history_invoice_and_forged_checkout_are_isolated(): void
    {
        $first = $this->owner();
        $order = $this->purchase($first);
        $this->notification($order)->assertOk();
        $secondBusiness = Business::create(['name' => 'Second store']);
        app(TenantContext::class)->set($secondBusiness);
        $second = $this->owner();
        app(TenantContext::class)->clear();
        $this->actingAs($second)->get(route('subscriptions.index'))->assertOk()->assertDontSee($order->order_id);
        $this->get(route('subscriptions.result', $order->order_id))->assertNotFound();
        $this->get(route('subscriptions.invoice', $order->order_id))->assertNotFound();
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly', 'business_id' => $first->business_id])->assertRedirect();
        $latest = SubscriptionOrder::withoutGlobalScope('business')->latest('id')->firstOrFail();
        $this->assertSame($secondBusiness->id, $latest->business_id);
        $this->assertSame('free', $secondBusiness->fresh()->subscription_plan);
    }

    public function test_platform_can_change_prices_and_monitor_without_purchasing(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $admin = $this->owner('Super Admin');
        $plan = Package::where('code', 'premium')->firstOrFail();
        $this->actingAs($admin)->put(route('packages.update', $plan), array_merge($plan->only(['name', ...Package::FEATURES, ...Package::LIMITS]), ['price_monthly' => 88000, 'price_yearly' => 880000, 'is_active' => true]))->assertSessionHasNoErrors();
        $this->assertSame(88000, $plan->fresh()->price_monthly);
        $this->assertSame(49000, $order->fresh()->amount);
        $this->get(route('super-admin.subscriptions.index'))->assertOk()->assertSee($order->order_id);
    }

    public function test_gateway_failure_and_timeout_do_not_grant_or_duplicate(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['error' => ['code' => 'invalid_request', 'message' => 'Invalid request']], 422)]);
        $owner = $this->owner();
        $this->actingAs($owner)->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        $this->assertSame('failed', SubscriptionOrder::firstOrFail()->status);
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['*' => Http::failedConnection()]);
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        $this->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        $this->assertDatabaseCount('subscription_orders', 2);
        $this->assertSame('create_unknown', SubscriptionOrder::latest('id')->firstOrFail()->status);
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
    }

    public function test_payment_does_not_unsuspend_or_reactivate_a_store(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $owner->business->update(['is_active' => false, 'subscription_status' => 'suspended']);
        $this->notification($order)->assertOk();
        $this->assertFalse($owner->business->fresh()->is_active);
        $this->assertSame('suspended', $owner->business->fresh()->subscription_status);
        $this->assertFalse(app(PackageAccess::class)->allows('export_excel', $owner->business));
        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_authorize_or_refund_success_does_not_count_as_payment(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        foreach (['AUTHORIZE', 'REFUND'] as $type) {
            $this->notification($order, ['transaction' => ['type' => $type]])->assertOk();
        }
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
        $this->assertNull($order->fresh()->paid_at);
    }

    public function test_expired_doku_owner_can_login_and_scheduler_preserves_manual_subscriptions(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $this->notification($order)->assertOk();
        $manual = Business::create(['name' => 'Manual grant', 'subscription_plan' => 'premium', 'subscription_ends_at' => now()->subDay()]);
        $this->travelTo($order->fresh()->expires_at->copy()->addSecond());
        $this->post(route('logout'));
        $this->post(route('login'), ['login' => $owner->username, 'password' => 'password'])->assertSessionHasNoErrors()->assertRedirect(route('dashboard', absolute: false));
        $this->get(route('subscriptions.index'))->assertOk();
        $this->artisan('subscriptions:expire')->assertSuccessful();
        $this->assertSame('premium', $manual->fresh()->subscription_plan);
        $this->assertSame('active', $manual->fresh()->subscription_status);
        $this->assertFalse($manual->fresh()->allowsOperations());
    }

    public function test_manual_platform_override_and_unlimited_grants_keep_existing_semantics(): void
    {
        $owner = $this->owner();
        $order = $this->purchase($owner);
        $this->notification($order)->assertOk();
        $admin = $this->owner('Super Admin');
        $this->actingAs($admin)->put(route('businesses.update', $owner->business_id), [
            'name' => $owner->business->name, 'subscription_plan' => 'premium',
            'subscription_status' => 'active', 'subscription_ends_at' => null, 'is_active' => true,
        ])->assertSessionHasNoErrors();
        $this->assertNull($owner->business->fresh()->subscription_source);
        $this->assertSame('premium', $owner->business->fresh()->effectiveSubscriptionPlan());
        $this->actingAs($owner)->postJson(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertUnprocessable();
        $this->assertDatabaseCount('subscription_orders', 1);
    }

    public function test_unsafe_checkout_response_is_never_used_as_a_redirect(): void
    {
        Http::swap(new \Illuminate\Http\Client\Factory());
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['response' => ['payment' => ['url' => 'https://attacker.example/collect']]])]);
        $owner = $this->owner();
        $this->actingAs($owner)->post(route('subscriptions.store'), ['billing_cycle' => 'monthly'])->assertRedirect();
        $order = SubscriptionOrder::firstOrFail();
        $this->assertNull($order->payment_url);
        $this->assertSame('create_unknown', $order->status);
        $this->assertSame('free', $owner->business->fresh()->subscription_plan);
    }

    public function test_payment_options_show_before_creating_subscription_and_use_server_price(): void
    {
        $user=$this->owner();
        $premium=app(PackageAccess::class)->forType($user->business->business_type,'premium');
        $this->actingAs($user)->get(route('subscriptions.payment-options',['billing_cycle'=>'monthly','amount'=>1,'business_id'=>999]))
            ->assertOk()->assertSee('Metode pembayaran')->assertSee('Bayar Sekarang')->assertSee('DOKU Checkout')
            ->assertViewHas('amount', $premium->price_monthly)->assertViewHas('business',fn($b)=>$b->id===$user->business_id);
        $this->assertDatabaseCount('subscription_orders',0);
        Http::assertNothingSent();
        $this->get(route('subscriptions.payment-options',['billing_cycle'=>'invalid']))->assertSessionHasErrors('billing_cycle');
        $this->get(route('subscriptions.index'))->assertOk()->assertSee(route('subscriptions.payment-options'),false);
    }
}
