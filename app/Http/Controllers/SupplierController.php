<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Menampilkan daftar supplier.
     */
    public function index(Request $request)
    {
        $query = Supplier::withCount('products')
            ->latest();

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");

            });
        }

        $suppliers = $query
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }


    /**
     * Form tambah supplier.
     */
    public function create()
    {
        $lastSupplier = Supplier::orderBy('id', 'desc')->first();

        if ($lastSupplier) {

            $lastNumber = (int) str_replace(
                'SUP',
                '',
                $lastSupplier->code
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;

        }

        $code = 'SUP' . str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );

        return view('suppliers.create', compact('code'));
    }


    /**
     * Simpan supplier.
     */
    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|string|max:255',

            'phone' => 'nullable|string|max:30',

            'email' => 'nullable|email|max:255',

            'address' => 'nullable|string',

        ], [

            'name.required' => 'Nama supplier wajib diisi.',

            'email.email' => 'Format email tidak valid.',

        ]);


        /*
        |--------------------------------------------------------------------------
        | Membuat kode supplier otomatis
        |--------------------------------------------------------------------------
        */

        $lastSupplier = Supplier::orderBy('id', 'desc')->first();

        if ($lastSupplier) {

            $lastNumber = (int) str_replace(
                'SUP',
                '',
                $lastSupplier->code
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;

        }


        $code = 'SUP' . str_pad(
            $nextNumber,
            3,
            '0',
            STR_PAD_LEFT
        );


        /*
        |--------------------------------------------------------------------------
        | Simpan supplier
        |--------------------------------------------------------------------------
        */

        Supplier::create([

            'code' => $code,

            'name' => $request->name,

            'phone' => $request->phone,

            'email' => $request->email,

            'address' => $request->address,

        ]);


        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier berhasil ditambahkan dengan kode ' . $code . '.'
            );
    }


    /**
     * Detail supplier.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load('products');

        return view(
            'suppliers.show',
            compact('supplier')
        );
    }


    /**
     * Form edit supplier.
     */
    public function edit(Supplier $supplier)
    {
        return view(
            'suppliers.edit',
            compact('supplier')
        );
    }


    /**
     * Update supplier.
     */
    public function update(
        Request $request,
        Supplier $supplier
    ) {
        $request->validate([

            'code' => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,

            'name' => 'required|string|max:255',

            'phone' => 'nullable|string|max:30',

            'email' => 'nullable|email|max:255',

            'address' => 'nullable|string',

        ], [

            'code.required' => 'Kode supplier wajib diisi.',

            'code.unique' => 'Kode supplier sudah digunakan.',

            'name.required' => 'Nama supplier wajib diisi.',

            'email.email' => 'Format email tidak valid.',

        ]);


        $supplier->update([

            'code' => strtoupper($request->code),

            'name' => $request->name,

            'phone' => $request->phone,

            'email' => $request->email,

            'address' => $request->address,

        ]);


        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier berhasil diperbarui.'
            );
    }


    /**
     * Hapus supplier.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->products()->exists()) {

            return redirect()
                ->route('suppliers.index')
                ->with(
                    'error',
                    'Supplier tidak dapat dihapus karena masih digunakan oleh produk.'
                );
        }


        $supplier->delete();


        return redirect()
            ->route('suppliers.index')
            ->with(
                'success',
                'Supplier berhasil dihapus.'
            );
    }
}