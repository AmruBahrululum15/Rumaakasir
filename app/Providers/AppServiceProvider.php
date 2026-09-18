<?php

namespace App\Providers;

use App\Models\Setting;
use App\Tenancy\TenantContext;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Scoped prevents tenant state from leaking between requests in Octane/queue workers.
        $this->app->scoped(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Gunakan Pagination Bootstrap
        |--------------------------------------------------------------------------
        | AdminLTE 3 menggunakan Bootstrap.
        | Jangan menggunakan pagination Tailwind bawaan Laravel.
        */

        Paginator::useBootstrapFive();

        // One branding source for guest pages, AdminLTE, receipts, and the app.
        View::composer('*', function ($view): void {
            $settings = Setting::allAsArray();
            $view->with('branding', [
                'name' => ($settings['app_name'] ?? '') ?: 'POS Enterprise',
                'description' => $settings['app_description'] ?? '',
                'logo' => ($settings['sidebar_logo'] ?? null) ?: ($settings['app_logo'] ?? null),
            ]);
        });
    }
}
