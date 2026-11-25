<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\BelongsToBusiness;

class Sale extends Model
{
    use BelongsToBusiness, HasFactory;
    use \App\Models\Concerns\BelongsToOutlet;

    protected $fillable = [
        'code',
        'sale_date',
        'total',
        'paid',
        'change',
        'notes',
        'customer_id',
        'user_id',
        'payment_method',
        'payment_status',
        'order_source',
        'order_status',
        'table_id',
        'loyalty_redeem_points',
        'loyalty_discount',
        'promotion_discount',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'change' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function (Sale $sale) {
            if (in_array($sale->order_source,['customer_portal','table_qr'],true) && $sale->payment_status==='PAID' && $sale->wasChanged('payment_status')) app(\App\Services\CustomerOrderNotifications::class)->send($sale,'payment_received');
            if($sale->outlet_id && (preg_match('/^STOCK_RESTORED=1$/m',(string)$sale->notes) || (str_contains((string)$sale->getRawOriginal('notes'),'STOCK_RESERVED=1') && str_contains((string)$sale->notes,'STOCK_RESERVED=0')))) app(\App\Services\OutletInventory::class)->restoreAllocation($sale);
            if ($sale->wasRecentlyCreated || $sale->wasChanged('payment_status')) app(\App\Services\LoyaltyService::class)->settle($sale);
            if ($sale->wasRecentlyCreated || $sale->wasChanged('payment_status')) app(\App\Services\PortalPurchaseLink::class)->record($sale);
        });
    }

    public function saleDetails(): HasMany
    {
        return $this->hasMany(
            SaleDetail::class
        );
    }

    public function scopeValidForTotals(\Illuminate\Database\Eloquent\Builder $query): void
    {
        if(app(\App\Tenancy\TenantContext::class)->business()?->business_type!==null) {
            $query->where(fn($q)=>$q->whereNull('sales.payment_status')->orWhereNotIn('sales.payment_status',['REFUNDED','VOIDED']));
        }
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /** Safe display fallbacks for records created before transaction context existed. */
    public function getCustomerDisplayNameAttribute(): string
    {
        return $this->customer?->name ?: ($this->noteValue('CUSTOMER_NAME') ?: 'Umum');
    }

    public function getCashierDisplayNameAttribute(): string
    {
        return $this->user?->name ?: ($this->user?->username ?: ($this->user?->email ?: '-'));
    }

    public function getPaymentMethodDisplayAttribute(): string
    {
        return strtoupper($this->payment_method ?: ($this->noteValue('PAYMENT_METHOD') ?: 'CASH'));
    }

    public function getPaymentStatusDisplayAttribute(): string
    {
        return strtoupper($this->payment_status ?: ($this->noteValue('PAYMENT_STATUS') ?: 'PENDING'));
    }

    public function getOrderSourceDisplayAttribute(): string
    {
        if ($this->order_source === 'customer_portal') return 'Customer Portal';
        return ($this->order_source ?: ($this->noteValue('ORDER_TYPE') === 'QR_TABLE' ? 'table_qr' : 'cashier')) === 'table_qr' ? 'QR Meja' : 'Kasir';
    }

    private function noteValue(string $key): ?string
    {
        foreach (preg_split('/\r\n|\r|\n/', (string) $this->notes) ?: [] as $line) {
            if (str_starts_with(trim($line), $key . '=')) {
                return trim(substr(trim($line), strlen($key) + 1));
            }
        }
        return null;
    }

}
