<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Table;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class SaasSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function grant(User $user, array $permissions): void
    {
        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
    }

    private function secondStore(): array
    {
        $business = Business::create(['name' => 'Toko Kedua']);
        app(TenantContext::class)->set($business);
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Rahasia Toko Kedua', 'slug' => 'rahasia']);
        $supplier = Supplier::create(['name' => 'Supplier Kedua']);
        app(TenantContext::class)->clear();

        return [$business, $user, $category, $supplier];
    }

    public function test_second_store_user_can_login_and_bind_only_their_own_records(): void
    {
        $firstUser = User::factory()->create(['username' => 'legacy-default']);
        $firstCategory = Category::create(['name' => 'Kategori Default', 'slug' => 'default']);
        [$business, $user, $category] = $this->secondStore();
        $this->grant($user, ['categories.view', 'categories.edit', 'categories.delete']);

        $this->post('/login', ['login' => $user->username, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->get(route('categories.show', $category))->assertOk()->assertSee($category->name);
        $this->get(route('categories.show', $firstCategory))->assertNotFound();
        $this->put(route('categories.update', $firstCategory), ['name' => 'Diserang'])->assertNotFound();
        $this->delete(route('categories.destroy', $firstCategory))->assertNotFound();
        $this->assertDatabaseHas('categories', ['id' => $firstCategory->id, 'name' => 'Kategori Default']);
        $this->assertNull(app(TenantContext::class)->id());
        $this->post('/logout');
        $this->post('/login', ['login' => $firstUser->username, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($firstUser);
    }

    public function test_foreign_category_and_supplier_are_rejected_before_product_creation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->grant($user, ['products.create']);
        [, , $category, $supplier] = $this->secondStore();

        $this->actingAs($user)->postJson(route('products.store'), [
            'name' => 'Cross Tenant', 'category_id' => $category->id, 'supplier_id' => $supplier->id,
            'price' => 1000, 'stock' => 1, 'status' => 'active',
            'image' => UploadedFile::fake()->image('test.jpg'),
        ])->assertUnprocessable()->assertJsonValidationErrors(['category_id', 'supplier_id']);
        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_super_admin_selection_controls_binding_and_does_not_leak_into_next_request(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->grant($admin, ['categories.view']);
        $first = Category::create(['name' => 'Kategori Default', 'slug' => 'default']);
        [$business, , $second] = $this->secondStore();

        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('businesses.index'));
        $this->post(route('businesses.activate', $business))->assertRedirect('/dashboard');
        $this->get(route('categories.show', $second))->assertOk();
        $this->get(route('categories.show', $first))->assertNotFound();
        $this->get(route('businesses.index'))->assertOk()->assertViewHas('businesses', fn ($stores) =>
            $stores->firstWhere('id', 1)->users_count === 1 && $stores->firstWhere('id', $business->id)->users_count === 1
        );
        $this->post(route('businesses.activate', 1))->assertRedirect('/dashboard');
        $this->get(route('categories.show', $first))->assertOk();
        $this->get(route('categories.show', $second))->assertNotFound();
    }

    public function test_tenant_cannot_manage_platform_or_assign_super_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Atasan', 'web'));
        $this->grant($user, ['users.create', 'users.edit', 'users.view']);
        Role::findOrCreate('Super Admin', 'web');
        [$business, $otherUser] = $this->secondStore();

        $this->actingAs($user)->get(route('businesses.index'))->assertForbidden();
        $this->post(route('businesses.store'), [])->assertForbidden();
        $this->put(route('businesses.update', $business), [])->assertForbidden();
        $this->post(route('businesses.activate', $business))->assertForbidden();
        $this->postJson(route('users.store'), ['role' => 'Super Admin'])->assertForbidden();
        $this->get(route('users.show', $otherUser))->assertNotFound();
        $this->put(route('users.update', $otherUser), [])->assertNotFound();
    }

    public function test_user_quota_blocks_only_new_users_and_preserves_existing_accounts(): void
    {
        $user = User::factory()->create();
        $this->grant($user, ['users.create', 'users.view']);
        $user->business->update(['user_limit' => 1]);
        $this->actingAs($user)->postJson(route('users.store'), [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->get(route('users.index'))->assertOk();
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_product_quota_is_tenant_specific_and_cannot_be_overridden_in_request(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $this->grant($user, ['products.create']);
        $user->business->update(['product_limit' => 1]);
        $category = Category::create(['name' => 'Kategori', 'slug' => 'kategori']);
        $payload = ['name' => 'Produk Pertama', 'category_id' => $category->id, 'price' => 1000,
            'stock' => 1, 'status' => 'active', 'image' => UploadedFile::fake()->image('first.jpg'), 'business_id' => 999];
        $this->actingAs($user)->post(route('products.store'), $payload)->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['name' => 'Produk Pertama', 'business_id' => 1]);
        $this->postJson(route('products.store'), array_merge($payload, ['name' => 'Kedua', 'product_limit' => 500]))
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('products', 1);
    }

    public function test_disabled_account_and_inactive_or_expired_store_cannot_use_existing_sessions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk();
        $user->update(['is_active' => false]);
        $this->get('/dashboard')->assertForbidden();
        $user->update(['is_active' => true]);
        $user->business->update(['subscription_status' => 'inactive']);
        $this->get('/dashboard')->assertForbidden();
        $user->business->update(['subscription_status' => 'active', 'subscription_ends_at' => now()->subMinute()]);
        $this->get('/dashboard')->assertForbidden();
        $user->business->update(['subscription_ends_at' => null, 'is_active' => false]);
        $this->get('/dashboard')->assertForbidden();
        $this->get('/ai-assistant')->assertForbidden();
    }

    public function test_guest_branding_never_falls_back_to_another_active_tenant(): void
    {
        Business::findOrFail(1)->update(['is_active' => false]);
        [$business] = $this->secondStore();
        app(TenantContext::class)->set($business);
        Setting::setValue('app_name', 'Branding Rahasia Kedua');
        $this->get('/login')->assertOk()->assertDontSee('Branding Rahasia Kedua');
        $this->assertNull(app(TenantContext::class)->id());
    }

    public function test_private_and_legacy_photos_require_owner_or_same_tenant_permission(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = User::factory()->create(['foto' => 'users/legacy.jpg']);
        Storage::disk('public')->put('users/legacy.jpg', 'legacy-photo');
        [$business, $other] = $this->secondStore();
        $other->update(['foto' => 'profiles/private.jpg']);
        Storage::disk('local')->put('profiles/private.jpg', 'private-photo');

        $this->get(route('users.photo', $user))->assertRedirect('/login');
        $this->actingAs($user)->get(route('users.photo', $user))->assertOk();
        $this->get(route('users.photo', $other))->assertNotFound();
        $this->grant($user, ['users.view']);
        $this->get(route('users.photo', $other))->assertNotFound();
        $this->actingAs($other)->get(route('users.photo', $other))->assertOk();
        Storage::disk('public')->assertExists('users/legacy.jpg');
    }

    public function test_public_qr_uses_its_store_and_blocks_inactive_subscription(): void
    {
        [$business, , $category] = $this->secondStore();
        app(TenantContext::class)->set($business);
        $table = Table::create(['name' => 'Meja Kedua', 'qr_token' => 'second-store-token', 'is_active' => true]);
        Product::create(['name' => 'Menu Kedua', 'category_id' => $category->id, 'price' => 1000, 'stock' => 1, 'is_active' => true]);
        app(TenantContext::class)->clear();
        $this->get(route('customer.menu', $table->qr_token))->assertOk()->assertSee('Menu Kedua');
        $this->assertNull(app(TenantContext::class)->id());
        $business->update(['subscription_status' => 'inactive']);
        $this->get(route('customer.menu', $table->qr_token))->assertNotFound();
        $this->post(route('customer.checkout.store', $table), [])->assertNotFound();
    }

    public function test_supervisor_creates_users_in_their_own_store_and_uploads_private_photo(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Role::findOrCreate('Kasir', 'web');
        [$business, $owner] = $this->secondStore();
        $owner->assignRole(Role::findOrCreate('Atasan', 'web'));
        $this->grant($owner, ['users.create', 'users.view']);

        $this->actingAs($owner)->post(route('users.store'), [
            'name' => 'Kasir Baru', 'username' => 'kasir-baru', 'email' => 'kasir@example.test',
            'password' => 'password-baru', 'password_confirmation' => 'password-baru',
            'role' => 'Kasir', 'is_active' => 1, 'business_id' => 1,
            'foto' => UploadedFile::fake()->image('kasir.jpg'),
        ])->assertRedirect(route('users.index'));
        $cashier = User::withoutGlobalScope('business')->where('username', 'kasir-baru')->firstOrFail();
        $this->assertSame($business->id, $cashier->business_id);
        $this->assertTrue($cashier->hasRole('Kasir'));
        Storage::disk('local')->assertExists($cashier->foto);
        Storage::disk('public')->assertMissing($cashier->foto);
        $this->get(route('users.photo', $cashier))->assertOk();
    }

    public function test_exports_and_notifications_remain_isolated(): void
    {
        $first = User::factory()->create();
        $saleData = ['code' => 'LOCAL-SALE', 'sale_date' => now()->toDateString(), 'total' => 1000, 'paid' => 1000, 'change' => 0];
        $firstSale = \App\Models\Sale::create($saleData);
        [$business, $second] = $this->secondStore();
        app(TenantContext::class)->set($business);
        $secondSale = \App\Models\Sale::create(array_merge($saleData, ['code' => 'SECRET-SALE']));
        $notification = $second->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'test', 'data' => ['message' => 'Secret'],
        ]);
        app(TenantContext::class)->set($first->business);
        $this->assertSame([$firstSale->id], (new \App\Exports\ReportExport())->collection()->modelKeys());
        app(TenantContext::class)->set($business);
        $this->assertSame([$secondSale->id], (new \App\Exports\ReportExport())->collection()->modelKeys());

        $this->actingAs($first)->patch(route('notifications.read', $notification->id))->assertNotFound();
        $this->delete(route('notifications.destroy', $notification->id))->assertNotFound();
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'read_at' => null]);
    }
}
