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

    protected $fillable = [
        'name', 'slug', 'phone', 'address', 'logo', 'is_active',
        'subscription_plan', 'subscription_status', 'subscription_ends_at',
        'user_limit', 'product_limit',
    ];

    protected $casts = [
        'subscription_started_at' => 'datetime',
        'is_active' => 'boolean',
        'subscription_ends_at' => 'datetime',
        'user_limit' => 'integer',
        'product_limit' => 'integer',
    ];

    protected $attributes = [
        'is_active' => true,
        'subscription_plan' => 'free',
        'subscription_status' => 'active',
    ];

    public function allowsOperations(): bool
    {
        // Typed tenants retain Free operations after a paid/admin Premium term expires.
        // Null-type legacy tenants keep their original operational access rules.
        if ($this->business_type !== null && $this->subscription_plan === 'premium'
            && in_array($this->subscription_status, ['active', 'expired'], true)
            && $this->subscription_ends_at?->isPast()) {
            return $this->is_active;
        }
        // Approved Premium applications use Free until an actual payment settles.
        if ($this->subscription_source === 'store_registration' && $this->subscription_plan === 'free'
            && $this->subscription_status === 'pending_payment') {
            return $this->is_active;
        }
        // Paid DOKU terms expire into Free. Administrative suspension and all
        // legacy platform-controlled access rules retain their existing meaning.
        if (in_array($this->subscription_source, ['doku', 'midtrans'], true)
            && in_array($this->subscription_status, ['active', 'expired'], true)) {
            return $this->is_active;
        }
        return $this->is_active && $this->subscription_status === 'active'
            && (! $this->subscription_ends_at || $this->subscription_ends_at->isFuture());
    }

    public function effectiveSubscriptionPlan(): string
    {
        if (in_array($this->subscription_source, ['doku', 'midtrans'], true)
            && ($this->subscription_status !== 'active' || ! $this->subscription_ends_at?->isFuture())) {
            return 'free';
        }
        return $this->subscription_plan;
    }

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
