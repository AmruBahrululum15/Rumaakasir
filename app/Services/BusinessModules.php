<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Str;

class BusinessModules
{
    public function types(): array { return config('business_modules.types'); }
    public function normalize(?string $type): ?string
    {
        if ($type === null || trim($type) === '') return null;
        $code = strtolower(trim($type));
        return config('business_modules.aliases')[$code] ?? $code;
    }
    public function label(?string $type): string
    {
        $code = $this->normalize($type);
        return $code === null ? 'Legacy (fitur existing)' : ($this->types()[$code]['label'] ?? $type);
    }
    public function profile(?string $type): array
    {
        $code = $this->normalize($type);
        return array_values(array_unique([...config('business_modules.core'), ...($code === null ? config('business_modules.legacy') : ($this->types()[$code]['modules'] ?? []))]));
    }
    public function permitsType(Business $business, string $module): bool
    {
        return in_array($module, $this->profile($business->business_type), true);
    }
    public function allows(Business $business, string $module): bool
    {
        if ($business->business_type !== null && isset(config('premium_features.features')[app(PremiumFeatures::class)->key($module)])) return app(PackageAccess::class)->allows($module, $business);
        if (! $this->permitsType($business, $module)) return false;
        if ($business->business_type !== null && in_array($module, config('business_modules.planned'), true)) return false;
        $feature = config('business_modules.module_packages')[$module] ?? null;
        return ! $feature || app(PackageAccess::class)->allows($feature, $business);
    }
    public function tableNavigationLabel(Business $business): string
    {
        return $business->business_type !== null && ! app(PackageAccess::class)->allows('qr_order', $business) ? 'Meja' : 'Meja & QR';
    }

    /** Only implemented modules are advertised as available. */
    public function comparison(?string $type): array
    {
        $access = app(PackageAccess::class);
        $free = $access->forType($type, 'free');
        $premium = $access->forType($type, 'premium');
        $profile = $this->preview($type);
        $rows = [];
        foreach (['pos', 'products', 'categories', 'customers', 'suppliers', 'purchases', 'inventory', 'tables', 'reports'] as $key) {
            if (isset($profile[$key])) $rows[] = ['key' => $key, 'label' => $profile[$key], 'group' => $key === 'reports' ? 'Laporan' : 'Operasional', 'free' => true, 'premium' => true];
        }
        foreach (['products' => 'Jumlah produk', 'users' => 'Jumlah pengguna', 'tables' => 'Jumlah meja'] as $key => $label) {
            if ($key !== 'users' && ! isset($profile[$key])) continue;
            $rows[] = ['key' => 'max_'.$key, 'label' => $label, 'group' => 'Kapasitas', 'free' => $free->{'max_'.$key} ?? 'Unlimited', 'premium' => $premium->{'max_'.$key} ?? 'Unlimited'];
        }
        if ($type !== null) {
            foreach (app(PremiumFeatures::class)->catalog($type) as $key => $feature) {
                if (!$feature['enabled']) continue;
                $rows[] = ['key'=>$key, 'label'=>$feature['label'], 'group'=>$feature['group'], 'free'=>false, 'premium'=>true];
            }
        } else {
        foreach (config('business_modules.feature_labels') as $key => $label) {
            $module = config('business_modules.package_modules')[$key];
            if (! isset($profile[$module])) continue;
            $rows[] = ['key' => $key, 'label' => $label, 'group' => in_array($key, ['export_excel', 'export_pdf']) ? 'Laporan' : 'Fitur Premium', 'free' => (bool) $free->$key, 'premium' => (bool) $premium->$key];
        }
        foreach (['dine_in', 'takeaway'] as $key) {
            if (isset($profile[$key])) $rows[] = ['key' => $key, 'label' => $profile[$key], 'group' => 'Fitur Premium', 'free' => (bool) $free->qr_order, 'premium' => (bool) $premium->qr_order];
        }
        }
        return $rows;
    }

    public function offerPreview(?string $type, string $code): array
    {
        $plan = app(PackageAccess::class)->forType($type, $code);
        $features = [];
        foreach ($this->comparison($type) as $row) {
            if ($row[$code] === false) continue;
            $features[] = $row['label'].(is_bool($row[$code]) ? '' : ': '.$row[$code]);
        }
        return ['name' => $plan->name, 'active' => $plan->is_active, 'monthly' => $plan->price_monthly, 'yearly' => $plan->price_yearly, 'features' => $features];
    }

    public function ensureRoute(Business $business, ?string $route): void
    {
        foreach (config('business_modules.routes') as $pattern => $module) {
            if (Str::is($pattern, $route ?? '')) {
                abort_unless($this->allows($business, $module), 403, 'Modul ini tidak tersedia untuk jenis usaha toko Anda.');
                return;
            }
        }
    }
    public function preview(?string $type): array
    {
        return array_intersect_key(config('business_modules.labels'), array_flip(array_diff($this->profile($type), array_keys($this->planned($type)))));
    }
    public function planned(?string $type): array
    {
        $keys=array_filter(array_intersect($this->profile($type), config('business_modules.planned')),fn($key)=>!app(PremiumFeatures::class)->implemented($key));
        return array_intersect_key(config('business_modules.labels'), array_flip($keys));
    }
}
