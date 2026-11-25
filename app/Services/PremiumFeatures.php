<?php

namespace App\Services;

use App\Models\Business;

class PremiumFeatures
{
    public function key(string $key): string { return config('premium_features.aliases')[$key] ?? $key; }
    public function definitions(?string $type): array
    {
        if ($type === null) return [];
        $type = app(BusinessModules::class)->normalize($type);
        if (!array_key_exists($type, config('business_modules.types'))) return [];
        return array_filter(config('premium_features.features'), fn ($feature) => $feature['types'] === null || in_array($type, $feature['types'], true));
    }
    public function implemented(string $key): bool
    {
        $definition = config('premium_features.features')[$this->key($key)] ?? null;
        return $definition && $definition['implemented'];
    }
    public function catalog(?string $type, string $code = 'premium'): array
    {
        $result = [];
        if ($type === null) return $result;
        $snapshot = app(PackageSnapshot::class);
        $base = $snapshot->base($code);
        if (!$base) return [];
        $settings = $snapshot->settings($base->id, app(BusinessModules::class)->normalize($type));
        foreach ($this->definitions($type) as $key => $definition) {
            $state = $settings['feature_statuses'][$definition['inherits'] ?? $key] ?? $definition['status'];
            if (!in_array($state, config('premium_features.states'), true)) $state = 'disabled';
            if (!$definition['implemented'] && $state === 'available') $state = 'disabled';
            if (isset($definition['provider']) && blank(config($definition['provider'])) && $state === 'available') $state = 'disabled';
            $flag = $definition['package_flag'] ?? null;
            if ($state === 'available' && $flag && !($settings[$flag] ?? $base->$flag)) $state = 'disabled';
            $result[$key] = $definition + ['key'=>$key];
            $result[$key]['status'] = $state;
            $result[$key]['enabled'] = $code === 'premium' && $state === 'available';
        }
        return $result;
    }
    public function allows(Business $business, string $key): bool
    {
        if ($business->business_type === null || !$business->is_active) return false;
        if (app(PackageAccess::class)->effectiveCode($business) !== 'premium') return false;
        return $this->catalog($business->business_type)[$this->key($key)]['enabled'] ?? false;
    }
}
