<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Services\PackageAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceBrandingPackage
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app(PackageAccess::class)->allows('custom_branding')) {
            foreach (PackageAccess::BRANDING_FILES as $field) {
                abort_if($request->hasFile($field) || $request->filled($field), 403, PackageAccess::PREMIUM_MESSAGE);
            }
            foreach (PackageAccess::BRANDING_FIELDS as $field) {
                $stored = Setting::getValue($field, $field === 'app_name' ? 'POS Enterprise' : '');
                $submitted = $request->input($field);
                abort_if($request->exists($field) && (! is_scalar($submitted) && $submitted !== null
                    || (string) $submitted !== (string) $stored), 403, PackageAccess::PREMIUM_MESSAGE);
                // Preserve branding when the Free contact-settings form omits it.
                $request->merge([$field => $stored]);
            }
        }

        return $next($request);
    }
}
