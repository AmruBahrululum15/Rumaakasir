<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;

class Product extends Model
{
    use BelongsToBusiness, HasFactory;

    /*
    |--------------------------------------------------------------------------
    | MASS ASSIGNMENT
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'name',
        'category_id',
        'supplier_id',
        'price',
        'stock',
        'image',
        'status',
        'is_active',
    ];


    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

protected $appends = [
    'image_url',
    'stock_status',
    'stock_status_label',
];
    /*
    |--------------------------------------------------------------------------
    | RELASI KATEGORI
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(
            Category::class,
            'category_id'
        );
    }

    /**
     * Supplier produk, bila produk memiliki pemasok utama.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }


    /*
    |--------------------------------------------------------------------------
    | URL GAMBAR
    |--------------------------------------------------------------------------
    */

    public function getImageUrlAttribute()
    {
        if (!$this->image) {

            return asset(
                'images/product-placeholder.png'
            );
        }

        return asset(
            'storage/' . $this->image
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS STOK
    |--------------------------------------------------------------------------
    */

    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {

            return 'habis';

        }

        if ($this->stock <= 5) {

            return 'menipis';

        }

        return 'tersedia';
    }


    /*
    |--------------------------------------------------------------------------
    | LABEL STATUS STOK
    |--------------------------------------------------------------------------
    */

    public function getStockStatusLabelAttribute()
    {
        return match ($this->stock_status) {

            'habis' =>
                'Stok Habis',

            'menipis' =>
                'Stok Menipis',

            default =>
                'Stok Tersedia',

        };
    }
}
