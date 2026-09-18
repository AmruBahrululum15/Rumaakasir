@extends('layouts.app')

@section('title', 'Tambah Pembelian')

@section('content_header')

<div>
    <h2 class="fw-bold mb-1">
        Tambah Pembelian
    </h2>

    <p class="text-muted mb-0">
        Tambahkan transaksi pembelian barang
    </p>
</div>

@stop


@section('content')

<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">

    @csrf

    <div class="card">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="fas fa-shopping-cart mr-2"></i>
                Form Pembelian
            </h5>

        </div>


        <div class="card-body">

            {{-- ERROR VALIDASI --}}

            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        Terdapat kesalahan:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- INFORMASI PEMBELIAN --}}

            <div class="row">

                {{-- KODE --}}

                <div class="col-md-4">

                    <div class="form-group">

                        <label>
                            Kode Pembelian
                        </label>

                        <input type="text"
                               class="form-control"
                               value="{{ $code }}"
                               readonly>

                        <small class="text-muted">

                            Kode dibuat otomatis oleh sistem.

                        </small>

                    </div>

                </div>


                {{-- SUPPLIER --}}

                <div class="col-md-4">

                    <div class="form-group">

                        <label>
                            Supplier
                            <span class="text-danger">*</span>
                        </label>

                        <select name="supplier_id"
                                class="form-control @error('supplier_id') is-invalid @enderror"
                                required>

                            <option value="">
                                -- Pilih Supplier --
                            </option>

                            @foreach($suppliers as $supplier)

                                <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>

                                    {{ $supplier->code }}
                                    -
                                    {{ $supplier->name }}

                                </option>

                            @endforeach

                        </select>

                        @error('supplier_id')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- TANGGAL --}}

                <div class="col-md-4">

                    <div class="form-group">

                        <label>
                            Tanggal Pembelian
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                               name="purchase_date"
                               class="form-control @error('purchase_date') is-invalid @enderror"
                               value="{{ old('purchase_date', date('Y-m-d')) }}"
                               required>

                        @error('purchase_date')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>

            </div>


            <hr>


            {{-- DETAIL PRODUK --}}

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h5 class="mb-0">

                    <i class="fas fa-box mr-2"></i>

                    Detail Produk

                </h5>

                <button type="button"
                        class="btn btn-success btn-sm"
                        id="addProduct">

                    <i class="fas fa-plus mr-1"></i>

                    Tambah Produk

                </button>

            </div>


            <div class="table-responsive">

                <table class="table table-bordered"
                       id="productTable">

                    <thead class="thead-light">

                        <tr>

                            <th width="30%">
                                Produk
                            </th>

                            <th width="18%">
                                Harga Satuan
                            </th>

                            <th width="15%">
                                Jumlah
                            </th>

                            <th width="20%">
                                Subtotal
                            </th>

                            <th width="10%">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody id="productRows">

                        {{-- BARIS PRODUK AKAN DIBUAT JAVASCRIPT --}}

                    </tbody>


                    <tfoot>

                        <tr>

                            <td colspan="3"
                                class="text-right">

                                <strong>
                                    TOTAL PEMBELIAN
                                </strong>

                            </td>

                            <td>

                                <strong id="grandTotal">
                                    Rp 0
                                </strong>

                            </td>

                            <td></td>

                        </tr>

                    </tfoot>

                </table>

            </div>


            {{-- CATATAN --}}

            <div class="form-group mt-4">

                <label>
                    Catatan
                </label>

                <textarea name="notes"
                          rows="3"
                          class="form-control"
                          placeholder="Catatan pembelian (opsional)">{{ old('notes') }}</textarea>

            </div>

        </div>


        {{-- FOOTER --}}

        <div class="card-footer">

            <a href="{{ route('purchases.index') }}"
               class="btn btn-secondary">

                <i class="fas fa-arrow-left mr-1"></i>

                Kembali

            </a>


            <button type="submit"
                    class="btn btn-primary float-right">

                <i class="fas fa-save mr-1"></i>

                Simpan Pembelian

            </button>

        </div>

    </div>

</form>

@stop


@section('js')

<script>

