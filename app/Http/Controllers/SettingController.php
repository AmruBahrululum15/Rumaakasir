<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SettingController extends Controller
{
    /**
     * ============================================================
     * HALAMAN PENGATURAN
     * ============================================================
     */
    public function index()
    {
        $settings = Setting::allAsArray();

        return view(
            'settings.index',
            compact('settings')
        );
    }

    /**
     * ============================================================
     * UPDATE PENGATURAN
     * ============================================================
     */
    public function update(Request $request)
    {
        $validated = $request->validate([

            /*
            |--------------------------------------------------------------------------
            | INFORMASI APLIKASI
            |--------------------------------------------------------------------------
            */

            'app_name' => [
                'required',
                'string',
                'max:100',
            ],

            'company_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'business_address' => ['nullable', 'string', 'max:500'],
            'business_phone' => ['nullable', 'string', 'max:50'],
            'business_email' => ['nullable', 'email', 'max:255'],

            'app_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | STRUK
            |--------------------------------------------------------------------------
            */

            'receipt_header' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'receipt_footer' => [
                'nullable',
                'string',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | LOGO
            |--------------------------------------------------------------------------
            */

            'app_logo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            /*
            |--------------------------------------------------------------------------
            | FAVICON
            |--------------------------------------------------------------------------
            */

            'app_favicon' => [
                'nullable',
                'image',
                'mimes:png,ico,jpg,jpeg',
                'max:1024',
            ],

        ], [

            'app_name.required' => 'Nama aplikasi wajib diisi.',

            'app_name.max' => 'Nama aplikasi maksimal 100 karakter.',

            'receipt_header.max' => 'Header struk maksimal 1000 karakter.',

            'receipt_footer.max' => 'Footer struk maksimal 1000 karakter.',

            'app_logo.image' => 'File logo harus berupa gambar.',

            'app_logo.mimes' => 'Logo harus berformat JPG, JPEG, PNG, atau WEBP.',

            'app_logo.max' => 'Ukuran logo maksimal 2 MB.',

            'app_favicon.image' => 'Favicon harus berupa gambar.',

            'app_favicon.mimes' => 'Favicon harus berformat PNG, ICO, JPG, atau JPEG.',

            'app_favicon.max' => 'Ukuran favicon maksimal 1 MB.',
        ]);

        try {

            /*
            |--------------------------------------------------------------------------
            | INFORMASI APLIKASI
            |--------------------------------------------------------------------------
            */

            Setting::setValue(
                'app_name',
                $validated['app_name']
            );

            Setting::setValue(
                'company_name',
                $validated['company_name'] ?? ''
            );

            Setting::setValue(
                'app_description',
                $validated['app_description'] ?? ''
            );

            Setting::setValue('business_address', $validated['business_address'] ?? '');
            Setting::setValue('business_phone', $validated['business_phone'] ?? '');
            Setting::setValue('business_email', $validated['business_email'] ?? '');

            /*
            |--------------------------------------------------------------------------
            | HEADER STRUK
            |--------------------------------------------------------------------------
            */

            Setting::setValue(
                'receipt_header',
                $validated['receipt_header'] ?? ''
            );

            /*
            |--------------------------------------------------------------------------
            | FOOTER STRUK
            |--------------------------------------------------------------------------
            */

            Setting::setValue(
                'receipt_footer',
                $validated['receipt_footer'] ?? ''
            );

            /*
            |--------------------------------------------------------------------------
            | LOGO
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('app_logo')) {
                $oldLogo = Setting::getValue('app_logo');
                $logoPath =
                    $request
                        ->file('app_logo')
                        ->store(
                            'settings',
                            'public'
                        );

                Setting::setValue(
                    'app_logo',
                    $logoPath
                );
                if ($oldLogo && $oldLogo !== $logoPath && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FAVICON
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('app_favicon')) {
                $oldFavicon = Setting::getValue('app_favicon');
                $faviconPath =
                    $request
                        ->file('app_favicon')
                        ->store(
                            'settings',
                            'public'
                        );

                Setting::setValue(
                    'app_favicon',
                    $faviconPath
                );
                if ($oldFavicon && $oldFavicon !== $faviconPath && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | BERHASIL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('settings.index')
                ->with(
                    'success',
                    'Pengaturan aplikasi berhasil diperbarui.'
                );

        } catch (Throwable $e) {

            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Pengaturan gagal disimpan: '
                    .$e->getMessage()
                );
        }
    }
}
