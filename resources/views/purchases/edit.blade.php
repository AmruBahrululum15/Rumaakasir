@extends('layouts.app')

@section('title', 'Edit Pembelian')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>
        <h2 class="fw-bold mb-1">
            Edit Pembelian
        </h2>

        <p class="text-muted mb-0">
            Ubah data transaksi pembelian
        </p>
    </div>

    <a
        href="{{ route('purchases.show', $purchase->id) }}"
        class="btn btn-info"
    >
        <i class="fas fa-eye mr-1"></i>
        Detail
    </a>

</div>

@stop


@section('content')

<div class="card">

    {{-- HEADER --}}
    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-edit mr-2"></i>
            Form Edit Pembelian
        </h5>

    </div>


    {{-- FORM --}}
    <form
        action="{{ route('purchases.update', $purchase->id) }}"
        method="POST"
        id="purchaseForm"
    >

        @csrf

        @method('PUT')


        <div class="card-body">

            {{-- ERROR SESSION --}}
            @if(session('error'))

                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    {{ session('error') }}

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >
                        <span>&times;</span>
                    </button>

                </div>

            @endif


            {{-- VALIDATION ERROR --}}
            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Terdapat kesalahan:
                    </strong>

                    <ul class="mb-0 mt-2">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- INFORMASI PEMBELIAN --}}
            <div class="row">

                {{-- KODE --}}
                <div class="col-md-6">

                    <div class="form-group">

                        <label>
                            Kode Pembelian
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $purchase->code }}"
                            readonly
                        >

                        <small class="text-muted">
                            Kode pembelian tidak dapat diubah.
                        </small>

                    </div>

                </div>


                {{-- SUPPLIER --}}
                <div class="col-md-6">

                    <div class="form-group">

                        <label for="supplier_id">

                            Supplier

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <select
                            name="supplier_id"
                            id="supplier_id"
                            class="form-control @error('supplier_id') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                -- Pilih Supplier --
                            </option>

                            @foreach($suppliers as $supplier)

                                <option
                                    value="{{ $supplier->id }}"
                                    {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}
                                >
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

            </div>


            {{-- TANGGAL --}}
            <div class="row">

                <div class="col-md-6">

                    <div class="form-group">

                        <label for="purchase_date">

                            Tanggal Pembelian

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <input
                            type="date"
                            name="purchase_date"
                            id="purchase_date"
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            value="{{ old('purchase_date', \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d')) }}"
                            required
                        >

                        @error('purchase_date')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>

            </div>


            <hr>


            {{-- PRODUK --}}
            <div class="d-flex justify-content-between align-items-center mb-3">

                <div>

                    <h5 class="mb-1">

                        <i class="fas fa-box mr-2"></i>

                        Detail Produk

                    </h5>

                    <small class="text-muted">
                        Ubah produk, jumlah, atau harga pembelian.
                    </small>

                </div>


                <button
                    type="button"
                    class="btn btn-success btn-sm"
                    id="btnTambahProduk"
                >

                    <i class="fas fa-plus mr-1"></i>

                    Tambah Produk

                </button>

            </div>


            {{-- TABLE PRODUK --}}
            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover"
                    id="tableProduk"
                >

                    <thead class="thead-light">

                        <tr>

                            <th width="35%">
                                Produk
                            </th>

                            <th width="20%">
                                Harga Satuan
                            </th>

                            <th width="15%">
                                Jumlah
                            </th>

                            <th width="20%">
                                Subtotal
                            </th>

                            <th width="10%" class="text-center">
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody id="produkContainer">

                        {{-- DATA PRODUK DARI DATABASE AKAN DIMASUKKAN DI SINI --}}

                    </tbody>

                </table>

            </div>


            {{-- JIKA TIDAK ADA PRODUK --}}
            <div
                id="emptyProductMessage"
                class="alert alert-warning text-center"
                style="display:none;"
            >

                <i class="fas fa-box-open mr-1"></i>

                Belum ada produk dalam pembelian.

            </div>


            {{-- TOTAL --}}
            <div class="row justify-content-end mt-4">

                <div class="col-md-5">

                    <div class="card border-primary">

                        <div class="card-header bg-primary text-white">

                            <h5 class="mb-0">

                                <i class="fas fa-calculator mr-2"></i>

                                Ringkasan Pembelian

                            </h5>

                        </div>


                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <span class="font-weight-bold">
                                    Total Pembelian
                                </span>

                                <strong
                                    class="text-primary"
                                    style="font-size:22px;"
                                    id="grandTotal"
                                >
                                    Rp 0
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- CATATAN --}}
            <div class="form-group mt-3">

                <label for="notes">

                    Catatan

                </label>

                <textarea
                    name="notes"
                    id="notes"
                    rows="4"
                    class="form-control"
                    placeholder="Catatan pembelian..."
                >{{ old('notes', $purchase->notes) }}</textarea>

            </div>

        </div>


        {{-- FOOTER --}}
        <div class="card-footer d-flex justify-content-between">

            <a
                href="{{ route('purchases.index') }}"
                class="btn btn-secondary"
            >

                <i class="fas fa-arrow-left mr-1"></i>

                Kembali

            </a>


            <button
                type="submit"
                class="btn btn-primary"
                id="btnUpdate"
            >

                <i class="fas fa-save mr-1"></i>

                Simpan Perubahan

            </button>

        </div>

    </form>

