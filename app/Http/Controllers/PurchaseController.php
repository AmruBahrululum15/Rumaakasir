<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseController extends Controller
{
    /**
     * Menampilkan daftar pembelian.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | QUERY DASAR
        |--------------------------------------------------------------------------
        */

        $query = Purchase::with('supplier')
            ->latest('id');


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'code',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas(
                    'supplier',
                    function ($supplier) use ($search) {

                        $supplier->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );

                    }
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $purchases = $query
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'purchases.index',
            compact('purchases')
        );
    }


    /**
     * Form tambah pembelian.
     */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | DATA SUPPLIER
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DATA PRODUK
        |--------------------------------------------------------------------------
        |
        | Hanya produk aktif yang ditampilkan.
        |
        */

        $products = Product::where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GENERATE KODE PEMBELIAN
        |--------------------------------------------------------------------------
        */

        $lastPurchase = Purchase::orderBy(
            'id',
            'desc'
        )->first();


        if ($lastPurchase) {

            $lastNumber = (int) str_replace(
                'PB-',
                '',
                $lastPurchase->code
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;

        }


        $code = 'PB-' . str_pad(
            $nextNumber,
            4,
            '0',
            STR_PAD_LEFT
        );


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'purchases.create',
            compact(
                'suppliers',
                'products',
                'code'
            )
        );
    }


    /**
     * Menyimpan pembelian baru.
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'supplier_id' => [
                'required',
                'exists:suppliers,id'
            ],

            'purchase_date' => [
                'required',
                'date'
            ],

            'products' => [
                'required',
                'array',
                'min:1'
            ],

            'products.*.product_id' => [
                'required',
                'exists:products,id'
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],

            'products.*.price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'notes' => [
                'nullable',
                'string'
            ],

        ], [

            'supplier_id.required' =>
                'Supplier wajib dipilih.',

            'supplier_id.exists' =>
                'Supplier tidak ditemukan.',

            'purchase_date.required' =>
                'Tanggal pembelian wajib diisi.',

            'purchase_date.date' =>
                'Format tanggal pembelian tidak valid.',

            'products.required' =>
                'Minimal satu produk harus ditambahkan.',

            'products.min' =>
                'Minimal satu produk harus ditambahkan.',

            'products.*.product_id.required' =>
                'Produk wajib dipilih.',

            'products.*.product_id.exists' =>
                'Produk tidak ditemukan.',

            'products.*.quantity.required' =>
                'Jumlah produk wajib diisi.',

            'products.*.quantity.integer' =>
                'Jumlah produk harus berupa angka.',

            'products.*.quantity.min' =>
                'Jumlah produk minimal 1.',

            'products.*.price.required' =>
                'Harga produk wajib diisi.',

            'products.*.price.numeric' =>
                'Harga produk harus berupa angka.',

            'products.*.price.min' =>
                'Harga produk tidak boleh kurang dari 0.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(function () use ($request) {

                /*
                |--------------------------------------------------------------------------
                | GENERATE KODE PEMBELIAN
                |--------------------------------------------------------------------------
                */

                $lastPurchase = Purchase::lockForUpdate()
                    ->orderBy(
                        'id',
                        'desc'
                    )
                    ->first();


                if ($lastPurchase) {

                    $lastNumber = (int) str_replace(
                        'PB-',
                        '',
                        $lastPurchase->code
                    );

                    $nextNumber = $lastNumber + 1;

                } else {

                    $nextNumber = 1;

                }


                $code = 'PB-' . str_pad(
                    $nextNumber,
                    4,
                    '0',
                    STR_PAD_LEFT
                );


                /*
                |--------------------------------------------------------------------------
                | HITUNG TOTAL
                |--------------------------------------------------------------------------
                */

                $total = 0;


                foreach ($request->products as $item) {

                    $quantity = (int) $item['quantity'];

                    $price = (float) $item['price'];

                    $subtotal =
                        $price * $quantity;

                    $total += $subtotal;
                }


                /*
                |--------------------------------------------------------------------------
                | SIMPAN TRANSAKSI UTAMA
                |--------------------------------------------------------------------------
                */

                $purchase = Purchase::create([

                    'code' =>
                        $code,

                    'supplier_id' =>
                        $request->supplier_id,

                    'purchase_date' =>
                        $request->purchase_date,

                    'total' =>
                        $total,

                    'notes' =>
                        $request->notes,

                ]);


                /*
                |--------------------------------------------------------------------------
                | SIMPAN DETAIL DAN TAMBAH STOK
                |--------------------------------------------------------------------------
                */

                foreach ($request->products as $item) {

                    $quantity = (int) $item['quantity'];

                    $price = (float) $item['price'];

                    $subtotal =
                        $price * $quantity;


                    /*
                    |--------------------------------------------------------------------------
                    | LOCK PRODUK
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::lockForUpdate()
                        ->findOrFail(
                            $item['product_id']
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN DETAIL
                    |--------------------------------------------------------------------------
                    */

                    PurchaseDetail::create([

                        'purchase_id' =>
                            $purchase->id,

                        'product_id' =>
                            $product->id,

                        'quantity' =>
                            $quantity,

                        'price' =>
                            $price,

                        'subtotal' =>
                            $subtotal,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | TAMBAH STOK
                    |--------------------------------------------------------------------------
                    */

                    $product->increment(
                        'stock',
                        $quantity
                    );
                }

            });


            /*
            |--------------------------------------------------------------------------
            | BERHASIL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Pembelian berhasil disimpan dan stok produk telah diperbarui.'
                );


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | GAGAL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Pembelian gagal disimpan: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Menampilkan detail pembelian.
     */
    public function show(Purchase $purchase)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD RELATION
        |--------------------------------------------------------------------------
        */

        $purchase->load([
            'supplier',
            'details.product'
        ]);


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'purchases.show',
            compact('purchase')
        );
    }


    /**
     * Form edit pembelian.
     */
    public function edit(Purchase $purchase)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD DETAIL
        |--------------------------------------------------------------------------
        */

        $purchase->load([
            'supplier',
            'details.product'
        ]);


        /*
        |--------------------------------------------------------------------------
        | DATA SUPPLIER
        |--------------------------------------------------------------------------
        */

        $suppliers = Supplier::orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | DATA PRODUK
        |--------------------------------------------------------------------------
        */

        $products = Product::where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'purchases.edit',
            compact(
                'purchase',
                'suppliers',
                'products'
            )
        );
    }


    /**
     * Update pembelian.
     *
     * Stok pembelian lama dikembalikan terlebih dahulu,
     * kemudian detail baru disimpan dan stok baru ditambahkan.
     */
    public function update(
        Request $request,
        Purchase $purchase
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'supplier_id' => [
                'required',
                'exists:suppliers,id'
            ],

            'purchase_date' => [
                'required',
                'date'
            ],

            'products' => [
                'required',
                'array',
                'min:1'
            ],

            'products.*.product_id' => [
                'required',
                'exists:products,id'
            ],

            'products.*.quantity' => [
                'required',
                'integer',
                'min:1'
            ],

            'products.*.price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'notes' => [
                'nullable',
                'string'
            ],

        ], [

            'supplier_id.required' =>
                'Supplier wajib dipilih.',

            'supplier_id.exists' =>
                'Supplier tidak ditemukan.',

            'purchase_date.required' =>
                'Tanggal pembelian wajib diisi.',

            'purchase_date.date' =>
                'Format tanggal pembelian tidak valid.',

            'products.required' =>
                'Minimal satu produk harus ditambahkan.',

            'products.min' =>
                'Minimal satu produk harus ditambahkan.',

            'products.*.product_id.required' =>
                'Produk wajib dipilih.',

            'products.*.product_id.exists' =>
                'Produk tidak ditemukan.',

            'products.*.quantity.required' =>
                'Jumlah produk wajib diisi.',

            'products.*.quantity.integer' =>
                'Jumlah produk harus berupa angka.',

            'products.*.quantity.min' =>
                'Jumlah produk minimal 1.',

            'products.*.price.required' =>
                'Harga produk wajib diisi.',

            'products.*.price.numeric' =>
                'Harga produk harus berupa angka.',

            'products.*.price.min' =>
                'Harga produk tidak boleh kurang dari 0.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(function () use (
                $request,
                $purchase
            ) {

                /*
                |--------------------------------------------------------------------------
                | LOAD DETAIL LAMA
                |--------------------------------------------------------------------------
                */

                $purchase->load('details');


                /*
                |--------------------------------------------------------------------------
                | KEMBALIKAN STOK LAMA
                |--------------------------------------------------------------------------
                */

                foreach ($purchase->details as $detail) {

                    $product = Product::lockForUpdate()
                        ->find(
                            $detail->product_id
                        );


                    if ($product) {

                        $product->decrement(
                            'stock',
                            $detail->quantity
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | HAPUS DETAIL LAMA
                |--------------------------------------------------------------------------
                */

                $purchase->details()->delete();


                /*
                |--------------------------------------------------------------------------
                | HITUNG TOTAL BARU
                |--------------------------------------------------------------------------
                */

                $total = 0;


                /*
                |--------------------------------------------------------------------------
                | SIMPAN DETAIL BARU
                |--------------------------------------------------------------------------
                */

                foreach ($request->products as $item) {

                    $quantity =
                        (int) $item['quantity'];

                    $price =
                        (float) $item['price'];


                    /*
                    |--------------------------------------------------------------------------
                    | HITUNG SUBTOTAL
                    |--------------------------------------------------------------------------
                    */

                    $subtotal =
                        $price * $quantity;


                    $total += $subtotal;


                    /*
                    |--------------------------------------------------------------------------
                    | LOCK PRODUK
                    |--------------------------------------------------------------------------
                    */

                    $product = Product::lockForUpdate()
                        ->findOrFail(
                            $item['product_id']
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SIMPAN DETAIL
                    |--------------------------------------------------------------------------
                    */

                    PurchaseDetail::create([

                        'purchase_id' =>
                            $purchase->id,

                        'product_id' =>
                            $product->id,

                        'quantity' =>
                            $quantity,

                        'price' =>
                            $price,

                        'subtotal' =>
                            $subtotal,

                    ]);


                    /*
                    |--------------------------------------------------------------------------
                    | TAMBAH STOK BARU
                    |--------------------------------------------------------------------------
                    */

                    $product->increment(
                        'stock',
                        $quantity
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE TRANSAKSI UTAMA
                |--------------------------------------------------------------------------
                */

                $purchase->update([

                    'supplier_id' =>
                        $request->supplier_id,

                    'purchase_date' =>
                        $request->purchase_date,

                    'total' =>
                        $total,

                    'notes' =>
                        $request->notes,

                ]);

            });


            /*
            |--------------------------------------------------------------------------
            | BERHASIL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Pembelian berhasil diperbarui dan stok produk telah disesuaikan.'
                );


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | GAGAL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Pembelian gagal diperbarui: '
                    . $e->getMessage()
                );
        }
    }


    /**
     * Menghapus pembelian.
     *
     * Stok dikurangi kembali sesuai jumlah
     * produk yang terdapat pada pembelian.
     */
    public function destroy(Purchase $purchase)
    {
        /*
        |--------------------------------------------------------------------------
        | DATABASE TRANSACTION
        |--------------------------------------------------------------------------
        */

        try {

            DB::transaction(function () use ($purchase) {

                /*
                |--------------------------------------------------------------------------
                | LOAD DETAIL
                |--------------------------------------------------------------------------
                */

                $purchase->load('details');


                /*
                |--------------------------------------------------------------------------
                | KURANGI STOK
                |--------------------------------------------------------------------------
                */

                foreach ($purchase->details as $detail) {

                    $product = Product::lockForUpdate()
                        ->find(
                            $detail->product_id
                        );


                    if ($product) {

                        /*
                        |--------------------------------------------------------------------------
                        | CEK STOK
                        |--------------------------------------------------------------------------
                        */

                        if (
                            $product->stock
                            <
                            $detail->quantity
                        ) {

                            throw new \Exception(

                                'Stok produk '
                                . $product->name
                                . ' tidak mencukupi untuk membatalkan pembelian. '
                                . 'Stok saat ini: '
                                . $product->stock
                                . ', jumlah yang harus dikurangi: '
                                . $detail->quantity
                                . '.'

                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | KURANGI STOK
                        |--------------------------------------------------------------------------
                        */

                        $product->decrement(
                            'stock',
                            $detail->quantity
                        );
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | HAPUS PEMBELIAN
                |--------------------------------------------------------------------------
                |
                | Detail akan ikut terhapus jika
                | foreign key menggunakan cascade.
                |
                */

                $purchase->details()->delete();


                /*
                |--------------------------------------------------------------------------
                | HAPUS TRANSAKSI UTAMA
                |--------------------------------------------------------------------------
                */

                $purchase->delete();

            });


            /*
            |--------------------------------------------------------------------------
            | BERHASIL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('purchases.index')
                ->with(
                    'success',
                    'Pembelian berhasil dihapus dan stok produk telah dikurangi kembali.'
                );


        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | GAGAL
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('purchases.index')
                ->with(
                    'error',
                    'Pembelian gagal dihapus: '
                    . $e->getMessage()
                );
        }
    }
}