<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSubscriptionOwner
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        // Super Admin may hold other roles but never purchases for a selected tenant.
        abort_unless($user && ! $user->hasRole('Super Admin') && $user->hasAnyRole(['Atasan', 'Pemilik']), 403);
        return $next($request);
    }
}