</div>

@stop


@section('js')

<script>

let productIndex = 0;


/*
|--------------------------------------------------------------------------
| DATA PRODUK
|--------------------------------------------------------------------------
*/

const products = @json($products);


/*
|--------------------------------------------------------------------------
| DATA DETAIL PEMBELIAN LAMA
|--------------------------------------------------------------------------
*/

const purchaseDetails = @json($purchase->details);


/*
|--------------------------------------------------------------------------
| FORMAT RUPIAH
|--------------------------------------------------------------------------
*/

function formatRupiah(angka)
{

    angka = parseFloat(angka) || 0;

    return new Intl.NumberFormat('id-ID', {

        style: 'currency',

        currency: 'IDR',

        maximumFractionDigits: 0

    }).format(angka);

}


/*
|--------------------------------------------------------------------------
| BUAT OPTION PRODUK
|--------------------------------------------------------------------------
*/

function generateProductOptions(selectedId = '')
{

    let options =
        '<option value="">-- Pilih Produk --</option>';


    products.forEach(function(product) {

        let selected =
            String(product.id) === String(selectedId)
                ? 'selected'
                : '';


        options += `

            <option
                value="${product.id}"
                data-price="${product.price}"
                data-stock="${product.stock}"
                ${selected}
            >

                ${product.name}

                (Stok saat ini: ${product.stock})

            </option>

        `;

    });


    return options;

}


/*
|--------------------------------------------------------------------------
| TAMBAH BARIS PRODUK
|--------------------------------------------------------------------------
*/

function tambahProduk(
    selectedProductId = '',
    quantity = 1,
    price = 0
)
{

    let rowIndex = productIndex;


    let row = `

        <tr class="product-row">

            {{-- PRODUK --}}
            <td>

                <select
                    name="products[${rowIndex}][product_id]"
                    class="form-control product-select"
                    required
                >

                    ${generateProductOptions(selectedProductId)}

                </select>

            </td>


            {{-- HARGA --}}
            <td>

                <input
                    type="number"
                    name="products[${rowIndex}][price]"
                    class="form-control price-input"
                    value="${price}"
                    min="0"
                    step="1"
                    required
                >

                <small class="text-muted price-display">
                    ${formatRupiah(price)}
                </small>

            </td>


            {{-- JUMLAH --}}
            <td>

                <input
                    type="number"
                    name="products[${rowIndex}][quantity]"
                    class="form-control quantity-input"
                    value="${quantity}"
                    min="1"
                    required
                >

                <small class="text-muted stock-info">
                    Stok: -
                </small>

            </td>


            {{-- SUBTOTAL --}}
            <td>

                <input
                    type="text"
                    class="form-control subtotal-display"
                    value="${formatRupiah(price * quantity)}"
                    readonly
                >

            </td>


            {{-- AKSI --}}
            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-danger btn-sm btnHapusProduk"
                    title="Hapus Produk"
                >

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        </tr>

    `;


    $('#produkContainer').append(row);


    let newRow =
        $('#produkContainer .product-row').last();


    updateProductRow(newRow);


    productIndex++;


    hitungTotal();

}


