<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Services\PackageAccess;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnforceTenantCapacity
{
    public function handle(Request $request, Closure $next, string $resource, string $mode = 'single'): Response
    {
        abort_unless(in_array($resource, ['users', 'products', 'tables'], true), 403);

        // Holding the tenant row lock through the insert prevents concurrent
        // requests from both consuming the last available slot.
        return DB::transaction(function () use ($request, $next, $resource, $mode) {
            $business = Business::query()->lockForUpdate()
                ->findOrFail(app(TenantContext::class)->id() ?? 1);
            abort_unless($business->allowsOperations(), 403, 'Toko atau langganan tidak aktif.');
            $additional = 1;
            $field = 'name';
            if ($resource === 'tables' && $mode === 'generate') {
                $data = $request->validate(['jumlah' => ['required', 'integer', 'min:1', 'max:500']]);
                // Existing generator reuses inactive tables before creating rows.
                $additional = max(0, (int) $data['jumlah'] - DB::table('tables')->where('business_id', $business->id)->count());
                $field = 'jumlah';
            }
            app(PackageAccess::class)->ensureCapacity($business, $resource, $additional, $field, $mode === 'approve');

            return $next($request);
        });
    }
}
