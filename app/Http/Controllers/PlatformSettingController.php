<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformSettingController extends Controller
{
    public function index(): View
    {
        return view('super-admin.platform-settings', [
            'settings' => PlatformSetting::find(1) ?? new PlatformSetting([
                'app_name' => config('platform.default_name'),
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($data['logo']);
        if ($request->hasFile('logo')) {
            $data['logo'] = 'storage/'.$request->file('logo')->store('platform', 'public');
        }
        $oldLogo = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $settings = PlatformSetting::lockForUpdate()->find(1);
            $oldLogo = $settings?->logo;
            PlatformSetting::updateOrCreate(['id' => 1], $data);
            return $oldLogo;
        });
        // Only generated uploads owned by the platform may be removed.
        // Never delete a bundled/default logo or a tenant file.
        if (isset($data['logo']) && $oldLogo !== $data['logo'] && is_string($oldLogo)
            && preg_match('#^storage/platform/[A-Za-z0-9]{40}\.(png|jpe?g|webp)$#iD', $oldLogo)
            && ! PlatformSetting::where('logo', $oldLogo)->exists()
            && ! \Illuminate\Support\Facades\DB::table('settings')->whereIn('value', [$oldLogo, substr($oldLogo, 8)])->exists()) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(substr($oldLogo, 8));
        }

        return to_route('super-admin.platform-settings.index')->with('success', 'Pengaturan platform berhasil disimpan.');
    }
}
