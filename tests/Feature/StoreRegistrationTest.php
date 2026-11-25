<?php

namespace Tests\Feature;

use App\Mail\StoreRegistrationMail;
use App\Models\{Business, Category, Package, StoreRegistration, User};
use App\Notifications\StoreRegistrationNotification;
use App\Services\{PackageAccess, StoreRegistrationService};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Auth, Hash, Http, Log, Mail, Notification, URL};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class StoreRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        Http::preventStrayRequests();
    }

    private function data(array $extra = []): array
    {
        return [...[
            'submission_token' => (string) Str::uuid(), 'store_name' => 'Toko Pengajuan', 'business_type' => 'Retail',
            'store_phone' => '081234567890', 'store_email' => 'store@example.test', 'address' => 'Jalan Toko 123',
            'province' => 'Jawa Barat', 'city' => 'Bandung', 'owner_name' => 'Pemilik Baru', 'owner_username' => 'pemilik_baru',
            'owner_email' => 'owner-new@example.test', 'owner_phone' => '081234567891', 'requested_plan' => 'free', 'terms' => '1',
        ], ...$extra];
    }

    private function submit(array $extra = []): StoreRegistration
    {
        $data = $this->data($extra);
        $this->withSession(['store_registration_token' => $data['submission_token']])->post(route('store-registration.store'), $data)
            ->assertRedirect(route('store-registration.success'));
        return StoreRegistration::where('submission_token', $data['submission_token'])->firstOrFail();
    }

    private function admin(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));
        return $user;
    }

    public function test_guest_form_login_link_and_live_package_configuration(): void
    {
        Package::where('code', 'premium')->update(['price_monthly' => 75000, 'max_products' => 345]);
        $this->get(route('login'))->assertOk()->assertSee(route('store-registration.create'));
        $this->get(route('store-registration.create'))->assertOk()->assertSee('Daftarkan Toko Anda')
            ->assertSee('75.000')->assertSee('345')->assertSee('Review & Submit')->assertDontSee('name="password"', false);
        $this->get(route('store-registration.terms'))->assertOk();
    }

    public function test_submit_only_creates_pending_application_and_notifies_all_admins(): void
    {
        $first = $this->admin(); $second = $this->admin();
        $businesses = Business::count(); $users = User::count();
        $default = Business::findOrFail(1)->toArray();
        $registration = $this->submit();
        $this->assertGuest();
        $this->assertSame('pending', $registration->status);
        $this->assertNull($registration->business_id);
        $this->assertDatabaseCount('businesses', $businesses);
        $this->assertDatabaseCount('users', $users);
        $this->assertSame($default, Business::findOrFail(1)->toArray());
        Notification::assertSentTo([$first, $second], StoreRegistrationNotification::class);
        Mail::assertSent(StoreRegistrationMail::class, fn ($mail) => $mail->kind === 'received' && $mail->hasTo($registration->owner_email));
        Mail::assertSent(StoreRegistrationMail::class, fn ($mail) => $mail->kind === 'review' && $mail->hasTo($first->email));
        $this->assertStringNotContainsString('password', json_encode($registration->toArray()));
    }

    public function test_double_submit_and_duplicate_identity_do_not_create_multiple_applications(): void
    {
        $data = $this->data();
        for ($i = 0; $i < 2; $i++) $this->withSession(['store_registration_token' => $data['submission_token']])
            ->post(route('store-registration.store'), $data)->assertRedirect(route('store-registration.success'));
        $this->assertDatabaseCount('store_registrations', 1);
        Mail::assertSentCount(1);
        $data['submission_token'] = (string) Str::uuid();
        $this->withSession(['store_registration_token' => $data['submission_token']])->post(route('store-registration.store'), $data)->assertSessionHasErrors('owner_username');
        $this->assertDatabaseCount('store_registrations', 1);
    }

    public function test_validation_rejects_forged_tenant_plan_missing_terms_and_invalid_cycle(): void
    {
        $data = $this->data(['business_id' => 1, 'requested_plan' => 'premium', 'billing_cycle' => 'lifetime', 'terms' => 0, 'business_type' => 'Lainnya']);
        $this->withSession(['store_registration_token' => $data['submission_token']])->post(route('store-registration.store'), $data)
            ->assertSessionHasErrors(['business_id', 'billing_cycle', 'terms', 'business_type_other']);
        $data = $this->data(['requested_plan' => 'unknown']);
        $this->withSession(['store_registration_token' => $data['submission_token']])->post(route('store-registration.store'), $data)->assertSessionHasErrors('requested_plan');
        $this->assertDatabaseCount('store_registrations', 0);
    }

    public function test_existing_user_identity_is_rejected_and_prices_are_not_accepted(): void
    {
        $existing = User::factory()->create(['username' => 'taken']);
        $data = $this->data(['owner_username' => 'taken', 'owner_email' => $existing->email]);
        $this->withSession(['store_registration_token' => $data['submission_token']])->post(route('store-registration.store'), $data)->assertSessionHasErrors(['owner_username'])->assertSessionDoesntHaveErrors('owner_email');
        $registration = $this->submit(['requested_plan' => 'premium', 'billing_cycle' => 'yearly', 'amount' => 1, 'password' => 'DO-NOT-STORE']);
        $this->assertSame('yearly', $registration->billing_cycle);
        $this->assertStringNotContainsString('DO-NOT-STORE', json_encode($registration->getAttributes()));
        Http::assertNothingSent();
    }

    public function test_submission_is_rate_limited_by_ip(): void
    {
        for ($i = 0; $i < 5; $i++) $this->post(route('store-registration.store'), [])->assertStatus(302);
        $this->post(route('store-registration.store'), [])->assertStatus(429);
    }

    public function test_all_tenant_roles_are_forbidden_from_review_routes(): void
    {
        $registration = $this->submit();
        foreach (['Admin', 'Atasan', 'Kasir', 'Gudang'] as $role) {
            $user = $this->admin($role);
            $this->actingAs($user)->get(route('super-admin.store-registrations.index'))->assertForbidden();
            $this->get(route('super-admin.store-registrations.show', $registration))->assertForbidden();
            foreach (['approve', 'reject', 'resend'] as $action) $this->post(route('super-admin.store-registrations.'.$action, $registration), ['rejection_reason' => 'No'])->assertForbidden();
        }
        $this->assertSame('pending', $registration->fresh()->status);
    }

    public function test_approve_free_is_idempotent_creates_correct_owner_and_preserves_default_store(): void
    {
        $registration = $this->submit(); $admin = $this->admin();
        Category::create(['name' => 'Default Private Category', 'slug' => 'default-private']);
        $default = Business::findOrFail(1)->toArray();
        $before = Business::count();
        $this->actingAs($admin)->get(route('super-admin.store-registrations.index'))->assertOk()->assertSee('Toko Pengajuan');
        $this->get(route('super-admin.store-registrations.show', $registration))->assertOk();
        for ($i = 0; $i < 2; $i++) $this->post(route('super-admin.store-registrations.approve', $registration))->assertRedirect();
        $registration->refresh(); $business = $registration->business; $owner = $registration->owner;
        $this->assertSame('approved', $registration->status);
        $this->assertSame($admin->id, $registration->reviewed_by);
        $this->assertNotNull($registration->reviewed_at);
        $this->assertDatabaseCount('businesses', $before + 1);
        $this->assertSame(1, User::withoutGlobalScope('business')->where('username', 'pemilik_baru')->count());
        $this->assertNotSame(1, $business->id);
        $this->assertSame($business->id, $owner->business_id);
        $this->assertSame($owner->id, $business->owner_id);
        $this->assertTrue($owner->hasRole('Atasan'));
        $this->assertTrue($owner->must_change_credentials);
        $this->assertSame('free', $business->effectiveSubscriptionPlan());
        $this->assertTrue($business->allowsOperations());
        $this->assertSame($default, Business::findOrFail(1)->toArray());
        app(TenantContext::class)->set($business);
        $this->assertSame(0, Category::count());
        app(TenantContext::class)->clear();
        Notification::assertSentTo($owner, StoreRegistrationNotification::class);
        Mail::assertSent(StoreRegistrationMail::class, fn ($mail) => $mail->kind === 'approved' && $mail->hasTo($owner->email));
        $this->post(route('super-admin.store-registrations.reject', $registration), ['rejection_reason' => 'Late rejection'])->assertStatus(409);
    }

    public function test_premium_approval_is_pending_payment_but_free_access_remains_usable(): void
    {
        $registration = $this->submit(['requested_plan' => 'premium', 'billing_cycle' => 'monthly']);
        $this->actingAs($this->admin())->post(route('super-admin.store-registrations.approve', $registration))->assertRedirect();
        $registration->refresh(); $business = $registration->business;
        $this->assertSame('pending_payment', $business->subscription_status);
        $this->assertSame('free', $business->effectiveSubscriptionPlan());
        $this->assertTrue($business->allowsOperations());
        $this->assertFalse(app(PackageAccess::class)->allows('export_excel', $business));
        $owner = $registration->owner; $owner->update(['must_change_credentials' => false]);
        $this->actingAs($owner)->get(route('dashboard'))->assertOk();
        $this->get(route('subscriptions.index'))->assertOk()->assertSee('Premium Menunggu Pembayaran');
        $business->update(['is_active' => false]);
        $this->assertFalse($business->allowsOperations());
        Http::assertNothingSent();
    }

    public function test_reject_requires_reason_creates_no_tenant_and_sends_rejection(): void
    {
        $registration = $this->submit(); $admin = $this->admin();
        $businesses = Business::count(); $users = User::count();
        $this->actingAs($admin)->post(route('super-admin.store-registrations.reject', $registration))->assertSessionHasErrors('rejection_reason');
        $this->post(route('super-admin.store-registrations.reject', $registration), ['rejection_reason' => 'Informasi toko belum lengkap'])->assertRedirect();
        $this->assertSame('rejected', $registration->fresh()->status);
        $this->assertDatabaseCount('businesses', $businesses); $this->assertDatabaseCount('users', $users);
        Mail::assertSent(StoreRegistrationMail::class, fn ($mail) => $mail->kind === 'rejected' && $mail->registration->rejection_reason === 'Informasi toko belum lengkap');
        $this->post(route('super-admin.store-registrations.approve', $registration))->assertStatus(409);
    }

    public function test_approval_revalidates_identity_if_an_existing_user_claimed_it(): void
    {
        $registration = $this->submit();
        User::factory()->create(['username' => $registration->owner_username]);
        $before = Business::count();
        $this->actingAs($this->admin())->post(route('super-admin.store-registrations.approve', $registration))->assertSessionHasErrors('owner_username');
        $this->assertDatabaseCount('businesses', $before);
        $this->assertSame('pending', $registration->fresh()->status);
    }

    public function test_email_failure_never_rolls_back_approval_or_logs_sensitive_message(): void
    {
        $registration = $this->submit();
        $admin = $this->admin();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('secret-mail-credential'));
        Log::spy();
        $this->actingAs($admin)->post(route('super-admin.store-registrations.approve', $registration))->assertRedirect();
        $this->assertSame('approved', $registration->fresh()->status);
        $this->assertNotNull($registration->fresh()->business_id);
        Log::shouldHaveReceived('warning')->withArgs(fn ($message, $data) => $data['stage'] === 'approval_email' && ! str_contains(json_encode($data), 'secret-mail-credential'));
    }

    public function test_activation_requires_signed_link_and_first_login_password_setup(): void
    {
        $registration = $this->submit();
        app(StoreRegistrationService::class)->review($registration, $this->admin(), true);
        $registration->refresh();
        $url = URL::temporarySignedRoute('store-registration.activate', now()->addHour(), ['registration' => $registration->id]);
        $this->get(route('store-registration.activate', $registration))->assertForbidden();
        $this->get($url)->assertOk(); $this->assertGuest();
        $this->assertNull($registration->fresh()->activated_at);
        $this->post($url)->assertRedirect(route('first-login.edit'));
        $this->assertAuthenticatedAs($registration->owner);
        $this->get(route('dashboard'))->assertRedirect(route('first-login.edit'));
        $this->put(route('first-login.update'), ['username' => 'pemilik_baru', 'password' => 'secure-new-password', 'password_confirmation' => 'secure-new-password'])->assertRedirect(route('dashboard'));
        $owner = $registration->owner->fresh();
        $this->assertFalse($owner->must_change_credentials);
        $this->assertTrue(Hash::check('secure-new-password', $owner->password));
        $this->get(route('dashboard'))->assertOk();
        $this->post(route('logout'));
        $this->post($url)->assertStatus(410);
        $this->post(route('login'), ['login' => 'pemilik_baru', 'password' => 'secure-new-password'])->assertRedirect('/dashboard');
    }

    public function test_expired_link_is_rejected_and_admin_can_resend(): void
    {
        $registration = $this->submit(); $admin = $this->admin();
        app(StoreRegistrationService::class)->review($registration, $admin, true);
        $expired = URL::temporarySignedRoute('store-registration.activate', now()->subMinute(), ['registration' => $registration->id]);
        $this->post($expired)->assertForbidden();
        $this->actingAs($admin)->post(route('super-admin.store-registrations.resend', $registration))->assertRedirect();
        Mail::assertSent(StoreRegistrationMail::class, fn ($mail) => $mail->kind === 'approved' && str_contains($mail->actionUrl, 'signature='));
    }

    public function test_database_notification_payload_and_email_views_render_without_password(): void
    {
        $registration = $this->submit();
        $payload = (new StoreRegistrationNotification($registration))->toArray($this->admin());
        $this->assertSame($registration->id, $payload['registration_id']);
        $this->assertSame('Pengajuan toko baru: Toko Pengajuan', $payload['message']);
        foreach (['received', 'review', 'approved', 'rejected'] as $kind) {
            $html = (new StoreRegistrationMail($registration, $kind, route('login')))->render();
            $this->assertStringContainsString('Toko Pengajuan', $html);
            $this->assertStringNotContainsString('name="password"', $html);
        }
    }

    public function test_database_notifications_and_pending_badge_are_visible_to_super_admin(): void
    {
        Notification::swap(new \Illuminate\Notifications\ChannelManager($this->app));
        $admin = $this->admin();
        $registration = $this->submit();
        $this->assertSame(1, $admin->unreadNotifications()->count());
        $this->actingAs($admin)->get(route('super-admin.notifications.index'))->assertOk()
            ->assertSee('Pengajuan toko baru: Toko Pengajuan')->assertSee(route('super-admin.store-registrations.show', $registration));
        $response = $this->get(route('super-admin.dashboard'))->assertOk()->assertSee('Pengajuan Toko Pending');
        $dom = new \DOMDocument(); @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $badges = $xpath->query('//a[@href="'.route('super-admin.store-registrations.index').'"]//span[contains(@class,"sa-count")]');
        $this->assertSame('1', trim($badges->item(0)->textContent));
        $this->post(route('super-admin.store-registrations.approve', $registration))->assertRedirect();
        $this->assertSame(1, $registration->fresh()->owner->unreadNotifications()->count());
    }

    public function test_review_filters_and_search_do_not_expose_other_results(): void
    {
        $registration = $this->submit();
        $this->actingAs($this->admin())->get(route('super-admin.store-registrations.index', ['status' => 'pending', 'requested_plan' => 'free', 'business_type' => 'Retail', 'search' => 'Pengajuan']))->assertOk()->assertSee($registration->owner_email);
        $this->get(route('super-admin.store-registrations.index', ['requested_plan' => 'premium']))->assertOk()->assertDontSee($registration->owner_email)->assertSee('Belum ada pengajuan toko.');
    }
}
