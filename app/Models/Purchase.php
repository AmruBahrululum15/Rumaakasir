<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\BelongsToBusiness;

class Purchase extends Model
{
    use BelongsToBusiness, HasFactory;
    use \App\Models\Concerns\BelongsToOutlet;

    protected $fillable = [
        'code',
        'supplier_id',
        'purchase_date',
        'total',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total' => 'decimal:2',
    ];

    /**
     * Relasi ke Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi ke Detail Pembelian
     */
    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
