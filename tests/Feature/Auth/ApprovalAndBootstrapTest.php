<?php

namespace Tests\Feature\Auth;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\InitialSupervisorSeeder;
use Database\Seeders\SuperAdminTestAccountSeeder;
use Tests\Concerns\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalAndBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_and_rejected_users_cannot_authenticate(): void
    {
        foreach (['pending', 'rejected'] as $status) {
            $user = User::factory()->create([
                'username' => $status.'-user',
                'status' => $status,
                'is_active' => false,
            ]);

            $this->post('/login', ['login' => $user->username, 'password' => 'password'])
                ->assertSessionHasErrors('login');
            $this->assertGuest();
        }
    }

    public function test_registration_only_creates_a_pending_role_request(): void
    {
        foreach (['Admin', 'Gudang', 'Kasir', 'Atasan'] as $role) {
            $email = strtolower($role).'@example.test';
            $this->post('/register', [
                'name' => 'Pendaftar '.$role,
                'username' => strtolower($role).'-candidate',
                'email' => $email,
                'password' => 'password',
                'password_confirmation' => 'password',
                'requested_role' => $role,
            ])->assertRedirect(route('account.pending', false));

            $user = User::whereEmail($email)->firstOrFail();
            $this->assertSame('pending', $user->status);
            $this->assertFalse($user->is_active);
            $this->assertTrue($user->roles->isEmpty());
        }
    }

    public function test_only_supervisor_with_permission_can_approve_and_assign_requested_role(): void
    {
        $supervisorRole = Role::create(['name' => 'Atasan', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'users.approve', 'guard_name' => 'web']);
        $supervisorRole->givePermissionTo($permission);
        Role::create(['name' => 'Kasir', 'guard_name' => 'web']);
        $supervisor = User::factory()->create();
        $supervisor->assignRole($supervisorRole);
        $candidate = User::factory()->create(['status' => 'pending', 'is_active' => false, 'requested_role' => 'Kasir']);

        $this->actingAs($supervisor)->get('/users/approvals')->assertOk();
        $this->patch('/users/'.$candidate->id.'/approve')->assertRedirect();

        $candidate->refresh();
        $this->assertSame('approved', $candidate->status);
        $this->assertTrue($candidate->is_active);
        $this->assertTrue($candidate->hasRole('Kasir'));
    }

    public function test_regular_user_cannot_access_or_approve_registration(): void
    {
        $user = User::factory()->create();
        $candidate = User::factory()->create(['status' => 'pending', 'is_active' => false]);

        $this->actingAs($user)->get('/users/approvals')->assertForbidden();
        $this->patch('/users/'.$candidate->id.'/approve')->assertForbidden();
        $this->assertSame('pending', $candidate->fresh()->status);
    }

    public function test_initial_supervisor_is_idempotent_and_must_change_credentials(): void
    {
        $this->seed(InitialSupervisorSeeder::class);
        $this->seed(InitialSupervisorSeeder::class);

        $user = User::role('Atasan')->firstOrFail();
        $this->assertSame(1, User::role('Atasan')->count());
        $this->assertSame('atasan', $user->username);
        $this->assertSame('approved', $user->status);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->must_change_credentials);
        $this->assertTrue(Hash::check('atasan123', $user->password));

        $this->post('/login', ['login' => 'atasan', 'password' => 'atasan123'])
            ->assertRedirect(route('first-login.edit', false));
        $this->get('/dashboard')->assertRedirect(route('first-login.edit', false));
    }

    public function test_initial_supervisor_can_change_credentials_before_using_dashboard(): void
    {
        $this->seed(InitialSupervisorSeeder::class);
        $supervisor = User::role('Atasan')->firstOrFail();

        $this->actingAs($supervisor)->put('/account/first-login', [
            'username' => 'atasan-utama',
            'password' => 'password-baru-aman',
            'password_confirmation' => 'password-baru-aman',
        ])->assertRedirect(route('dashboard', false));

        $supervisor->refresh();
        $this->assertFalse($supervisor->must_change_credentials);
        $this->assertTrue(Hash::check('password-baru-aman', $supervisor->password));
        $this->post('/logout');
        $this->post('/login', ['login' => 'atasan', 'password' => 'atasan123'])->assertSessionHasErrors('login');
    }

    public function test_super_admin_must_change_the_initial_password_before_accessing_dashboard(): void
    {
        $this->seed(SuperAdminTestAccountSeeder::class);
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        $this->assertTrue($superAdmin->hasRole('Super Admin'));
        $this->assertTrue($superAdmin->must_change_credentials);
        $this->assertTrue(Hash::check('superadmin123', $superAdmin->password));

        $this->post('/login', ['login' => 'superadmin', 'password' => 'superadmin123'])
            ->assertRedirect(route('first-login.edit', false));
        $this->get('/dashboard')->assertRedirect(route('first-login.edit', false));
        $this->get('/account/first-login')->assertSee('Password saat ini');

        $this->put('/account/first-login', [
            'current_password' => 'superadmin123',
            'password' => 'superadmin-password-baru',
            'password_confirmation' => 'superadmin-password-baru',
        ])->assertRedirect(route('super-admin.dashboard', false));

        $superAdmin->refresh();
        $this->assertFalse($superAdmin->must_change_credentials);
        $this->assertTrue(Hash::check('superadmin-password-baru', $superAdmin->password));

        $this->post('/logout');
        $this->post('/login', ['login' => 'superadmin', 'password' => 'superadmin-password-baru'])
            ->assertRedirect(route('super-admin.dashboard', false));
    }

    public function test_login_uses_rumaakasir_branding_and_platform_logo(): void
    {
        Setting::setValue('app_name', 'Toko Contoh');
        Setting::setValue('app_logo', 'settings/logo.png');
        \App\Models\PlatformSetting::updateOrCreate(['id' => 1], ['app_name' => 'Platform Test', 'logo' => 'storage/platform/logo.png']);

        $this->get('/login')->assertOk()
            ->assertSee('Platform Test')
            ->assertSee('storage/platform/logo.png')
            ->assertDontSee('Toko Contoh')->assertDontSee('storage/settings/logo.png');

        \App\Models\PlatformSetting::find(1)->update(['app_name' => '', 'logo' => null]);
        config(['app.name' => 'Configured Platform']);
        $this->get('/login')->assertSee('Rumaakasir')->assertSee('All rights reserved.')->assertDontSee('Configured Platform')->assertDontSee('Toko Contoh');
    }
}
