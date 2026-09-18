<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;

class Supplier extends Model
{
    use BelongsToBusiness;
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'address',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function purchases()
{
    return $this->hasMany(Purchase::class);
}
}