/*
|--------------------------------------------------------------------------
| UPDATE BARIS PRODUK
|--------------------------------------------------------------------------
*/

function updateProductRow(row)
{

    let select =
        row.find('.product-select');


    let selected =
        select.find(':selected');


    let price =
        parseFloat(
            row.find('.price-input').val()
        ) || 0;


    let stock =
        parseInt(
            selected.data('stock')
        ) || 0;


    /*
    |--------------------------------------------------------------------------
    | STOK
    |--------------------------------------------------------------------------
    */

    if (selected.val()) {

        row.find('.stock-info')
            .text(
                'Stok saat ini: ' + stock
            );

        row.find('.quantity-input')
            .attr(
                'max',
                stock
            );

    }
    else {

        row.find('.stock-info')
            .text('Stok: -');

        row.find('.quantity-input')
            .removeAttr('max');

    }


    /*
    |--------------------------------------------------------------------------
    | HARGA
    |--------------------------------------------------------------------------
    */

    row.find('.price-display')
        .text(
            formatRupiah(price)
        );


    /*
    |--------------------------------------------------------------------------
    | SUBTOTAL
    |--------------------------------------------------------------------------
    */

    hitungSubtotal(row);

}


/*
|--------------------------------------------------------------------------
| PILIH PRODUK
|--------------------------------------------------------------------------
*/

$(document).on(
    'change',
    '.product-select',
    function()
    {

        let row =
            $(this).closest('.product-row');


        let selected =
            $(this).find(':selected');


        let price =
            parseFloat(
                selected.data('price')
            ) || 0;


        let stock =
            parseInt(
                selected.data('stock')
            ) || 0;


        /*
        |--------------------------------------------------------------------------
        | Jika produk baru dipilih
        |--------------------------------------------------------------------------
        */

        row.find('.price-input')
            .val(price);


        row.find('.quantity-input')
            .val(1);


        row.find('.quantity-input')
            .attr(
                'max',
                stock
            );


        row.find('.stock-info')
            .text(
                'Stok saat ini: ' + stock
            );


        row.find('.price-display')
            .text(
                formatRupiah(price)
            );


        hitungSubtotal(row);

    }
);


/*
|--------------------------------------------------------------------------
| HARGA BERUBAH
|--------------------------------------------------------------------------
*/

$(document).on(
    'input',
    '.price-input',
    function()
    {

        let row =
            $(this).closest('.product-row');


        let price =
            parseFloat($(this).val())
            || 0;


        row.find('.price-display')
            .text(
                formatRupiah(price)
            );


        hitungSubtotal(row);

    }
);


/*
|--------------------------------------------------------------------------
| JUMLAH BERUBAH
|--------------------------------------------------------------------------
*/

$(document).on(
    'input',
    '.quantity-input',
    function()
    {

        let row =
            $(this).closest('.product-row');


        let quantity =
            parseInt($(this).val())
            || 0;


        let stock =
            parseInt(
                $(this).attr('max')
            )
            || 0;


        if (quantity < 1) {

            $(this).val(1);

            quantity = 1;

        }


        /*
        |--------------------------------------------------------------------------
        | Batasi stok
        |--------------------------------------------------------------------------
        */

        if (
            stock > 0 &&
            quantity > stock
        ) {

            $(this).val(stock);

            quantity = stock;

        }


        hitungSubtotal(row);

    }
);


