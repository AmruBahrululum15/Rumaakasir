<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCredentialsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_credentials) {
            return redirect()->route('first-login.edit')
                ->with('warning', 'Untuk keamanan, ubah username dan password awal sebelum melanjutkan.');
        }

        return $next($request);
    }
}
