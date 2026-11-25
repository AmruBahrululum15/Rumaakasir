<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageVariant extends Model
{
    protected $fillable = ['package_id', 'business_type', 'settings'];
    protected $casts = ['settings' => 'array'];
}