/*
|--------------------------------------------------------------------------
| HITUNG SUBTOTAL
|--------------------------------------------------------------------------
*/

function hitungSubtotal(row)
{

    let price =
        parseFloat(
            row.find('.price-input').val()
        )
        || 0;


    let quantity =
        parseInt(
            row.find('.quantity-input').val()
        )
        || 0;


    let subtotal =
        price * quantity;


    row.find('.subtotal-display')
        .val(
            formatRupiah(subtotal)
        );


    hitungTotal();

}


/*
|--------------------------------------------------------------------------
| HITUNG TOTAL
|--------------------------------------------------------------------------
*/

function hitungTotal()
{

    let total = 0;


    $('.product-row').each(function() {

        let price =
            parseFloat(
                $(this)
                    .find('.price-input')
                    .val()
            )
            || 0;


        let quantity =
            parseInt(
                $(this)
                    .find('.quantity-input')
                    .val()
            )
            || 0;


        total +=
            price * quantity;

    });


    $('#grandTotal')
        .text(
            formatRupiah(total)
        );

}


/*
|--------------------------------------------------------------------------
| HAPUS PRODUK
|--------------------------------------------------------------------------
*/

$(document).on(
    'click',
    '.btnHapusProduk',
    function()
    {

        $(this)
            .closest('.product-row')
            .remove();


        updateEmptyMessage();


        hitungTotal();

    }
);


/*
|--------------------------------------------------------------------------
| EMPTY MESSAGE
|--------------------------------------------------------------------------
*/

function updateEmptyMessage()
{

    if (
        $('.product-row').length === 0
    ) {

        $('#emptyProductMessage')
            .show();

    }
    else {

        $('#emptyProductMessage')
            .hide();

    }

}


/*
|--------------------------------------------------------------------------
| TAMBAH PRODUK
|--------------------------------------------------------------------------
*/

$('#btnTambahProduk').on(
    'click',
    function()
    {

        tambahProduk();

        updateEmptyMessage();

    }
);


/*
|--------------------------------------------------------------------------
| LOAD DATA LAMA
|--------------------------------------------------------------------------
*/

$(document).ready(function() {


    /*
    |--------------------------------------------------------------------------
    | Jika ada old input
    |--------------------------------------------------------------------------
    */

    @if(old('products'))

        const oldProducts =
            @json(old('products'));


        oldProducts.forEach(function(item) {

            tambahProduk(

                item.product_id || '',

                item.quantity || 1,

                item.price || 0

            );

        });


    @else


        /*
        |--------------------------------------------------------------------------
        | Load detail dari database
        |--------------------------------------------------------------------------
        */

        if (
            purchaseDetails &&
            purchaseDetails.length > 0
        ) {

            purchaseDetails.forEach(function(detail) {

                tambahProduk(

                    detail.product_id,

                    detail.quantity,

                    detail.price

                );

            });

        }
        else {

            tambahProduk();

        }

    @endif


    updateEmptyMessage();


    hitungTotal();

});


/*
|--------------------------------------------------------------------------
| CEGAH SUBMIT TANPA PRODUK
|--------------------------------------------------------------------------
*/

$('#purchaseForm').on(
    'submit',
    function(event)
    {

        if (
            $('.product-row').length === 0
        ) {

            event.preventDefault();


            alert(
                'Minimal harus ada satu produk.'
            );


            return false;

        }


        let valid = true;


        $('.product-row').each(function() {

            let product =
                $(this)
                    .find('.product-select')
                    .val();


            let quantity =
                parseInt(
                    $(this)
                        .find('.quantity-input')
                        .val()
                )
                || 0;


            let price =
                parseFloat(
                    $(this)
                        .find('.price-input')
                        .val()
                )
                || 0;


            if (
                !product ||
                quantity < 1 ||
                price < 0
            ) {

                valid = false;

            }

        });


        if (!valid) {

            event.preventDefault();


            alert(
                'Mohon periksa kembali data produk.'
            );


            return false;

        }


        return true;

    }
);

</script>

@stop
