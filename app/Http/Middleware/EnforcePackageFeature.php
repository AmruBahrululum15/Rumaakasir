<?php

namespace App\Http\Middleware;

use App\Services\PackageAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePackageFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $access = app(PackageAccess::class);
        $business = $access->currentBusiness();
        if ($business->business_type !== null && ! $access->allows($feature, $business)
            && $request->isMethod('GET') && ! $request->expectsJson() && $request->user()->hasRole('Atasan')) {
            // Do not convert cross-tenant table probes into an upgrade redirect.
            if ($table = $request->route('table')) {
                $id = $table instanceof \App\Models\Table ? $table->id : $table;
                abort_unless(\App\Models\Table::where('business_id', $business->id)->whereKey($id)->exists(), 404);
            }
            $module = config('business_modules.package_modules')[$feature] ?? $feature;
            $catalogKey = app(\App\Services\PremiumFeatures::class)->key($feature);
            if (isset(app(\App\Services\PremiumFeatures::class)->definitions($business->business_type)[$catalogKey]) || app(\App\Services\BusinessModules::class)->permitsType($business, $module)) {
                $entry = app(\App\Services\PremiumFeatures::class)->catalog($business->business_type)[$catalogKey] ?? null;
                $label = $entry['label'] ?? (config('business_modules.feature_labels')[$feature] ?? 'Fitur ini');
                return redirect()->route('subscriptions.index')->with('info', $label.($entry && $entry['status'] !== 'available' ? ' belum tersedia pada paket toko Anda.' : ' tersedia pada paket Premium.'));
            }
        }
        $access->ensureFeature($feature, $business);

        return $next($request);
    }
}
