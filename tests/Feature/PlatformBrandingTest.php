<?php

namespace Tests\Feature;

use App\Models\{PlatformSetting, User};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\Concerns\RefreshDatabase;
use Tests\TestCase;

class PlatformBrandingTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));
        $this->actingAs($user);
    }

    public function test_saved_branding_immediately_updates_public_login_and_copyright(): void
    {
        Storage::fake('public');
        $this->administrator();
        $this->get(route('super-admin.platform-settings.index'))->assertOk()->assertSee('Branding Aplikasi')->assertSee('Nama Platform')->assertSee('platform-logo-preview');
        $this->put(route('super-admin.platform-settings.update'), ['app_name' => 'Rumaa POS', 'logo' => UploadedFile::fake()->image('logo.png')])->assertSessionHasNoErrors()->assertRedirect();
        $path = PlatformSetting::findOrFail(1)->logo;
        Storage::disk('public')->assertExists(substr($path, 8));
        $this->post(route('logout'));
        $this->get(route('login'))->assertOk()->assertSee('Rumaa POS')->assertSee($path)
            ->assertSee('Rumaa POS. All rights reserved.')->assertSee(route('store-registration.create'));
    }

    public function test_replacement_deletes_only_unreferenced_generated_platform_upload(): void
    {
        Storage::fake('public');
        $this->administrator();
        $old = 'platform/'.str_repeat('a', 40).'.png';
        Storage::disk('public')->put($old, 'old-platform-logo');
        PlatformSetting::create(['id' => 1, 'app_name' => 'Old', 'logo' => 'storage/'.$old]);
        $this->put(route('super-admin.platform-settings.update'), ['app_name' => 'New', 'logo' => UploadedFile::fake()->image('new.png')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->put('settings/tenant.png', 'tenant-logo');
        PlatformSetting::find(1)->update(['logo' => 'storage/settings/tenant.png']);
        $this->put(route('super-admin.platform-settings.update'), ['app_name' => 'New', 'logo' => UploadedFile::fake()->image('new.png')])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists('settings/tenant.png');
        $this->assertFileExists(public_path(config('platform.default_logo')));
    }

    public function test_invalid_and_oversized_logo_are_rejected(): void
    {
        Storage::fake('public');
        $this->administrator();
        foreach ([UploadedFile::fake()->create('logo.svg', 20, 'image/svg+xml'), UploadedFile::fake()->image('large.jpg')->size(2049)] as $file) {
            $this->put(route('super-admin.platform-settings.update'), ['app_name' => 'Test', 'logo' => $file])->assertSessionHasErrors('logo');
        }
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
