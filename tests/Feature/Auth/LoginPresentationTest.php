<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class LoginPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_and_links_keep_existing_routes_and_csrf(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('action="'.route('login').'"', false)
            ->assertSee('name="_token"', false)->assertSee('method="POST"', false)
            ->assertSee(route('password.request'))->assertSee(route('store-registration.create'))
            ->assertSee('href="'.route('customer.register').'"', false)
            ->assertSee('href="'.route('customer.login').'"', false)
            ->assertSee('Belum punya akun?')->assertSee('Daftar Pelanggan')->assertSee('Masuk sebagai Pelanggan')
            ->assertSee('autocomplete="current-password"', false)->assertSee('data-password-toggle', false);
        $this->get(route('password.request'))->assertOk();
        $this->get(route('store-registration.create'))->assertOk();
        $this->get(route('customer.register'))->assertOk();
        $this->get(route('customer.login'))->assertOk();
    }

    public function test_errors_and_success_are_rendered_without_echoing_password(): void
    {
        $this->from(route('login'))->post(route('login'), ['login' => 'unknown@example.test', 'password' => 'NeverEchoThisPassword'])
            ->assertRedirect(route('login'));
        $this->get(route('login'))->assertOk()->assertSee('role="alert"', false)
            ->assertSee('Username atau password yang Anda masukkan salah.')
            ->assertDontSee('NeverEchoThisPassword');
        $this->withSession(['status' => 'Password berhasil diubah.'])->get(route('login'))->assertOk()->assertSee('Password berhasil diubah.');
    }

    public static function roles(): array { return [['Super Admin'], ['Atasan'], ['Admin'], ['Kasir'], ['Gudang']]; }

    #[DataProvider('roles')]
    public function test_all_existing_roles_can_submit_the_login_form(string $role): void
    {
        $user = User::factory()->create(['username' => 'login-test']);
        $user->assignRole(Role::findOrCreate($role, 'web'));
        $this->post(route('login'), ['login' => 'login-test', 'password' => 'password', 'remember' => '1'])
            ->assertRedirect(route($role === 'Super Admin' ? 'super-admin.dashboard' : 'dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
