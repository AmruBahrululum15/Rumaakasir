<?php

namespace App\Tenancy;

use App\Models\Business;

class TenantContext
{
    private ?Business $business = null;

    public function set(Business $business): void { $this->business = $business; }
    public function clear(): void { $this->business = null; }
    public function business(): ?Business { return $this->business; }
    public function id(): ?int { return $this->business?->getKey(); }
}
