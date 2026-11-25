<?php

namespace Tests\Feature;

use App\Models\{Business, Package, StoreRegistration, Table, User};
use App\Services\{BusinessModules, PackageAccess, StoreRegistrationService};
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\{Mail, Notification};
use Illuminate\Support\Str;
use Spatie\Permission\Models\{Role, Permission};
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class BusinessModuleProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void { parent::setUp(); Mail::fake(); Notification::fake(); }

    private function tenant(?string $type, string $plan = 'free'): array
    {
        $business = Business::create(['name' => 'Test '.($type ?? 'Legacy'), 'subscription_plan' => $plan]);
        $business->forceFill(['business_type' => $type])->save();
        app(TenantContext::class)->set($business);
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Atasan', 'web'));
        foreach (['tables.view', 'tables.create', 'products.view', 'suppliers.view', 'purchases.view', 'reports.view'] as $name) $user->givePermissionTo(Permission::findOrCreate($name, 'web'));
        $table = Table::create(['name' => 'Meja Uji', 'qr_token' => Str::random(30), 'is_active' => true]);
        app(TenantContext::class)->clear();
        return [$business, $user, $table];
    }

    public function test_registration_normalizes_and_approval_copies_only_stored_business_type(): void
    {
        $token = (string) Str::uuid();
        $this->withSession(['store_registration_token' => $token])->post(route('store-registration.store'), [
            'submission_token' => $token, 'store_name' => 'Cafe Baru', 'business_type' => 'Cafe', 'store_phone' => '08123456789',
            'store_email' => 'store@example.test', 'address' => 'Alamat', 'province' => 'Jawa Barat', 'city' => 'Bandung',
            'owner_name' => 'Owner', 'owner_username' => 'newowner', 'owner_email' => 'owner@example.test',
            'owner_phone' => '08123456789', 'requested_plan' => 'free', 'terms' => 1,
        ])->assertRedirect(route('store-registration.success'));
        $registration = StoreRegistration::firstOrFail();
        $this->assertSame('cafe', $registration->business_type);
        $admin = User::factory()->create(); $admin->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($admin)->get(route('super-admin.store-registrations.show', $registration))->assertOk()->assertSee('Modul yang akan tersedia')->assertSee('Meja');
        $this->post(route('super-admin.store-registrations.approve', $registration), ['business_type' => 'retail'])->assertRedirect();
        $this->assertSame('cafe', $registration->fresh()->business->business_type);
        $this->get(route('businesses.show', $registration->fresh()->business_id))->assertOk()->assertSee('Jenis Usaha')->assertSee('Cafe')->assertSee('Modul Aktif');
    }

    public function test_retail_hides_tables_and_blocks_reads_writes_and_public_qr_even_on_premium(): void
    {
        [$business, $user, $table] = $this->tenant('retail', 'premium');
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('href="'.route('tables.index').'"', false);
        $this->get(route('tables.index'))->assertForbidden();
        $this->get(route('tables.qr', $table))->assertForbidden();
        $this->post(route('tables.store'), ['name' => 'Forged', 'business_id' => 1])->assertForbidden();
        $this->get(route('customer.menu', $table->qr_token))->assertForbidden();
        $this->get(route('customer.checkout', $table))->assertForbidden();
        $this->post(route('customer.checkout.store', $table), [])->assertForbidden();
        $this->assertFalse(app(PackageAccess::class)->allows('qr_order', $business));
    }

    public function test_cafe_tables_and_qr_are_allowed_only_when_package_permits(): void
    {
        [$business, $user, $table] = $this->tenant('cafe', 'premium');
        Package::where('code', 'premium')->update(['qr_order' => true]);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('href="'.route('tables.index').'"', false);
        $this->get(route('tables.index'))->assertOk();
        $this->get(route('customer.menu', $table->qr_token))->assertOk();
        Package::where('code', 'premium')->update(['qr_order' => false]);
        $business->update(['subscription_plan' => 'free']);
        $this->get(route('customer.menu', $table->qr_token))->assertForbidden();
        $this->get(route('tables.qr', $table))->assertRedirect(route('subscriptions.index'));
        $this->get(route('tables.index'))->assertOk();
        $this->assertFalse(app(PackageAccess::class)->allows('export_excel', $business));
    }

    public function test_tenant_cannot_change_or_borrow_other_tenant_profile(): void
    {
        [$retail, $user] = $this->tenant('retail');
        [$cafe, $other, $table] = $this->tenant('cafe');
        $this->actingAs($user)->put(route('businesses.update', $cafe), ['business_type' => 'retail'])->assertForbidden();
        $this->get(route('tables.index', ['business_id' => $cafe->id, 'business_type' => 'cafe']))->assertForbidden();
        $this->get(route('tables.show', $table))->assertForbidden();
        $this->assertSame('cafe', $cafe->fresh()->business_type);
        $this->assertSame('retail', $retail->fresh()->business_type);
    }

    public function test_legacy_and_default_store_keep_existing_modules(): void
    {
        $default = Business::findOrFail(1);
        $this->assertNull($default->business_type);
        $this->assertTrue(app(BusinessModules::class)->allows($default, 'tables'));
        [$legacy, $user] = $this->tenant(null);
        $this->actingAs($user)->get(route('tables.index'))->assertOk();
        $this->get(route('dashboard'))->assertOk()->assertSee('href="'.route('tables.index').'"', false);
        $this->assertNull($default->fresh()->business_type);
        $this->assertTrue($default->fresh()->allowsOperations());
    }

    public function test_all_profiles_have_core_and_planned_modules_have_no_menu_routes(): void
    {
        $service = app(BusinessModules::class);
        $this->assertCount(12, $service->types());
        foreach (array_keys($service->types()) as $type) {
            foreach (['dashboard','pos','customers','settings','profile','subscriptions'] as $core) $this->assertContains($core, $service->profile($type));
        }
        $this->assertContains('product_variants', $service->profile('fashion'));
        $this->assertContains('laundry_status', $service->profile('laundry'));
        $this->assertContains('booking', $service->profile('salon'));
        $this->assertContains('vehicles', $service->profile('bengkel'));
        [$business, $user] = $this->tenant('laundry');
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertDontSee('href="'.route('suppliers.index').'"', false)->assertDontSee('href="'.route('tables.index').'"', false);
        $this->get(route('suppliers.index'))->assertForbidden();
        $this->get(route('purchases.index'))->assertForbidden();
    }

    public function test_registration_type_cards_preview_and_malformed_type_validation(): void
    {
        $this->get(route('store-registration.create'))->assertOk()->assertSee('business-type-card')->assertSee('Fitur yang akan tersedia')->assertSee('value="bengkel"', false);
        $token = (string) Str::uuid();
        $this->withSession(['store_registration_token' => $token])->post(route('store-registration.store'), ['submission_token' => $token, 'business_type' => ['cafe']])->assertSessionHasErrors('business_type');
    }
}
