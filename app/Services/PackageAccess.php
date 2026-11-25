<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Package;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PackageAccess
{
    public const PREMIUM_MESSAGE = 'Fitur ini tersedia pada paket Premium.';

    public const BRANDING_FIELDS = [
        'app_name', 'company_name', 'app_description', 'receipt_header', 'receipt_footer',
    ];

    public const BRANDING_FILES = ['app_logo', 'app_favicon', 'sidebar_logo'];

    public function currentBusiness(): Business
    {
        // Never resolve the tenant or package from submitted form/query values.
        $id = app(TenantContext::class)->id();
        abort_unless($id, 403, 'Pilih toko terlebih dahulu.');

        return Business::query()->findOrFail($id);
    }

    public function package(Business $business): Package
    {
        // Read current persisted membership: an upgrade/downgrade is immediate,
        // even when a caller holds an older Business instance.
        $current = Business::query()->findOrFail($business->id);
        return $this->forType($current->business_type, $this->effectiveCode($current));
    }

    public function effectiveCode(Business $business): string
    {
        $code = $business->effectiveSubscriptionPlan();
        if ($business->business_type !== null && $code === 'premium'
            && ($business->subscription_status !== 'active' || ($business->subscription_ends_at && ! $business->subscription_ends_at->isFuture()))) {
            return 'free';
        }
        return $code;
    }

    /** Resolve a read-only package projection; the original package ID remains the order FK. */
    public function forType(?string $type, string $code): Package
    {
        $package = DB::transactionLevel() > 0
            ? Package::where('code', $code)->sharedLock()->first()
            : app(PackageSnapshot::class)->base($code);
        abort_unless($package, 403, 'Paket toko tidak tersedia.');
        if ($type === null) return $package;
        $modules = app(BusinessModules::class);
        $type = $modules->normalize($type);
        $settings = DB::transactionLevel() > 0
            ? (\App\Models\PackageVariant::where('package_id', $package->id)->where('business_type', $type)->sharedLock()->first()?->settings ?? [])
            : app(PackageSnapshot::class)->settings($package->id, $type);
        $package->fill($settings);
        $catalog = app(PremiumFeatures::class)->catalog($type, $code);
        foreach (Package::FEATURES as $feature) {
            $module = config('business_modules.package_modules')[$feature] ?? $feature;
            $catalogKey = app(PremiumFeatures::class)->key($feature);
            if (!(bool) ($catalog[$catalogKey]['enabled'] ?? false) || ! in_array($module, $modules->profile($type), true)
                || ($code !== 'premium' && in_array($feature, config('business_modules.premium_only'), true))) {
                $package->setAttribute($feature, false);
            }
        }
        return $package;
    }

    public function allows(string $feature, ?Business $business = null): bool
    {
        $business = Business::findOrFail(($business ?? $this->currentBusiness())->id);
        if ($business->business_type !== null) return app(PremiumFeatures::class)->allows($business, $feature);
        // Legacy uses its original flags and does not receive the new Premium matrix.
        if (!in_array($feature, Package::FEATURES, true)) return false;
        $module = config('business_modules.package_modules')[$feature] ?? null;
        return (! $module || app(BusinessModules::class)->permitsType($business, $module))
            && (bool) $this->package($business)->getAttribute($feature);
    }

    public function ensureFeature(string $feature, ?Business $business = null): void
    {
        abort_unless($this->allows($feature, $business), 403, self::PREMIUM_MESSAGE);
    }

    public function limit(Business $business, string $resource): ?int
    {
        abort_unless(in_array('max_'.$resource, Package::LIMITS, true), 403);
        $limit = $this->package($business)->getAttribute('max_'.$resource);
        $override = match ($resource) {
            'products' => $business->product_limit,
            'users' => $business->user_limit,
            default => null,
        };

        // Keep existing per-store caps without allowing them to bypass a plan.
        return $override === null ? $limit : ($limit === null ? $override : min($limit, $override));
    }

    public function ensureCapacity(Business $business, string $resource, int $additional = 1, string $field = 'name', bool $existingRecord = false): void
    {
        $limit = $this->limit($business, $resource);
        $query = DB::table($resource)->where('business_id', $business->id);
        // Public registrations are pending applications, not POS users yet.
        // An approved but inactive user still occupies a seat.
        $count = (clone $query)->when($resource === 'users', fn ($query) => $query->where('status', 'approved'))->count();
        $legacyUserCapExceeded = $resource === 'users' && $business->user_limit !== null
            && $query->count() + ($existingRecord ? 0 : $additional) > $business->user_limit;
        if ($additional <= 0 || (! $legacyUserCapExceeded && ($limit === null || $count + $additional <= $limit))) {
            return;
        }

        $message = match ($resource) {
            'products' => "Batas {$limit} produk untuk paket {$this->package($business)->name} telah tercapai.",
            'users' => 'Batas pengguna paket Anda telah tercapai.',
            'tables' => 'Batas meja paket Anda telah tercapai.',
        };
        throw ValidationException::withMessages([$field => $message]);
    }
}
