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
        'table_id',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'change' => 'decimal:2',
    ];

    public function saleDetails(): HasMany
    {
        return $this->hasMany(
            SaleDetail::class
        );
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
