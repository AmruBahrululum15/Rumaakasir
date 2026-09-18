<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\SaleDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk.
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $search = $request->get('search');


        /*
        |--------------------------------------------------------------------------
        | QUERY PRODUK
        |--------------------------------------------------------------------------
        */

        $products = Product::with(['category', 'supplier'])
            ->when($search, function ($query) use ($search) {

                $query->where(function ($q) use ($search) {

                    $q->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhereHas(
                        'category',
                        function ($categoryQuery) use ($search) {

                            $categoryQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );

                        }
                    );

                    $q->orWhereHas(
                        'supplier',
                        function ($supplierQuery) use ($search) {
                            $supplierQuery->where(
                                'name',
                                'like',
                                '%' . $search . '%'
                            );
                        }
                    );

                });

            })
            ->latest()
            ->paginate(12)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.index',
            compact(
                'products',
                'search'
            )
        );
    }


    /**
     * Menampilkan form tambah produk.
     */
    public function create()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL KATEGORI
        |--------------------------------------------------------------------------
        */

        $categories = Category::orderBy('name')
            ->get();

        $suppliers = Supplier::orderBy('name')->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.create',
            compact('categories', 'suppliers')
        );
    }


    /**
     * Menyimpan produk baru.
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id'
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'stock' => [
                'required',
                'integer',
                'min:0'
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],

        ], [

            'name.required' =>
                'Nama produk wajib diisi.',

            'name.max' =>
                'Nama produk maksimal 255 karakter.',

            'category_id.required' =>
                'Kategori produk wajib dipilih.',

            'category_id.exists' =>
                'Kategori produk tidak ditemukan.',

            'supplier_id.exists' =>
                'Supplier produk tidak ditemukan.',

            'price.required' =>
                'Harga produk wajib diisi.',

            'price.numeric' =>
                'Harga produk harus berupa angka.',

            'price.min' =>
                'Harga produk tidak boleh kurang dari 0.',

            'stock.required' =>
                'Stok produk wajib diisi.',

            'stock.integer' =>
                'Stok harus berupa angka bulat.',

            'stock.min' =>
                'Stok tidak boleh kurang dari 0.',

            'status.required' =>
                'Status produk wajib dipilih.',

            'status.in' =>
                'Status produk tidak valid.',

            'image.required' =>
                'Gambar produk wajib diupload.',

            'image.image' =>
                'File yang diupload harus berupa gambar.',

            'image.mimes' =>
                'Format gambar harus JPG, JPEG, PNG, atau WEBP.',

            'image.max' =>
                'Ukuran gambar maksimal 2 MB.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | UPLOAD GAMBAR
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $validated['image'] =
                $request
                    ->file('image')
                    ->store(
                        'products',
                        'public'
                    );
        }

        $validated['is_active'] = $validated['status'] === 'active';


        /*
        |--------------------------------------------------------------------------
        | SIMPAN PRODUK
        |--------------------------------------------------------------------------
        */

        Product::create($validated);


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil ditambahkan.'
            );
    }


    /**
     * Menampilkan detail produk.
     */
    public function show(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | LOAD RELASI
        |--------------------------------------------------------------------------
        */

        $product->load(['category', 'supplier']);


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.show',
            compact('product')
        );
    }


    /**
     * Menampilkan form edit produk.
     */
    public function edit(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL KATEGORI
        |--------------------------------------------------------------------------
        */

        $categories = Category::orderBy('name')
            ->get();

        $suppliers = Supplier::orderBy('name')->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'products.edit',
            compact(
                'product',
                'categories',
                'suppliers'
            )
        );
    }


    /**
     * Update produk.
     */
    public function update(
        Request $request,
        Product $product
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'category_id' => [
                'required',
                'integer',
                'exists:categories,id'
            ],

            'supplier_id' => [
                'nullable',
                'integer',
                'exists:suppliers,id',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0'
            ],

            'stock' => [
                'required',
                'integer',
                'min:0'
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048'
            ],

        ], [

            'name.required' =>
                'Nama produk wajib diisi.',

            'name.max' =>
                'Nama produk maksimal 255 karakter.',

            'category_id.required' =>
                'Kategori produk wajib dipilih.',

            'category_id.exists' =>
                'Kategori produk tidak ditemukan.',

            'supplier_id.exists' =>
                'Supplier produk tidak ditemukan.',

            'price.required' =>
                'Harga produk wajib diisi.',

            'price.numeric' =>
                'Harga produk harus berupa angka.',

            'price.min' =>
                'Harga produk tidak boleh kurang dari 0.',

            'stock.required' =>
                'Stok produk wajib diisi.',

            'stock.integer' =>
                'Stok harus berupa angka bulat.',

            'stock.min' =>
                'Stok tidak boleh kurang dari 0.',

            'status.required' =>
                'Status produk wajib dipilih.',

            'status.in' =>
                'Status produk tidak valid.',

            'image.image' =>
                'File yang diupload harus berupa gambar.',

            'image.mimes' =>
                'Format gambar harus JPG, JPEG, PNG, atau WEBP.',

            'image.max' =>
                'Ukuran gambar maksimal 2 MB.',

        ]);

        $validated['is_active'] = $validated['status'] === 'active';


        /*
        |--------------------------------------------------------------------------
        | UPLOAD GAMBAR BARU
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            /*
            |--------------------------------------------------------------------------
            | SIMPAN GAMBAR BARU TERLEBIH DAHULU
            |--------------------------------------------------------------------------
            */

            $newImage =
                $request
                    ->file('image')
                    ->store(
                        'products',
                        'public'
                    );


            /*
            |--------------------------------------------------------------------------
            | SIMPAN PATH GAMBAR LAMA
            |--------------------------------------------------------------------------
            */

            $oldImage = $product->image;


            /*
            |--------------------------------------------------------------------------
            | MASUKKAN GAMBAR BARU
            |--------------------------------------------------------------------------
            */

            $validated['image'] = $newImage;


            /*
            |--------------------------------------------------------------------------
            | UPDATE PRODUK
            |--------------------------------------------------------------------------
            */

            $product->update($validated);


            /*
            |--------------------------------------------------------------------------
            | HAPUS GAMBAR LAMA
            |--------------------------------------------------------------------------
            */

            if (
                $oldImage &&
                Storage::disk('public')->exists(
                    $oldImage
                )
            ) {

                Storage::disk('public')->delete(
                    $oldImage
                );
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | UPDATE TANPA GAMBAR
            |--------------------------------------------------------------------------
            */

            $product->update($validated);

        }


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil diperbarui.'
            );
    }


    /**
     * Menghapus produk.
     */
    public function destroy(Product $product)
    {
        /*
        |--------------------------------------------------------------------------
        | CEK APAKAH PRODUK SUDAH DIGUNAKAN DALAM TRANSAKSI
        |--------------------------------------------------------------------------
        */

        $usedInSales = SaleDetail::where(
            'product_id',
            $product->id
        )->exists();


        /*
        |--------------------------------------------------------------------------
        | JIKA PRODUK SUDAH PERNAH TERJUAL
        |--------------------------------------------------------------------------
        */

        if ($usedInSales) {

            return redirect()
                ->route('products.index')
                ->with(
                    'error',
                    'Produk "' . $product->name . '" tidak dapat dihapus karena sudah digunakan dalam transaksi penjualan.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN PATH GAMBAR
        |--------------------------------------------------------------------------
        */

        $image = $product->image;


        /*
        |--------------------------------------------------------------------------
        | HAPUS PRODUK
        |--------------------------------------------------------------------------
        */

        $product->delete();


        /*
        |--------------------------------------------------------------------------
        | HAPUS GAMBAR SETELAH PRODUK BERHASIL DIHAPUS
        |--------------------------------------------------------------------------
        */

        if (
            $image &&
            Storage::disk('public')->exists($image)
        ) {

            Storage::disk('public')->delete($image);
        }


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('products.index')
            ->with(
                'success',
                'Produk berhasil dihapus.'
            );
    }

    /**
     * Mengaktifkan atau menonaktifkan produk tanpa mengubah data lainnya.
     */
    public function toggleStatus(Product $product)
    {
        $isActive = ! $product->is_active;

        $product->update([
            'is_active' => $isActive,
            'status' => $isActive ? 'active' : 'inactive',
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Status produk berhasil diperbarui.');
    }
}
