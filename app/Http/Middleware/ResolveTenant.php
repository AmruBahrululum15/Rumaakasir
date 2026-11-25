<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->is_active && $user->status === 'approved', 403, 'Akun Anda tidak aktif.');
        $business = $user?->business;

        // Super Admin is not permanently bound to one store. They operate the
        // normal POS through one explicitly selected tenant at a time, which
        // preserves the existing global business_id scopes.
        if ($user?->hasRole('Super Admin')) {
            $selectedId = $request->session()->get('super_admin_business_id');
            if (! $selectedId) {
                return redirect()->route('businesses.index')->with('warning', 'Pilih toko terlebih dahulu.');
            }
            $business = Business::query()->find($selectedId);
        }

        if (! $business || ! $business->allowsOperations()) {
            abort(403, 'Akun Anda tidak terhubung ke toko aktif.');
        }

        app(TenantContext::class)->set($business);

        try {
            app(\App\Tenancy\OutletContext::class)->resolve($request,$business);
            app(\App\Services\BusinessModules::class)->ensureRoute($business, $request->route()?->getName());
            return $next($request);
        } finally {
            app(\App\Tenancy\OutletContext::class)->clear();
            app(TenantContext::class)->clear();
        }
    }
}
