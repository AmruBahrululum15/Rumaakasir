<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Product;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    /**
     * Halaman menu pelanggan berdasarkan QR meja.
     */
    public function menu($qr_token)
    {
        // Cari meja berdasarkan QR token
        $table = Table::withoutGlobalScopes()->where('qr_token', $qr_token)
            ->where('is_active', 1)
            ->first();

        // Jika QR tidak ditemukan / meja tidak aktif
        if (!$table || ! $table->business?->allowsOperations()) {
            abort(404, 'QR Meja tidak ditemukan atau meja sedang tidak aktif.');
        }

        app(TenantContext::class)->set($table->business);

        // Ambil produk yang aktif dan masih memiliki stok
        $products = Product::with('category')
            ->where('business_id', $table->business_id)
            ->where('is_active', 1)
            ->where('stock', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return view('customer.menu', compact(
            'table',
            'products'
        ));
    }
}
