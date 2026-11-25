<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public const LIMITS = ['max_products', 'max_users', 'max_tables'];

    public const FEATURES = ['export_excel', 'export_pdf', 'custom_branding', 'ai_agent', 'qr_order'];

    protected $fillable = ['name', 'price_monthly', 'price_yearly', 'is_active', ...self::LIMITS, ...self::FEATURES];

    protected $casts = [
        'price_monthly' => 'integer', 'price_yearly' => 'integer', 'is_active' => 'boolean',
        'max_products' => 'integer', 'max_users' => 'integer', 'max_tables' => 'integer',
        'export_excel' => 'boolean', 'export_pdf' => 'boolean', 'custom_branding' => 'boolean',
        'ai_agent' => 'boolean', 'qr_order' => 'boolean',
    ];
}
