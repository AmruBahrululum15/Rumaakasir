<?php

namespace Tests\Feature;

use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class ProxyAssetUrlTest extends TestCase
{
    use RefreshDatabase;

    public static function connections(): array
    {
        return [
            'local HTTPS tunnel' => ['127.0.0.1', 'https', 'https'],
            'direct local HTTP' => ['127.0.0.1', 'http', 'http'],
            'untrusted forwarded header' => ['192.0.2.10', 'https', 'http'],
        ];
    }

    #[DataProvider('connections')]
    public function test_assets_and_forms_follow_only_the_trusted_connection(string $remote, string $forwarded, string $scheme): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user)->withServerVariables([
            'REMOTE_ADDR' => $remote,
            'HTTP_HOST' => 'preview.example.test',
            'HTTP_X_FORWARDED_PROTO' => $forwarded,
            'HTTP_X_FORWARDED_HOST' => 'untrusted.example.test',
        ])->get('http://preview.example.test/super-admin/dashboard')
            ->assertOk()
            ->assertSee($scheme.'://preview.example.test/vendor/adminlte/dist/css/adminlte.min.css', false)
            ->assertSee($scheme.'://preview.example.test/js/super-admin-shell.js', false)
            ->assertSee('action="'.$scheme.'://preview.example.test/logout"', false)
            ->assertDontSee('untrusted.example.test');
    }
}
