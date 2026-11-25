<?php

namespace App\Http\Middleware;

use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = app(TenantContext::class);
        $context->clear();
        app(\App\Tenancy\OutletContext::class)->clear();
        app(\App\Services\PackageSnapshot::class)->clear();

        try {
            return $next($request);
        } finally {
            app(\App\Tenancy\OutletContext::class)->clear();
            $context->clear();
        app(\App\Services\PackageSnapshot::class)->clear();
        }
    }
}
