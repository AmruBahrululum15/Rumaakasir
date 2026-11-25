<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use App\Tenancy\TenantContext;
use Tests\Concerns\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminBusinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_and_select_multiple_businesses(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($role);
        $secondBusiness = Business::create(['name' => 'Toko Kedua', 'slug' => 'toko-kedua']);

        $this->actingAs($superAdmin)->get(route('businesses.index'))->assertOk();
        $this->actingAs($superAdmin)->post(route('businesses.activate', $secondBusiness))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('super_admin_business_id', $secondBusiness->id);
    }

    public function test_super_admin_creates_a_business_and_its_first_supervisor(): void
    {
        $role = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::create(['name' => 'Atasan', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($role);

        $this->actingAs($superAdmin)->post(route('businesses.store'), [
            'name' => 'Toko Baru',
            'slug' => 'toko-baru',
            'phone' => '08123456789',
            'address' => 'Alamat Toko Baru',
            'subscription_plan' => 'free',
            'subscription_status' => 'active',
            'is_active' => '1',
            'supervisor_name' => 'Atasan Toko Baru',
            'supervisor_username' => 'atasan-toko-baru',
            'supervisor_email' => 'atasan-toko-baru@example.test',
            'supervisor_password' => 'password-awal-aman',
            'supervisor_password_confirmation' => 'password-awal-aman',
        ])->assertRedirect();

        $business = Business::where('slug', 'toko-baru')->firstOrFail();
        $supervisor = User::withoutGlobalScope('business')->where('username', 'atasan-toko-baru')->firstOrFail();

        $this->assertNotSame(1, $business->id);
        $this->assertSame($business->id, $supervisor->business_id);
        $this->assertSame($supervisor->id, $business->owner_id);
        $this->assertTrue($supervisor->hasRole('Atasan'));
        $this->assertTrue($supervisor->must_change_credentials);
        $this->assertTrue(Hash::check('password-awal-aman', $supervisor->password));
        $this->assertSame('free', $business->subscription_plan);
    }

    public function test_regular_user_cannot_access_business_management_and_data_stays_isolated(): void
    {
        $firstUser = User::factory()->create();
        $firstBusiness = $firstUser->business;
        $secondBusiness = Business::create(['name' => 'Toko Kedua', 'slug' => 'toko-kedua']);

        app(TenantContext::class)->set($secondBusiness);
        $secondCategory = Category::create(['name' => 'Kategori Kedua', 'slug' => 'kategori-kedua']);
        app(TenantContext::class)->clear();

        $firstUser->givePermissionTo(Permission::firstOrCreate([
            'name' => 'categories.view',
            'guard_name' => 'web',
        ]));

        $this->actingAs($firstUser)->get(route('businesses.index'))->assertForbidden();
        $this->actingAs($firstUser)->get(route('categories.show', $secondCategory))->assertNotFound();
        $this->assertSame(1, $firstBusiness->id);
    }
}
