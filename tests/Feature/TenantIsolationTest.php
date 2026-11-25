<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\User;
use App\Tenancy\TenantContext;
use Tests\Concerns\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_operational_data_is_scoped_to_the_current_business(): void
    {
        $firstUser = User::factory()->create();
        $firstBusiness = $firstUser->business;
        $this->assertSame($firstUser->id, $firstBusiness->fresh()->owner_id);
        $secondBusiness = Business::create(['name' => 'Toko Kedua', 'slug' => 'toko-kedua']);
        app(TenantContext::class)->set($secondBusiness);
        $secondUser = User::factory()->create();
        $this->assertSame($secondBusiness->id, $secondUser->business_id);

        app(TenantContext::class)->set($firstBusiness);
        $firstCategory = Category::create(['name' => 'Kategori Satu', 'slug' => 'kategori-satu']);

        app(TenantContext::class)->set($secondBusiness);
        $secondCategory = Category::create(['name' => 'Kategori Dua', 'slug' => 'kategori-dua']);

        app(TenantContext::class)->set($firstBusiness);
        $this->assertTrue(Category::whereKey($firstCategory)->exists());
        $this->assertFalse(Category::whereKey($secondCategory)->exists());

        $permission = Permission::firstOrCreate(['name' => 'categories.view', 'guard_name' => 'web']);
        $firstUser->givePermissionTo($permission);
        app(TenantContext::class)->clear();

        $this->actingAs($firstUser)
            ->get(route('categories.show', $secondCategory))
            ->assertNotFound();
    }
}
