<?php

namespace App\Http\Middleware;

use App\Models\Table;
use App\Services\PackageAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceQrPackage
{
    public function handle(Request $request, Closure $next): Response
    {
        // Public QR requests have no login tenant. Resolve only the route's table,
        // just as the existing QR controllers do; ignore body/query business_id.
        $query = Table::withoutGlobalScope('business');
        $table = $request->route('qr_token') !== null
            ? $query->where('qr_token', $request->route('qr_token'))->firstOrFail()
            : $query->findOrFail($request->route('table'));
        abort_unless($table->business?->allowsOperations(), 404);
        app(PackageAccess::class)->ensureFeature('qr_order', $table->business);

        return $next($request);
    }
}
