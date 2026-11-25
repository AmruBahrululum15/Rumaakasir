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
        $this->app->scoped(\App\Tenancy\OutletContext::class);
        $this->app->scoped(\App\Services\PackageSnapshot::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('customer-link', fn (\Illuminate\Http\Request $r) =>
            \Illuminate\Cache\RateLimiting\Limit::perHour(5)->by('customer-link:'.auth('web')->id()));
        \Illuminate\Support\Facades\RateLimiter::for('portal-auth', fn (\Illuminate\Http\Request $r) =>
            \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by('portal-auth:'.$r->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('portal-chat', fn (\Illuminate\Http\Request $r) =>
            \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('portal-chat:'.($r->routeIs('customer.*')?'customer:'.auth('customer')->id():'staff:'.auth('web')->id()).':'.$r->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('portal-write', fn (\Illuminate\Http\Request $r) =>
            \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('portal-write:'.auth('customer')->id().':'.$r->ip()));
        foreach ([\App\Models\Package::class, \App\Models\PackageVariant::class] as $model) {
            $model::saved(fn () => app(\App\Services\PackageSnapshot::class)->clear());
            $model::deleted(fn () => app(\App\Services\PackageSnapshot::class)->clear());
        }
        \Illuminate\Support\Facades\Blade::if('businessModule', function (string $module) {
            $business = app(TenantContext::class)->business();
            return $business && app(\App\Services\BusinessModules::class)->allows($business, $module);
        });
        \Illuminate\Support\Facades\RateLimiter::for('store-registration', fn (\Illuminate\Http\Request $request) =>
            \Illuminate\Cache\RateLimiting\Limit::perHour(5)->by($request->ip()));
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
