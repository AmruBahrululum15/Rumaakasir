<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $business = $request->user()?->business;

        if (! $business || ! $business->is_active) {
            abort(403, 'Akun Anda tidak terhubung ke toko aktif.');
        }

        app(TenantContext::class)->set($business);

        return $next($request);
    }
}
