<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToBusiness;

class Customer extends Model
{
    use BelongsToBusiness;
    protected $fillable = [
        'name',
        'phone',
        'address',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

}
