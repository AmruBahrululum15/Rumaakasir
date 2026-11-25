<?php

namespace Tests\Feature;

use App\Mail\StoreRegistrationMail;
use App\Models\{Business, StoreRegistration, User};
use App\Notifications\BusinessResetPasswordNotification;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Auth, DB, Hash, Http, Mail, Notification, Password};
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class SharedEmailAccountTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake(); Notification::fake(); Http::preventStrayRequests();
    }

    private function account(string $username): User
    {
        $business = Business::create(['name' => 'Toko '.$username, 'subscription_plan' => 'free']);
        app(TenantContext::class)->set($business);
        try {
            $user = User::factory()->create(['username' => $username, 'email' => 'owner@example.test']);
            $user->assignRole(Role::findOrCreate('Atasan', 'web'));
            return $user;
        } finally { app(TenantContext::class)->clear(); }
    }

    private function submit(string $username)
    {
        $token = (string) Str::uuid();
        return $this->withSession(['store_registration_token' => $token])->post(route('store-registration.store'), [
            'submission_token' => $token, 'store_name' => 'Toko '.$username, 'business_type' => 'retail',
            'store_phone' => '08123456789', 'store_email' => 'owner@example.test', 'address' => 'Alamat',
            'province' => 'Jawa Barat', 'city' => 'Bandung', 'owner_name' => 'Budi', 'owner_username' => $username,
            'owner_email' => 'owner@example.test', 'owner_phone' => '08123456789', 'requested_plan' => 'free', 'terms' => 1,
        ]);
    }

    private function requestToken(User $user): string
    {
        $this->post(route('password.email'), ['username' => $user->username])->assertSessionHasNoErrors();
        return Notification::sent($user, BusinessResetPasswordNotification::class)->last()->token;
    }

    private function reset(User $user, string $token, string $password = 'replacement-password')
    {
        return $this->post(route('password.store'), ['username' => $user->username, 'token' => $token,
            'password' => $password, 'password_confirmation' => $password]);
    }

    public function test_shared_email_registrations_approve_as_separate_accounts_and_tenants(): void
    {
        $this->submit('owner_a')->assertRedirect(route('store-registration.success'));
        $this->submit('owner_b')->assertRedirect(route('store-registration.success'));
        $this->submit('owner_a')->assertSessionHasErrors('owner_username')->assertSessionDoesntHaveErrors('owner_email');
        $admin = User::factory()->create(); $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $default = Business::findOrFail(1)->getAttributes();
        foreach (StoreRegistration::all() as $registration) {
            $this->actingAs($admin)->post(route('super-admin.store-registrations.approve', $registration))->assertSessionHasNoErrors();
            $this->assertTrue($registration->fresh()->owner->hasRole('Atasan'));
        }
        $a = User::where('username', 'owner_a')->firstOrFail();
        $b = User::where('username', 'owner_b')->firstOrFail();
        $this->assertSame($a->email, $b->email);
        $this->assertNotEquals($a->id, $b->id);
        $this->assertNotEquals($a->business_id, $b->business_id);
        $this->assertSame($default, Business::findOrFail(1)->getAttributes());
        Mail::assertSent(StoreRegistrationMail::class, fn ($mail) => $mail->kind === 'approved' && str_contains($mail->render(), 'owner_a') && str_contains($mail->render(), 'Toko owner_a'));
        Auth::logout();
        $this->submit('owner_c')->assertRedirect(route('store-registration.success'));
        $this->submit('owner_b')->assertSessionHasErrors('owner_username');
    }

    public function test_username_login_selects_correct_tenant_and_email_login_is_rejected(): void
    {
        $a = $this->account('owner_a'); $b = $this->account('owner_b');
        foreach ([$a, $b] as $user) {
            $this->post(route('login'), ['login' => $user->username, 'password' => 'password'])->assertRedirect(route('dashboard'));
            $this->assertAuthenticatedAs($user);
            $this->assertSame($user->business_id, Auth::user()->business_id);
            $this->get(route('dashboard'))->assertOk();
            $this->post(route('logout'));
        }
        $this->post(route('login'), ['login' => $a->email, 'password' => 'password'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_super_admin_existing_username_and_unambiguous_email_still_work(): void
    {
        $user = User::factory()->create(); $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        foreach ([$user->username, $user->email] as $login) {
            $this->post(route('login'), ['login' => $login, 'password' => 'password'])->assertRedirect(route('super-admin.dashboard'));
            $this->assertAuthenticatedAs($user); $this->post(route('logout'));
        }
    }

    public function test_first_login_and_password_change_touch_only_authenticated_account(): void
    {
        $a = $this->account('owner_a'); $b = $this->account('owner_b');
        $before = $b->password;
        $a->update(['must_change_credentials' => true]);
        $this->actingAs($a)->put(route('first-login.update'), ['username' => $a->username,
            'password' => 'first-new-password', 'password_confirmation' => 'first-new-password'])->assertRedirect(route('dashboard'));
        $this->assertTrue(Hash::check('first-new-password', $a->fresh()->password));
        $this->assertSame($before, $b->fresh()->password);
        $this->put(route('password.update'), ['current_password' => 'first-new-password', 'password' => 'second-new-password',
            'password_confirmation' => 'second-new-password'])->assertSessionHasNoErrors();
        $this->assertSame($before, $b->fresh()->password);
        $this->assertTrue(Hash::check('second-new-password', $a->fresh()->password));
    }

    public function test_reset_tokens_are_account_bound_hashed_and_consumed_once(): void
    {
        $a = $this->account('owner_a'); $b = $this->account('owner_b'); $before = $b->password;
        $tokenA = $this->requestToken($a); $tokenB = $this->requestToken($b);
        $record = DB::table('password_reset_tokens')->where('email', 'account:'.$a->id)->first();
        $this->assertNotSame($tokenA, $record->token);
        $this->assertTrue(Hash::check($tokenA, $record->token));
        $this->reset($b, $tokenA)->assertSessionHasErrors('username');
        $this->assertSame($before, $b->fresh()->password);
        $this->reset($a, $tokenA)->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('replacement-password', $a->fresh()->password));
        $this->assertSame($before, $b->fresh()->password);
        $this->assertTrue(Password::tokenExists($b, $tokenB));
        $this->reset($a, $tokenA, 'replayed-password')->assertSessionHasErrors('username');
        $this->assertTrue(Hash::check('replacement-password', $a->fresh()->password));
    }

    public function test_reset_email_and_readonly_form_identify_username_and_store(): void
    {
        $user = $this->account('owner_a'); $token = $this->requestToken($user);
        $mail = Notification::sent($user, BusinessResetPasswordNotification::class)->last()->toMail($user);
        $html = view($mail->view, $mail->viewData)->render();
        $this->assertStringContainsString('owner_a', $html);
        $this->assertStringContainsString('Toko owner_a', $html);
        $this->assertStringContainsString('username=owner_a', $html);
        $this->assertStringNotContainsString('email=owner', $html);
        $this->get(route('password.reset', ['token' => $token, 'username' => $user->username]))->assertOk()->assertSee('readonly', false)->assertDontSee($user->email);
    }

    public function test_reset_is_generic_rate_limited_and_requires_username(): void
    {
        $user = $this->account('owner_a');
        foreach ([$user->username, 'missing-account', $user->username] as $username) {
            $this->post(route('password.email'), compact('username'))->assertRedirect()->assertSessionHasNoErrors()
                ->assertSessionHas('status', 'Jika akun tersebut valid, instruksi reset password akan dikirim.');
        }
        Notification::assertSentToTimes($user, BusinessResetPasswordNotification::class, 1);
        $this->post(route('password.email'), ['email' => $user->email])->assertSessionHasErrors('username');
        $this->post(route('password.email'), ['username' => 'missing']);
        $this->post(route('password.email'), ['username' => 'missing'])->assertStatus(429);
    }

    public function test_expired_and_legacy_email_tokens_cannot_reset_account(): void
    {
        $user = $this->account('owner_a'); $token = $this->requestToken($user);
        $this->travel(config('auth.passwords.users.expire') + 1)->minutes();
        $this->reset($user, $token)->assertSessionHasErrors('username');
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => Hash::make('legacy-token'), 'created_at' => now()]);
        $this->reset($user, 'legacy-token')->assertSessionHasErrors('username');
    }

    public function test_owner_can_create_staff_with_shared_email_but_not_duplicate_username(): void
    {
        $owner = $this->account('owner_a'); $other = $this->account('owner_b');
        $owner->givePermissionTo(\Spatie\Permission\Models\Permission::findOrCreate('users.create', 'web'));
        Role::findOrCreate('Kasir', 'web');
        $data = ['name' => 'Kasir', 'username' => $other->username, 'email' => $owner->email,
            'password' => 'staff-password', 'password_confirmation' => 'staff-password', 'role' => 'Kasir', 'is_active' => 1];
        $this->actingAs($owner)->post(route('users.store'), $data)->assertSessionHasErrors('username')->assertSessionDoesntHaveErrors('email');
        $data['username'] = 'staff_a';
        $this->post(route('users.store'), $data)->assertSessionHasNoErrors();
        $staff = User::where('username', 'staff_a')->firstOrFail();
        $this->assertSame($owner->email, $staff->email);
        $this->assertSame($owner->business_id, $staff->business_id);
        $this->assertTrue($staff->hasRole('Kasir'));
    }

    public function test_profile_can_use_another_accounts_email_without_changing_that_account(): void
    {
        $a = $this->account('owner_a'); $b = $this->account('owner_b');
        $a->update(['email' => 'different@example.test']);
        $before = $b->fresh()->getAttributes();
        $this->actingAs($a)->patch(route('profile.update'), ['name' => $a->name, 'email' => $b->email])->assertSessionHasNoErrors();
        $this->assertSame($b->email, $a->fresh()->email);
        $this->assertSame($before, $b->fresh()->getAttributes());
    }
}

