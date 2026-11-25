<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Platform branding is deliberately separate from tenant settings. */
class PlatformSetting extends Model
{
    protected $fillable = ['app_name', 'logo', 'support_email', 'support_phone'];

    public function brandingName(): string
    {
        return trim((string) $this->app_name) ?: config('platform.default_name');
    }

    public function brandingLogo(): string
    {
        return $this->logo ?: config('platform.default_logo');
    }
}
