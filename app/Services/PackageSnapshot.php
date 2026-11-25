<?php

namespace App\Services;

use App\Models\{Package, PackageVariant};

/** Request-scoped, small package catalog. Never caches tenant membership or credentials. */
class PackageSnapshot
{
    private ?array $packages = null;
    private ?array $variants = null;

    public function clear(): void { $this->packages = $this->variants = null; }
    private function load(): void
    {
        if ($this->packages !== null) return;
        $this->packages = Package::all()->keyBy('code')->all();
        $this->variants = PackageVariant::all()->keyBy(fn ($row) => $row->package_id.':'.$row->business_type)->all();
    }
    public function base(string $code): ?Package
    {
        $this->load();
        return isset($this->packages[$code]) ? clone $this->packages[$code] : null;
    }
    public function settings(int $id, string $type): array
    {
        $this->load();
        return $this->variants[$id.':'.$type]->settings ?? [];
    }
}