document.addEventListener('DOMContentLoaded', function () {

    let rowIndex = 0;

    const productRows =
        document.getElementById('productRows');

    const addProductButton =
        document.getElementById('addProduct');

    const grandTotal =
        document.getElementById('grandTotal');


    /*
    |--------------------------------------------------------------------------
    | DATA PRODUK DARI LARAVEL
    |--------------------------------------------------------------------------
    */

    const products = @json($products);


    /*
    |--------------------------------------------------------------------------
    | FORMAT RUPIAH
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value)
    {
        value = Number(value) || 0;

        return 'Rp ' + value.toLocaleString(
            'id-ID'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | HITUNG TOTAL
    |--------------------------------------------------------------------------
    */

    function calculateTotal()
    {
        let total = 0;

        document
            .querySelectorAll('.product-row')
            .forEach(function(row)
            {
                const price =
                    Number(
                        row.querySelector('.price-input').value
                    ) || 0;

                const quantity =
                    Number(
                        row.querySelector('.quantity-input').value
                    ) || 0;

                const subtotal =
                    price * quantity;

                row.querySelector('.subtotal')
                    .innerText =
                    formatRupiah(subtotal);

                total += subtotal;
            });

        grandTotal.innerText =
            formatRupiah(total);
    }


    /*
    |--------------------------------------------------------------------------
    | TAMBAH BARIS PRODUK
    |--------------------------------------------------------------------------
    */

    function addProductRow()
    {
        const index = rowIndex++;

        let productOptions =
            '<option value="">-- Pilih Produk --</option>';

        products.forEach(function(product)
        {
            productOptions += `
                <option value="${product.id}"
                        data-price="${product.price}">
                    ${product.name}
                </option>
            `;
        });


        const row = document.createElement('tr');

        row.classList.add('product-row');


        row.innerHTML = `

            <td>

                <select
                    name="products[${index}][product_id]"
                    class="form-control product-select"
                    required>

                    ${productOptions}

                </select>

            </td>


            <td>

                <input
                    type="number"
                    name="products[${index}][price]"
                    class="form-control price-input"
                    min="0"
                    step="0.01"
                    value="0"
                    required>

            </td>


            <td>

                <input
                    type="number"
                    name="products[${index}][quantity]"
                    class="form-control quantity-input"
                    min="1"
                    value="1"
                    required>

            </td>


            <td>

                <strong class="subtotal">
                    Rp 0
                </strong>

            </td>


            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-danger btn-sm remove-product">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        `;


        productRows.appendChild(row);


        /*
        |--------------------------------------------------------------------------
        | PILIH PRODUK
        |--------------------------------------------------------------------------
        */

        const productSelect =
            row.querySelector('.product-select');

        const priceInput =
            row.querySelector('.price-input');

        const quantityInput =
            row.querySelector('.quantity-input');


        productSelect.addEventListener(
            'change',
            function()
            {
                const selectedOption =
                    this.options[this.selectedIndex];

                const price =
                    selectedOption.dataset.price || 0;

                priceInput.value = price;

                calculateTotal();
            }
        );


        /*
        |--------------------------------------------------------------------------
        | UBAH HARGA
        |--------------------------------------------------------------------------
        */

        priceInput.addEventListener(
            'input',
            calculateTotal
        );


        /*
        |--------------------------------------------------------------------------
        | UBAH JUMLAH
        |--------------------------------------------------------------------------
        */

        quantityInput.addEventListener(
            'input',
            calculateTotal
        );


        /*
        |--------------------------------------------------------------------------
        | HAPUS PRODUK
        |--------------------------------------------------------------------------
        */

        row.querySelector('.remove-product')
            .addEventListener(
                'click',
                function()
                {
                    row.remove();

                    calculateTotal();
                }
            );


        calculateTotal();
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON TAMBAH PRODUK
    |--------------------------------------------------------------------------
    */

    addProductButton.addEventListener(
        'click',
        addProductRow
    );


    /*
    |--------------------------------------------------------------------------
    | TAMBAHKAN BARIS PERTAMA
    |--------------------------------------------------------------------------
    */

    addProductRow();

});

</script>

@stop