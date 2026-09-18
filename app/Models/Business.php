<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'phone', 'address', 'logo', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(function (Business $business): void {
            $business->owner_id ??= Auth::id();
            $business->slug ??= Str::slug($business->name).'-'.Str::lower(Str::random(6));
        });
    }

    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function users(): HasMany { return $this->hasMany(User::class); }
    public function categories(): HasMany { return $this->hasMany(Category::class); }
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public function suppliers(): HasMany { return $this->hasMany(Supplier::class); }
    public function purchases(): HasMany { return $this->hasMany(Purchase::class); }
    public function purchaseDetails(): HasMany { return $this->hasMany(PurchaseDetail::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
    public function saleDetails(): HasMany { return $this->hasMany(SaleDetail::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function tables(): HasMany { return $this->hasMany(Table::class); }
    public function settings(): HasMany { return $this->hasMany(Setting::class); }
}
