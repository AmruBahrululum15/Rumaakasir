<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Local ngrok terminates HTTPS before forwarding to the HTTP server.
        // Trust only its loopback connection and protocol header, not arbitrary
        // proxy hosts, client IP headers, or every address on the network.
        $middleware->trustProxies(
            at: ['127.0.0.1', '::1'],
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
        );
        $middleware->prepend(\App\Http\Middleware\ResetTenantContext::class);
        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ResolveTenant::class,
        );
        $middleware->prependToPriorityList(
            \App\Http\Middleware\ResolveTenant::class,
            \App\Http\Middleware\EnsureCredentialsChanged::class,
        );
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'credentials.changed' => \App\Http\Middleware\EnsureCredentialsChanged::class,
            'tenant' => \App\Http\Middleware\ResolveTenant::class,
            'tenant.capacity' => \App\Http\Middleware\EnforceTenantCapacity::class,
            'package.feature' => \App\Http\Middleware\EnforcePackageFeature::class,
            'package.qr' => \App\Http\Middleware\EnforceQrPackage::class,
            'package.branding' => \App\Http\Middleware\EnforceBrandingPackage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['midtrans_server_key']);
    })
    ->create();
