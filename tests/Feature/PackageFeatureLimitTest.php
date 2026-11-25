<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Package;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Table;
use App\Models\User;
use App\Services\PackageAccess;
use App\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class PackageFeatureLimitTest extends TestCase
{
    use RefreshDatabase;

    private function operator(string $plan = 'free'): User
    {
        $user = User::factory()->create();
        $user->business->update(['subscription_plan' => $plan]);
        foreach (['products.create', 'products.view', 'users.create', 'users.view', 'users.approve', 'tables.create', 'tables.view', 'tables.edit', 'reports.view', 'settings.view', 'settings.edit'] as $name) {
            $user->givePermissionTo(Permission::findOrCreate($name, 'web'));
        }

        return $user;
    }

    private function productData(): array
    {
        $category = Category::firstOrCreate(['slug' => 'package-test'], ['name' => 'Package Test']);

        return ['name' => 'Produk Baru', 'category_id' => $category->id, 'price' => 1000, 'stock' => 1,
            'status' => 'active', 'image' => UploadedFile::fake()->image('product.jpg')];
    }

    private function fillProducts(int $count): void
    {
        $category = Category::firstOrCreate(['slug' => 'package-test'], ['name' => 'Package Test']);
        for ($i = 0; $i < $count; $i++) {
            Product::create(['name' => 'Produk '.($i + 1), 'category_id' => $category->id, 'price' => 1000, 'stock' => 1, 'status' => 'active', 'is_active' => true]);
        }
    }

    private function packagePayload(Package $package, array $changes = []): array
    {
        return array_merge($package->only(['name', ...Package::LIMITS, ...Package::FEATURES]), $changes);
    }

    private function businessPayload(Business $business, string $plan): array
    {
        return ['name' => $business->name, 'subscription_plan' => $plan, 'subscription_status' => 'active', 'is_active' => 1];
    }

    public function test_package_defaults_match_free_and_premium_specifications(): void
    {
        $this->assertDatabaseHas('packages', ['code' => 'free', 'max_products' => 100, 'max_users' => 2, 'max_tables' => 5,
            'export_excel' => false, 'export_pdf' => false, 'custom_branding' => false, 'ai_agent' => false, 'qr_order' => true]);
        $this->assertDatabaseHas('packages', ['code' => 'premium', 'max_products' => null, 'max_users' => null, 'max_tables' => null,
            'export_excel' => true, 'export_pdf' => true, 'custom_branding' => true, 'ai_agent' => true, 'qr_order' => true]);
    }

    public function test_free_can_create_one_hundred_products_through_the_existing_endpoint(): void
    {
        Storage::fake('public');
        $this->actingAs($this->operator());
        for ($i = 1; $i <= 100; $i++) {
            $this->post(route('products.store'), array_merge($this->productData(), ['name' => 'Produk '.$i]))
                ->assertRedirect(route('products.index'));
        }
        $this->assertDatabaseCount('products', 100);
    }

    public function test_free_product_one_hundred_and_one_is_rejected_with_a_readable_message(): void
    {
        Storage::fake('public');
        $this->actingAs($this->operator());
        $this->fillProducts(100);
        $this->postJson(route('products.store'), $this->productData())->assertUnprocessable()
            ->assertJsonValidationErrors('name')->assertJsonPath('errors.name.0', 'Batas 100 produk untuk paket Free telah tercapai.');
        $this->assertDatabaseCount('products', 100);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->from(route('products.create'))->post(route('products.store'), $this->productData())
            ->assertRedirect(route('products.create'))->assertSessionHasErrors(['name' => 'Batas 100 produk untuk paket Free telah tercapai.']);
    }

    public function test_premium_can_create_products_beyond_the_free_limit(): void
    {
        Storage::fake('public');
        $this->actingAs($this->operator('premium'));
        $this->fillProducts(100);
        $this->post(route('products.store'), $this->productData())->assertRedirect(route('products.index'));
        $this->assertDatabaseCount('products', 101);
    }

    public function test_free_allows_two_approved_users_including_the_owner(): void
    {
        $this->actingAs($this->operator());
        Role::findOrCreate('Kasir', 'web');
        $data = ['name' => 'Kasir', 'username' => 'kasir', 'email' => 'kasir@example.test', 'role' => 'Kasir',
            'password' => 'password-aman', 'password_confirmation' => 'password-aman', 'is_active' => 1];
        $this->post(route('users.store'), $data)->assertRedirect(route('users.index'));
        $this->postJson(route('users.store'), array_merge($data, ['username' => 'ketiga', 'email' => 'ketiga@example.test']))
            ->assertUnprocessable()->assertJsonPath('errors.name.0', 'Batas pengguna paket Anda telah tercapai.');
        $this->assertDatabaseCount('users', 2);
    }

    public function test_pending_approval_cannot_bypass_user_quota_and_inactive_users_still_count(): void
    {
        $user = $this->operator();
        $user->assignRole(Role::findOrCreate('Atasan', 'web'));
        Role::findOrCreate('Kasir', 'web');
        User::factory()->create(['is_active' => false]);
        $pending = User::factory()->create(['status' => 'pending', 'is_active' => false, 'requested_role' => 'Kasir']);
        $this->actingAs($user)->patchJson(route('users.approvals.approve', $pending))->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'Batas pengguna paket Anda telah tercapai.');
        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertTrue($pending->fresh()->roles->isEmpty());
    }

    public function test_free_allows_five_tables_and_rejects_the_sixth(): void
    {
        $this->actingAs($this->operator());
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('tables.store'), ['name' => 'Meja '.$i])->assertRedirect(route('tables.index'));
        }
        $this->postJson(route('tables.store'), ['name' => 'Meja 6'])->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'Batas meja paket Anda telah tercapai.');
        $this->assertDatabaseCount('tables', 5);
    }

    public function test_approval_does_not_count_a_pending_record_twice_against_legacy_cap(): void
    {
        $user = $this->operator();
        $user->business->update(['user_limit' => 2]);
        $user->assignRole(Role::findOrCreate('Atasan', 'web'));
        Role::findOrCreate('Kasir', 'web');
        $pending = User::factory()->create(['status' => 'pending', 'is_active' => false, 'requested_role' => 'Kasir']);
        $this->actingAs($user)->patch(route('users.approvals.approve', $pending))->assertRedirect();
        $this->assertSame('approved', $pending->fresh()->status);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_premium_can_exceed_free_user_and_table_limits(): void
    {
        $this->actingAs($this->operator('premium'));
        Role::findOrCreate('Kasir', 'web');
        for ($i = 1; $i <= 3; $i++) {
            $this->post(route('users.store'), ['name' => 'Kasir '.$i, 'username' => 'kasir-'.$i, 'email' => 'kasir'.$i.'@example.test',
                'password' => 'password-aman', 'password_confirmation' => 'password-aman', 'role' => 'Kasir', 'is_active' => 1])
                ->assertRedirect(route('users.index'));
        }
        $this->post(route('tables.generate'), ['jumlah' => 6])->assertRedirect(route('tables.index'));
        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('tables', 6);
    }

    public function test_legacy_store_cap_cannot_raise_the_free_package_limit(): void
    {
        $user = $this->operator();
        $user->business->update(['product_limit' => 1000]);
        $this->fillProducts(100);
        $this->actingAs($user)->postJson(route('products.store'), [])->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'Batas 100 produk untuk paket Free telah tercapai.');
        $this->assertDatabaseCount('products', 100);
    }

    public function test_disabled_subscription_cannot_use_premium_features(): void
    {
        $user = $this->operator('premium');
        $this->actingAs($user);
        foreach (['expired', 'suspended', 'inactive'] as $status) {
            $user->business->update(['subscription_status' => $status]);
            $this->get(route('reports.export.excel'))->assertForbidden();
            $this->postJson(route('products.store'), [])->assertForbidden();
        }
    }

    public function test_bulk_table_generation_is_atomic_and_cannot_exceed_limit(): void
    {
        $this->actingAs($this->operator());
        $this->postJson(route('tables.generate'), ['jumlah' => 6])->assertUnprocessable()
            ->assertJsonPath('errors.jumlah.0', 'Batas meja paket Anda telah tercapai.');
        $this->assertDatabaseCount('tables', 0);
        $this->post(route('tables.generate'), ['jumlah' => 5])->assertRedirect(route('tables.index'));
        Table::firstOrFail()->update(['is_active' => false]);
        $this->post(route('tables.generate'), ['jumlah' => 5])->assertRedirect(route('tables.index'));
        $this->assertDatabaseCount('tables', 5);
        $this->assertSame(5, Table::where('is_active', true)->count());
        $this->postJson(route('tables.generate'), ['jumlah' => 6])->assertUnprocessable();
        $this->assertDatabaseCount('tables', 5);
    }

    public static function exports(): array
    {
        return [['reports.export.excel'], ['reports.pdf']];
    }

    #[DataProvider('exports')]
    public function test_free_cannot_export_even_with_report_permission(string $route): void
    {
        $this->actingAs($this->operator())->get(route($route))->assertForbidden()->assertSee(PackageAccess::PREMIUM_MESSAGE);
    }

    #[DataProvider('exports')]
    public function test_premium_can_download_real_exports(string $route): void
    {
        $this->actingAs($this->operator('premium'))->get(route($route))->assertOk()->assertDownload();
    }

    public function test_upgrade_via_super_admin_immediately_unlocks_features(): void
    {
        $user = $this->operator();
        $business = $user->business;
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user)->get(route('ai.index'))->assertForbidden();
        $this->actingAs($admin)->put(route('businesses.update', $business), $this->businessPayload($business, 'premium'))
            ->assertRedirect(route('businesses.index'));
        $this->actingAs($user)->get(route('ai.index'))->assertOk();
        $this->get(route('reports.export.excel'))->assertOk()->assertDownload();
    }

    public function test_downgrade_preserves_all_records_and_blocks_only_new_additions(): void
    {
        $user = $this->operator('premium');
        $this->fillProducts(500);
        User::factory()->count(3)->create();
        for ($i = 0; $i < 6; $i++) {
            Table::create(['name' => 'Meja '.$i, 'qr_token' => 'downgrade-'.$i, 'is_active' => true]);
        }
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $before = [];
        foreach (['products', 'users', 'tables'] as $table) {
            $before[$table] = DB::table($table)->orderBy('id')->get()->toJson();
        }
        $this->actingAs($admin)->put(route('businesses.update', $user->business), $this->businessPayload($user->business, 'free'))
            ->assertRedirect(route('businesses.index'));
        foreach ($before as $table => $rows) {
            $this->assertSame($rows, DB::table($table)->orderBy('id')->get()->toJson());
        }
        $this->actingAs($user)->get(route('products.index'))->assertOk();
        $this->get(route('users.index'))->assertOk();
        $this->get(route('tables.index'))->assertOk();
        $this->postJson(route('products.store'), [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson(route('users.store'), [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson(route('tables.store'), [])->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->get(route('reports.pdf'))->assertForbidden();
        $this->get(route('ai.index'))->assertForbidden();
    }

    public function test_free_tenant_cannot_borrow_another_tenants_premium_package(): void
    {
        $user = $this->operator();
        $premium = Business::create(['name' => 'Premium Tenant', 'subscription_plan' => 'premium']);
        $this->actingAs($user)->withSession(['super_admin_business_id' => $premium->id])
            ->get(route('reports.export.excel', ['business_id' => $premium->id, 'package' => 'premium', 'subscription_plan' => 'premium']))->assertForbidden();
        $this->postJson(route('ai.chat'), ['message' => 'test', 'business_id' => $premium->id, 'package' => 'premium'])->assertForbidden();
        $this->fillProducts(100);
        $this->postJson(route('products.store'), ['business_id' => $premium->id, 'package' => 'premium', 'max_products' => null])->assertUnprocessable();
        $this->assertSame('free', $user->business->fresh()->subscription_plan);
    }

    public function test_super_admin_can_edit_all_package_settings_and_changes_apply_immediately(): void
    {
        $user = $this->operator();
        $free = Package::where('code', 'free')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($admin)->get(route('packages.index'))->assertOk()->assertSee('Fitur &amp; Paket', false);
        $changes = ['name' => 'Free Baru', 'max_products' => 1, 'max_users' => 4, 'max_tables' => 10,
            'export_excel' => true, 'export_pdf' => true, 'custom_branding' => true, 'ai_agent' => true, 'qr_order' => false];
        $this->put(route('packages.update', $free), $this->packagePayload($free, $changes))->assertRedirect(route('packages.index'));
        $this->assertDatabaseHas('packages', array_merge(['id' => $free->id, 'code' => 'free'], $changes));
        $this->actingAs($user)->get(route('ai.index'))->assertOk();
        $this->get(route('reports.export.excel'))->assertOk()->assertDownload();
        $this->fillProducts(1);
        $this->postJson(route('products.store'), [])->assertUnprocessable()
            ->assertJsonPath('errors.name.0', 'Batas 1 produk untuk paket Free Baru telah tercapai.');
    }

    public function test_regular_users_cannot_edit_packages_and_invalid_settings_are_not_saved(): void
    {
        $user = $this->operator();
        $free = Package::where('code', 'free')->firstOrFail();
        $before = $free->getAttributes();
        $this->actingAs($user)->get(route('packages.index'))->assertForbidden();
        $this->put(route('packages.update', $free), $this->packagePayload($free, ['max_products' => null]))->assertForbidden();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->putJson(route('packages.update', $free), $this->packagePayload($free, ['max_products' => -1, 'ai_agent' => 'yes']))
            ->assertUnprocessable()->assertJsonValidationErrors(['max_products', 'ai_agent']);
        $this->assertSame($before, $free->fresh()->getAttributes());
    }

    public function test_free_branding_changes_are_blocked_but_contact_updates_preserve_existing_branding(): void
    {
        Storage::fake('public');
        $user = $this->operator();
        Setting::setValue('app_name', 'Nama Existing');
        Setting::setValue('receipt_footer', 'Footer Existing');
        $this->actingAs($user)->putJson(route('settings.update'), ['app_name' => 'Nama Baru'])
            ->assertForbidden()->assertJsonPath('message', PackageAccess::PREMIUM_MESSAGE);
        $this->putJson(route('settings.update'), ['app_logo' => UploadedFile::fake()->image('logo.png')])->assertForbidden();
        $this->put(route('settings.update'), ['business_phone' => '0812345678'])->assertRedirect(route('settings.index'));
        $this->assertSame('Nama Existing', Setting::getValue('app_name'));
        $this->assertSame('Footer Existing', Setting::getValue('receipt_footer'));
        $this->assertSame('0812345678', Setting::getValue('business_phone'));
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->get(route('settings.index'))->assertOk()->assertSee('Premium');
    }

    public function test_premium_can_update_custom_branding(): void
    {
        Storage::fake('public');
        $this->actingAs($this->operator('premium'))->put(route('settings.update'), [
            'app_name' => 'Premium Branding', 'receipt_footer' => 'Terima kasih',
            'app_logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect(route('settings.index'));
        $this->assertSame('Premium Branding', Setting::getValue('app_name'));
        Storage::disk('public')->assertExists(Setting::getValue('app_logo'));
    }

    public function test_free_ai_is_blocked_on_every_endpoint_before_external_service_calls(): void
    {
        $mock = $this->mock(\App\Services\GeminiAiAgentService::class);
        $mock->shouldNotReceive('chat');
        $this->actingAs($this->operator())->get(route('ai.index'))->assertForbidden();
        $this->postJson(route('ai.chat'), ['message' => 'test'])->assertForbidden()->assertJsonPath('message', PackageAccess::PREMIUM_MESSAGE);
        $this->postJson(route('ai.reset'))->assertForbidden();
    }

    public function test_qr_feature_uses_the_public_tables_package_and_protects_all_new_order_entrypoints(): void
    {
        $user = $this->operator();
        $table = Table::create(['name' => 'Meja Free', 'qr_token' => 'free-qr', 'is_active' => true]);
        $premium = Business::create(['name' => 'Premium QR', 'subscription_plan' => 'premium']);
        Package::where('code', 'free')->firstOrFail()->update(['qr_order' => false]);
        $this->get(route('customer.menu', ['qr_token' => $table->qr_token, 'business_id' => $premium->id]))->assertForbidden();
        $this->get(route('customer.checkout', $table))->assertForbidden();
        $this->postJson(route('customer.checkout.store', $table), ['business_id' => $premium->id])->assertForbidden();
        $this->actingAs($user)->get(route('tables.qr', $table))->assertForbidden();
        $this->get(route('tables.qr.image', $table))->assertForbidden();
        $this->get(route('tables.printAllQr'))->assertForbidden();
        $this->get(route('tables.index'))->assertOk();
        $user->business->update(['subscription_plan' => 'premium']);
        $this->get(route('customer.menu', $table->qr_token))->assertOk();
        $this->get(route('tables.qr.image', $table))->assertOk();
    }
}
