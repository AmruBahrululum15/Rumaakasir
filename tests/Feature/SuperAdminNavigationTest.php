<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\PlatformSetting;
use App\Models\Setting;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class SuperAdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    private const MENUS = [
        'super-admin.dashboard', 'businesses.index', 'super-admin.subscriptions.index',
        'super-admin.store-registrations.index', 'super-admin.customer-portal', 'super-admin.platform-customers.index',
        'super-admin.users.index', 'super-admin.owners.index', 'packages.index',
        'super-admin.platform-settings.index', 'super-admin.activities.index',
        'super-admin.notifications.index', 'super-admin.profile.edit', 'super-admin.settings.index',
    ];

    private function userWithRole(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));
        return $user;
    }

    public static function menuPages(): array
    {
        return array_combine(self::MENUS, array_map(fn ($route) => [$route], self::MENUS));
    }

    #[DataProvider('menuPages')]
    public function test_each_menu_opens_without_a_selected_tenant_and_has_correct_active_state(string $route): void
    {
        $admin = $this->userWithRole();
        $admin->business->update(['is_active' => false, 'subscription_status' => 'suspended']);
        $response = $this->actingAs($admin)->get(route($route))->assertOk();
        $dom = new \DOMDocument();
        @$dom->loadHTML($response->getContent());
        $xpath = new \DOMXPath($dom);
        $links = $xpath->query('//aside[@id="sa-sidebar"]//a[contains(@class,"sa-nav-link")]');
        $this->assertCount(count(self::MENUS), $links);
        $this->assertEqualsCanonicalizing(array_map(fn ($name) => route($name), self::MENUS), array_map(fn ($link) => $link->getAttribute('href'), iterator_to_array($links)));
        $active = $xpath->query('//aside[@id="sa-sidebar"]//a[@aria-current="page"]');
        $this->assertCount(1, $active);
        $this->assertSame(route($route), $active->item(0)->getAttribute('href'));
        $this->assertNull(app(TenantContext::class)->id());
    }

    public static function tenantRoles(): array
    {
        return [['Admin'], ['Atasan'], ['Kasir'], ['Gudang']];
    }

    #[DataProvider('tenantRoles')]
    public function test_tenant_roles_cannot_open_any_platform_page_or_write_endpoint(string $role): void
    {
        $this->actingAs($this->userWithRole($role));
        foreach (self::MENUS as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->get(route('businesses.show', 1))->assertForbidden();
        $this->put(route('super-admin.platform-settings.update'), [])->assertForbidden();
        $this->patch(route('super-admin.profile.update'), [])->assertForbidden();
        $this->patch(route('super-admin.notifications.read-all'))->assertForbidden();
        $this->patch(route('super-admin.notifications.read', Str::uuid()))->assertForbidden();
        $this->patch(route('businesses.status', 1), ['is_active' => false])->assertForbidden();
    }

    public function test_global_user_directory_and_owner_filter_use_spatie_roles(): void
    {
        $admin = $this->userWithRole();
        $firstOwner = $this->userWithRole('Atasan');
        $second = Business::create(['name' => 'Second tenant']);
        app(TenantContext::class)->set($second);
        $secondOwner = $this->userWithRole('Atasan');
        $cashier = $this->userWithRole('Kasir');
        app(TenantContext::class)->clear();
        $this->actingAs($admin)->withSession(['super_admin_business_id' => 1])
            ->get(route('super-admin.users.index'))
            ->assertOk()->assertSee($firstOwner->email)->assertSee($secondOwner->email)->assertSee($cashier->email)->assertSee($second->name);
        $this->get(route('super-admin.owners.index'))->assertOk()
            ->assertSee($firstOwner->email)->assertSee($secondOwner->email)->assertDontSee($cashier->email);
    }

    public function test_platform_settings_are_separate_from_tenant_settings(): void
    {
        Storage::fake('public');
        $admin = $this->userWithRole();
        Setting::setValue('app_name', 'Original store');
        $before = Setting::withoutGlobalScope('business')->get()->toArray();
        $this->actingAs($admin)->put(route('super-admin.platform-settings.update'), [
            'app_name' => 'Platform brand', 'support_email' => 'support@example.test',
            'support_phone' => '0800000', 'logo' => UploadedFile::fake()->image('brand.png'), 'business_id' => 999,
        ])->assertSessionHasNoErrors()->assertRedirect(route('super-admin.platform-settings.index'));
        $settings = PlatformSetting::findOrFail(1);
        Storage::disk('public')->assertExists(substr($settings->logo, 8));
        $this->assertSame($before, Setting::withoutGlobalScope('business')->get()->toArray());
        $this->get(route('super-admin.dashboard'))->assertOk()->assertSee('Platform brand');
        $this->put(route('super-admin.platform-settings.update'), ['app_name' => 'Updated brand'])
            ->assertSessionHasNoErrors();
        $this->assertSame($settings->logo, $settings->fresh()->logo);
        $this->assertSame(1, PlatformSetting::count());
    }

    public function test_notifications_only_read_current_admin_records(): void
    {
        $admin = $this->userWithRole();
        $other = $this->userWithRole();
        $mine = $admin->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['title' => 'My notification']]);
        $theirs = $other->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['title' => 'Other private notification']]);
        $this->actingAs($admin)->get(route('super-admin.notifications.index'))
            ->assertOk()->assertSee('My notification')->assertDontSee('Other private notification');
        $this->patch(route('super-admin.notifications.read', $theirs->id))->assertNotFound();
        $this->from(route('super-admin.notifications.index'))->patch(route('super-admin.notifications.read', $mine->id))->assertRedirect(route('super-admin.notifications.index'));
        $this->assertNotNull($mine->fresh()->read_at);
        $second = $admin->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['message' => 'Second']]);
        $this->patch(route('super-admin.notifications.read-all'))->assertRedirect();
        $this->assertNotNull($second->fresh()->read_at);
        $this->assertNull($theirs->fresh()->read_at);
    }

    public function test_profile_and_password_reuse_existing_actions_without_tenant_selection(): void
    {
        $admin = $this->userWithRole();
        $originalBusiness = $admin->business_id;
        $this->actingAs($admin)->patch(route('super-admin.profile.update'), [
            'name' => 'Updated administrator', 'email' => $admin->email, 'business_id' => 999, 'is_active' => false,
        ])->assertSessionHasNoErrors()->assertRedirect(route('super-admin.profile.edit'));
        $this->assertSame('Updated administrator', $admin->fresh()->name);
        $this->assertSame($originalBusiness, $admin->fresh()->business_id);
        $this->assertTrue($admin->fresh()->is_active);
        $this->from(route('super-admin.settings.index'))->put(route('password.update'), [
            'current_password' => 'password', 'password' => 'new-password-secure', 'password_confirmation' => 'new-password-secure',
        ])->assertSessionHasNoErrors()->assertRedirect(route('super-admin.settings.index'));
        $this->assertTrue(Hash::check('new-password-secure', $admin->fresh()->password));
    }

    public function test_business_detail_and_status_preserve_users_and_subscription(): void
    {
        $admin = $this->userWithRole();
        $business = $admin->business;
        $before = $business->only('subscription_plan', 'subscription_status');
        $this->actingAs($admin)->get(route('businesses.show', $business))->assertOk()->assertSee($business->name);
        $this->patch(route('businesses.status', $business), ['is_active' => false, 'subscription_plan' => 'premium'])
            ->assertRedirect(route('businesses.index'));
        $this->assertFalse($business->fresh()->is_active);
        $this->assertSame($before, $business->fresh()->only('subscription_plan', 'subscription_status'));
        $this->assertNotNull($admin->fresh());
        $this->patch(route('businesses.status', $business), ['is_active' => true])->assertRedirect();
        $this->assertTrue($business->fresh()->is_active);
    }
}
