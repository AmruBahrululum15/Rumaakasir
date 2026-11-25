<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class DemoAccountCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_isolated_demo_supervisor_without_touching_other_tenants(): void
    {
        $this->artisan('demo:provision', [
            '--username' => 'demo',
            '--email' => 'demo@example.test',
            '--password' => 'A-demo-password!',
            '--business' => 'Rumaakasir Demo',
        ])->assertExitCode(0);

        $business = Business::query()->where('slug', 'rumaakasir-demo')->firstOrFail();
        $user = User::withoutGlobalScopes()->where('username', 'demo')->firstOrFail();

        $this->assertSame($business->id, $user->business_id);
        $this->assertSame($user->id, $business->owner_id);
        $this->assertTrue($user->hasRole('Atasan'));
        $this->assertFalse($user->must_change_credentials);
        $this->assertTrue(Hash::check('A-demo-password!', $user->password));
    }

    public function test_it_refuses_to_overwrite_an_account_owned_by_another_tenant(): void
    {
        $other = Business::create(['name' => 'Toko Lain']);
        $user = new User([
            'name' => 'Owner Lain',
            'username' => 'dipakai',
            'email' => 'other@example.test',
            'password' => Hash::make('Another-password!'),
            'business_id' => $other->id,
        ]);
        $user->saveQuietly();

        $this->artisan('demo:provision', [
            '--username' => 'dipakai',
            '--email' => 'demo@example.test',
            '--password' => 'A-demo-password!',
        ])->assertExitCode(1);

        $this->assertDatabaseMissing('businesses', ['slug' => 'rumaakasir-demo']);
    }
}
