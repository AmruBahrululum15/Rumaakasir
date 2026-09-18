@extends('layouts.app')

@section('title', 'Edit Penjualan')


@section('content_header')

<div class="d-flex justify-content-between align-items-center flex-wrap">

    <div>

        <h2 class="font-weight-bold mb-1">
            <i class="fas fa-edit text-primary mr-2"></i>
            Edit Penjualan
        </h2>

        <p class="text-muted mb-0">
            Perbarui detail transaksi penjualan
            <span class="mx-1">•</span>
            <strong>{{ $sale->code }}</strong>
        </p>

    </div>


    <div class="mt-2 mt-md-0">

        <a
            href="{{ route('sales.index') }}"
            class="btn btn-outline-secondary"
        >

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

    </div>

</div>

@stop



@section('content')


{{-- ========================================================= --}}
{{-- CUSTOM STYLE --}}
{{-- ========================================================= --}}

<style>

    .sale-card {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
    }


    .sale-card-header {
        background: linear-gradient(
            135deg,
            #007bff,
            #0056b3
        );

        color: white;

        padding: 18px 22px;
    }


    .info-card {
        border: 1px solid #e9ecef;
        border-radius: 12px;
        background: #fff;
        height: 100%;
    }


    .info-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 600;
        margin-bottom: 4px;
    }


    .info-value {
        font-weight: 700;
        font-size: 16px;
    }


    .product-section {
        border: 1px solid #e9ecef;
        border-radius: 14px;
        overflow: hidden;
    }


    .product-section-header {
        background: #f8f9fa;
        padding: 16px 18px;
        border-bottom: 1px solid #e9ecef;
    }


    .product-row {
        transition: all .2s ease;
    }


    .product-row:hover {
        background: #fbfdff;
    }


    .product-image-wrapper {
        width: 58px;
        height: 58px;
        min-width: 58px;
        border-radius: 10px;
        overflow: hidden;
        background: #f1f3f5;
        border: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
    }


    .product-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }


    .product-name {
        font-weight: 700;
        color: #343a40;
        line-height: 1.2;
    }


    .product-meta {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }


    .stock-badge {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 20px;
    }


    .price-display,
    .subtotal-display {
        font-weight: 600;
        background-color: #f8f9fa !important;
    }


    .subtotal-display {
        color: #007bff;
    }


    .quantity-input {
        text-align: center;
        font-weight: 600;
    }


    .payment-card {
        border: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,.08);
    }


    .payment-header {
        background: linear-gradient(
            135deg,
            #007bff,
            #0056b3
        );

        color: white;
        padding: 16px 20px;
    }


    .total-display {
        font-size: 24px;
        font-weight: 800;
        color: #007bff;
    }


    .change-display {
        font-size: 22px;
        font-weight: 800;
    }


    .btn-add-product {
        border-radius: 8px;
        font-weight: 600;
    }


    .btn-delete-product {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: 8px;
    }


    .empty-product {
        padding: 40px 20px;
        text-align: center;
        color: #6c757d;
    }


    .empty-product i {
        font-size: 42px;
        margin-bottom: 12px;
    }


    .form-control {
        border-radius: 8px;
    }


    .input-group-text {
        border-radius: 8px 0 0 8px;
    }


    @media (max-width: 768px) {

        .product-image-wrapper {
            width: 48px;
            height: 48px;
            min-width: 48px;
        }


        .total-display {
            font-size: 20px;
        }


        .change-display {
            font-size: 18px;
        }

    }

</style>



{{-- ========================================================= --}}
{{-- MAIN CARD --}}
{{-- ========================================================= --}}

<div class="card sale-card shadow-sm">


    {{-- HEADER --}}
    <div class="sale-card-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="mb-1 font-weight-bold">

                    <i class="fas fa-receipt mr-2"></i>

                    Transaksi {{ $sale->code }}

                </h5>

                <small class="opacity-75">

                    Perbarui data transaksi penjualan

                </small>

            </div>


            <span class="badge badge-light text-primary px-3 py-2">

                <i class="fas fa-edit mr-1"></i>

                Mode Edit

            </span>

        </div>

    </div>



    {{-- FORM --}}
    <form
        action="{{ route('sales.update', $sale->id) }}"
        method="POST"
        id="formEditSale"
    >

        @csrf

        @method('PUT')


        <div class="card-body p-4">


            {{-- ================================================= --}}
            {{-- ERROR --}}
            {{-- ================================================= --}}

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



            @if($errors->any())

                <div class="alert alert-danger">

                    <div class="font-weight-bold mb-2">

                        <i class="fas fa-exclamation-triangle mr-1"></i>

                        Terdapat kesalahan:

                    </div>


                    <ul class="mb-0 pl-3">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif



            {{-- ================================================= --}}
            {{-- INFORMASI TRANSAKSI --}}
            {{-- ================================================= --}}

            <div class="row mb-4">


                {{-- KODE --}}
                <div class="col-md-6 mb-3 mb-md-0">

                    <div class="info-card p-3">

                        <div class="d-flex align-items-center">

                            <div class="mr-3 text-primary">

                                <i class="fas fa-barcode fa-2x"></i>

                            </div>


                            <div>

                                <div class="info-label">
                                    Kode Penjualan
                                </div>

                                <div class="info-value">
                                    {{ $sale->code }}
                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- TANGGAL --}}
                <div class="col-md-6">

                    <div class="info-card p-3">

                        <div class="d-flex align-items-center">

                            <div class="mr-3 text-success">

                                <i class="fas fa-calendar-alt fa-2x"></i>

                            </div>


                            <div class="flex-grow-1">

                                <div class="info-label">
                                    Tanggal Penjualan
                                </div>


                                <input
                                    type="date"
                                    name="sale_date"
                                    class="form-control @error('sale_date') is-invalid @enderror"
                                    value="{{ old('sale_date', \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d')) }}"
                                    required
                                >


                                @error('sale_date')

                                    <div class="invalid-feedback">

                                        {{ $message }}

                                    </div>

                                @enderror

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- PRODUK --}}
            {{-- ================================================= --}}

            <div class="product-section mb-4">


                {{-- HEADER PRODUK --}}
                <div class="product-section-header">

                    <div class="d-flex justify-content-between align-items-center flex-wrap">

                        <div>

                            <h5 class="mb-1 font-weight-bold">

                                <i class="fas fa-box text-primary mr-2"></i>

                                Produk Penjualan

                            </h5>

                            <small class="text-muted">

                                Atur produk, jumlah dan stok transaksi.

                            </small>

                        </div>


                        <button
                            type="button"
                            class="btn btn-success btn-sm btn-add-product mt-2 mt-md-0"
                            id="btnTambahProduk"
                        >

                            <i class="fas fa-plus mr-1"></i>

                            Tambah Produk

                        </button>

                    </div>

                </div>



                {{-- INFO --}}
                <div class="px-3 pt-3">

                    <div class="alert alert-info py-2 mb-0">

                        <i class="fas fa-info-circle mr-2"></i>

                        Produk yang sudah dipilih tidak dapat dipilih kembali
                        pada baris lain.

                    </div>

                </div>



                {{-- TABLE --}}
                <div class="table-responsive p-3">

                    <table
                        class="table table-hover align-middle mb-0"
                        id="tableProduk"
                    >

                        <thead>

                            <tr class="text-muted">

                                <th width="32%">
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

                                <th width="10%" class="text-center">
                                    Aksi
                                </th>

                            </tr>

                        </thead>


                        <tbody id="produkContainer">


                            @foreach($sale->saleDetails as $detail)

                                @php

                                    $currentProduct = $detail->product;

                                    $availableStock = $currentProduct
                                        ? $currentProduct->stock + $detail->quantity
                                        : 0;

                                @endphp


                                <tr class="product-row">


                                    {{-- PRODUK --}}
                                    <td>

                                        <div class="d-flex align-items-center">


                                            {{-- FOTO --}}
                                            <div class="product-image-wrapper mr-3">

                                                @if($currentProduct)

                                                    <img
                                                        src="{{ $currentProduct->image_url }}"
                                                        class="product-image product-preview"
                                                        alt="{{ $currentProduct->name }}"
                                                        onerror="this.onerror=null;this.src='{{ asset('images/product-placeholder.png') }}';"
                                                    >

                                                @else

                                                    <img
                                                        src="{{ asset('images/product-placeholder.png') }}"
                                                        class="product-image"
                                                        alt="Produk tidak ditemukan"
                                                    >

                                                @endif

                                            </div>


                                            {{-- SELECT --}}
                                            <div class="flex-grow-1">

                                                <select
                                                    name="products[{{ $loop->index }}][product_id]"
                                                    class="form-control product-select"
                                                    required
                                                >

                                                    <option value="">
                                                        -- Pilih Produk --
                                                    </option>


                                                    @foreach($products as $product)

                                                        @php

                                                            $stockForOption =
                                                                $product->stock
                                                                +
                                                                (
                                                                    $product->id == $detail->product_id
                                                                    ? $detail->quantity
                                                                    : 0
                                                                );

                                                        @endphp


                                                        <option
                                                            value="{{ $product->id }}"
                                                            data-price="{{ $product->price }}"
                                                            data-stock="{{ $stockForOption }}"
                                                            data-image="{{ $product->image_url }}"
                                                            @selected($product->id == $detail->product_id)
                                                            {{ $stockForOption <= 0 && $product->id != $detail->product_id ? 'disabled' : '' }}
                                                        >

                                                            {{ $product->name }}

                                                            —
                                                            Stok {{ $stockForOption }}

                                                        </option>

                                                    @endforeach

                                                </select>


                                                <div class="product-meta">

                                                    <span class="stock-badge badge badge-light stock-badge-display">

                                                        <i class="fas fa-box mr-1"></i>

                                                        Stok tersedia:
                                                        {{ $availableStock }}

                                                    </span>

                                                </div>


                                                <small
                                                    class="text-danger product-error d-none"
                                                ></small>

                                            </div>

                                        </div>

                                    </td>



                                    {{-- HARGA --}}
                                    <td>

                                        <input
                                            type="text"
                                            class="form-control price-display"
                                            value="Rp {{ number_format($detail->price, 0, ',', '.') }}"
                                            readonly
                                        >


                                        <input
                                            type="hidden"
                                            name="products[{{ $loop->index }}][price]"
                                            class="price-input"
                                            value="{{ $detail->price }}"
                                        >

                                    </td>



                                    {{-- JUMLAH --}}
                                    <td>

                                        <input
                                            type="number"
                                            name="products[{{ $loop->index }}][quantity]"
                                            class="form-control quantity-input"
                                            value="{{ $detail->quantity }}"
                                            min="1"
                                            max="{{ $availableStock }}"
                                            required
                                        >


                                        <small class="text-muted stock-info">

                                            Stok tersedia:
                                            {{ $availableStock }}

                                        </small>

                                    </td>



                                    {{-- SUBTOTAL --}}
                                    <td>

                                        <input
                                            type="text"
                                            class="form-control subtotal-display"
                                            value="Rp {{ number_format($detail->subtotal, 0, ',', '.') }}"
                                            readonly
                                        >

                                    </td>



                                    {{-- AKSI --}}
                                    <td class="text-center">

                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm btn-delete-product btnHapusProduk"
                                            title="Hapus Produk"
                                        >

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </td>

                                </tr>

                            @endforeach


                        </tbody>

                    </table>


                    {{-- EMPTY --}}
                    <div
                        id="emptyProductMessage"
                        class="empty-product"
                        style="{{ $sale->saleDetails->count() > 0 ? 'display:none;' : '' }}"
                    >

                        <i class="fas fa-box-open"></i>

                        <h5 class="font-weight-bold">
                            Belum ada produk
                        </h5>

                        <p class="mb-3">
                            Tambahkan minimal satu produk untuk transaksi.
                        </p>


                        <button
                            type="button"
                            class="btn btn-primary btn-sm"
                            onclick="tambahProduk()"
                        >

                            <i class="fas fa-plus mr-1"></i>

                            Tambah Produk

                        </button>

                    </div>

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- PEMBAYARAN --}}
            {{-- ================================================= --}}

            <div class="row justify-content-end">


                <div class="col-lg-5 col-md-7">


                    <div class="card payment-card">


                        {{-- HEADER --}}
                        <div class="payment-header">

                            <div class="d-flex align-items-center">

                                <i class="fas fa-money-bill-wave fa-lg mr-3"></i>

                                <div>

                                    <h5 class="mb-0 font-weight-bold">
                                        Pembayaran
                                    </h5>

                                    <small>
                                        Informasi pembayaran transaksi
                                    </small>

                                </div>

                            </div>

                        </div>



                        <div class="card-body p-4">


                            {{-- TOTAL --}}
                            <div class="d-flex justify-content-between align-items-center">

                                <span class="text-muted">
                                    Total Penjualan
                                </span>


                                <span
                                    class="total-display"
                                    id="grandTotal"
                                >
                                    Rp 0
                                </span>

                            </div>


                            <hr>


                            {{-- BAYAR --}}
                            <div class="form-group">

                                <label
                                    for="paid"
                                    class="font-weight-bold"
                                >

                                    Uang Bayar

                                    <span class="text-danger">
                                        *
                                    </span>

                                </label>


                                <div class="input-group input-group-lg">

                                    <div class="input-group-prepend">

                                        <span class="input-group-text">
                                            Rp
                                        </span>

                                    </div>


                                    <input
                                        type="number"
                                        name="paid"
                                        id="paid"
                                        class="form-control @error('paid') is-invalid @enderror"
                                        min="0"
                                        step="1"
                                        value="{{ old('paid', $sale->paid) }}"
                                        required
                                    >

                                </div>


                                @error('paid')

                                    <div class="text-danger small mt-1">

                                        {{ $message }}

                                    </div>

                                @enderror

                            </div>



                            {{-- KEMBALIAN --}}
                            <div class="d-flex justify-content-between align-items-center mt-4">

                                <span class="font-weight-bold">
                                    Kembalian
                                </span>


                                <strong
                                    id="changeDisplay"
                                    class="change-display text-success"
                                >
                                    Rp 0
                                </strong>

                            </div>



                            {{-- STATUS --}}
                            <div
                                id="paymentStatus"
                                class="mt-3"
                            ></div>


                        </div>

                    </div>

                </div>

            </div>



            {{-- ================================================= --}}
            {{-- CATATAN --}}
            {{-- ================================================= --}}

            <div class="form-group mt-4">

                <label
                    for="notes"
                    class="font-weight-bold"
                >

                    <i class="fas fa-sticky-note text-warning mr-1"></i>

                    Catatan

                </label>


                <textarea
                    name="notes"
                    id="notes"
                    rows="3"
                    class="form-control"
                    placeholder="Tambahkan catatan transaksi jika diperlukan..."
                >{{ old('notes', $sale->notes) }}</textarea>

            </div>


        </div>



        {{-- ================================================= --}}
        {{-- FOOTER --}}
        {{-- ================================================= --}}

        <div class="card-footer bg-white border-top p-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap">


                <a
                    href="{{ route('sales.index') }}"
                    class="btn btn-outline-secondary"
                >

                    <i class="fas fa-arrow-left mr-1"></i>

                    Batal

                </a>


                <button
                    type="submit"
                    class="btn btn-primary px-4 mt-2 mt-md-0"
                    id="btnSimpan"
                >

                    <i class="fas fa-save mr-1"></i>

                    Simpan Perubahan

                </button>

            </div>

        </div>

    </form>

</div>

@stop



@section('js')

<script>

/*
|--------------------------------------------------------------------------
| INDEX PRODUK
|--------------------------------------------------------------------------
*/

let productIndex = {{ $sale->saleDetails->count() }};


/*
|--------------------------------------------------------------------------
| DATA PRODUK
|--------------------------------------------------------------------------
*/

const products = @json($products);


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
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(text)
{

    return $('<div>')
        .text(text ?? '')
        .html();

}


/*
|--------------------------------------------------------------------------
| GET SELECTED PRODUCT IDS
|--------------------------------------------------------------------------
*/

function getSelectedProductIds()
{

    let selectedIds = [];


    $('.product-select').each(function() {

        let value = $(this).val();


        if (value) {

            selectedIds.push(
                String(value)
            );

        }

    });


    return selectedIds;

}


/*
|--------------------------------------------------------------------------
| UPDATE PRODUCT OPTIONS
|--------------------------------------------------------------------------
*/

function updateProductOptions()
{

    let selectedIds =
        getSelectedProductIds();


    $('.product-select').each(function() {

        let currentValue =
            String(
                $(this).val() || ''
            );


        $(this)
            .find('option')
            .each(function() {

                let optionValue =
                    String(
                        $(this).val() || ''
                    );


                if (!optionValue) {

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | JANGAN DISABLE PRODUK STOK 0
                | YANG SEDANG DIGUNAKAN BARIS INI
                |--------------------------------------------------------------------------
                */

                let baseDisabled =
                    $(this).attr('data-stock') !== undefined
                    &&
                    parseInt(
                        $(this).attr('data-stock')
                    ) <= 0;


                /*
                |--------------------------------------------------------------------------
                | DUPLIKAT
                |--------------------------------------------------------------------------
                */

                if (
                    selectedIds.includes(optionValue)
                    &&
                    optionValue !== currentValue
                ) {

                    $(this)
                        .prop('disabled', true);

                } else if (
                    baseDisabled
                    &&
                    optionValue !== currentValue
                ) {

                    $(this)
                        .prop('disabled', true);

                } else {

                    $(this)
                        .prop('disabled', false);

                }

            });

    });

}


/*
|--------------------------------------------------------------------------
| BUILD PRODUCT OPTIONS
|--------------------------------------------------------------------------
*/

function buildProductOptions()
{

    let options =
        '<option value="">-- Pilih Produk --</option>';


    products.forEach(function(product) {

        let stock =
            parseInt(product.stock) || 0;


        let price =
            parseFloat(product.price) || 0;


        let image =
            product.image_url
            ||
            '{{ asset('images/product-placeholder.png') }}';


        let disabled =
            stock <= 0
            ? 'disabled'
            : '';


        options += `

            <option
                value="${product.id}"
                data-price="${price}"
                data-stock="${stock}"
                data-image="${escapeHtml(image)}"
                ${disabled}
            >

                ${escapeHtml(product.name)}
                —
                Stok ${stock}

            </option>

        `;

    });


    return options;

}


/*
|--------------------------------------------------------------------------
| TAMBAH PRODUK
|--------------------------------------------------------------------------
*/

function tambahProduk()
{

    let row = `

        <tr class="product-row">


            <td>

                <div class="d-flex align-items-center">


                    <div class="product-image-wrapper mr-3">

                        <img
                            src="{{ asset('images/product-placeholder.png') }}"
                            class="product-image product-preview"
                            alt="Preview Produk"
                        >

                    </div>


                    <div class="flex-grow-1">

                        <select
                            name="products[${productIndex}][product_id]"
                            class="form-control product-select"
                            required
                        >

                            ${buildProductOptions()}

                        </select>


                        <div class="product-meta">

                            <span class="stock-badge badge badge-light stock-badge-display">

                                <i class="fas fa-box mr-1"></i>

                                Pilih produk terlebih dahulu

                            </span>

                        </div>


                        <small
                            class="text-danger product-error d-none"
                        ></small>

                    </div>

                </div>

            </td>



            <td>

                <input
                    type="text"
                    class="form-control price-display"
                    value="Rp 0"
                    readonly
                >


                <input
                    type="hidden"
                    name="products[${productIndex}][price]"
                    class="price-input"
                    value="0"
                >

            </td>



            <td>

                <input
                    type="number"
                    name="products[${productIndex}][quantity]"
                    class="form-control quantity-input"
                    value="1"
                    min="1"
                    disabled
                    required
                >


                <small class="text-muted stock-info">

                    Pilih produk terlebih dahulu.

                </small>

            </td>



            <td>

                <input
                    type="text"
                    class="form-control subtotal-display"
                    value="Rp 0"
                    readonly
                >

            </td>



            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-danger btn-sm btn-delete-product btnHapusProduk"
                    title="Hapus Produk"
                >

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        </tr>

    `;


    $('#produkContainer')
        .append(row);


    productIndex++;


    updateProductOptions();

    updateEmptyMessage();

    hitungTotal();

}


/*
|--------------------------------------------------------------------------
| PRODUCT SELECT CHANGE
|--------------------------------------------------------------------------
*/

$(document).on(
    'change',
    '.product-select',
    function()
    {

        let select = $(this);

        let selected =
            select.find(':selected');

        let productId =
            select.val();

        let row =
            select.closest('.product-row');


        /*
        |--------------------------------------------------------------------------
        | BELUM PILIH
        |--------------------------------------------------------------------------
        */

        if (!productId) {

            resetProductRow(row);

            updateProductOptions();

            hitungTotal();

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT
        |--------------------------------------------------------------------------
        */

        let duplicate = false;


        $('.product-select')
            .not(select)
            .each(function() {

                if (
                    String($(this).val())
                    ===
                    String(productId)
                ) {

                    duplicate = true;

                }

            });


        if (duplicate) {

            alert(
                'Produk tersebut sudah dipilih pada baris lain.'
            );


            select.val('');


            resetProductRow(row);

            updateProductOptions();

            hitungTotal();

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        let price =
            parseFloat(
                selected.attr('data-price')
            ) || 0;


        let stock =
            parseInt(
                selected.attr('data-stock')
            ) || 0;


        let image =
            selected.attr('data-image')
            ||
            '{{ asset('images/product-placeholder.png') }}';


        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */

        row.find('.product-preview')
            .attr(
                'src',
                image
            )
            .attr(
                'alt',
                selected.text().trim()
            )
            .on(
                'error',
                function() {

                    this.onerror = null;

                    this.src =
                        '{{ asset('images/product-placeholder.png') }}';

                }
            );


        /*
        |--------------------------------------------------------------------------
        | HARGA
        |--------------------------------------------------------------------------
        */

        row.find('.price-display')
            .val(
                formatRupiah(price)
            );


        row.find('.price-input')
            .val(price);


        /*
        |--------------------------------------------------------------------------
        | STOK
        |--------------------------------------------------------------------------
        */

        row.find('.quantity-input')
            .attr(
                'max',
                stock
            )
            .val(1)
            .prop(
                'disabled',
                stock <= 0
            );


        row.find('.stock-info')
            .removeClass(
                'text-danger'
            )
            .addClass(
                'text-muted'
            )
            .text(
                'Stok tersedia: ' + stock
            );


        row.find('.stock-badge-display')
            .removeClass(
                'badge-light badge-danger badge-warning badge-success'
            );


        if (stock <= 0) {

            row.find('.stock-badge-display')
                .addClass('badge-danger')
                .html(`
                    <i class="fas fa-times-circle mr-1"></i>
                    Stok Habis
                `);

        } else if (stock <= 5) {

            row.find('.stock-badge-display')
                .addClass('badge-warning')
                .html(`
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Stok Menipis: ${stock}
                `);

        } else {

            row.find('.stock-badge-display')
                .addClass('badge-success')
                .html(`
                    <i class="fas fa-check-circle mr-1"></i>
                    Stok: ${stock}
                `);

        }


        /*
        |--------------------------------------------------------------------------
        | SUBTOTAL
        |--------------------------------------------------------------------------
        */

        hitungSubtotal(row);


        /*
        |--------------------------------------------------------------------------
        | UPDATE OPTION
        |--------------------------------------------------------------------------
        */

        updateProductOptions();

    }
);


/*
|--------------------------------------------------------------------------
| RESET PRODUCT ROW
|--------------------------------------------------------------------------
*/

function resetProductRow(row)
{

    row.find('.product-preview')
        .attr(
            'src',
            '{{ asset('images/product-placeholder.png') }}'
        );


    row.find('.price-display')
        .val('Rp 0');


    row.find('.price-input')
        .val(0);


    row.find('.quantity-input')
        .val(1)
        .prop(
            'disabled',
            true
        )
        .removeAttr('max');


    row.find('.stock-info')
        .removeClass(
            'text-danger'
        )
        .addClass(
            'text-muted'
        )
        .text(
            'Pilih produk terlebih dahulu.'
        );


    row.find('.stock-badge-display')
        .removeClass(
            'badge-success badge-warning badge-danger'
        )
        .addClass(
            'badge-light'
        )
        .html(`
            <i class="fas fa-box mr-1"></i>
            Pilih produk terlebih dahulu
        `);


    row.find('.subtotal-display')
        .val('Rp 0');


    row.find('.product-error')
        .addClass('d-none')
        .text('');

}


/*
|--------------------------------------------------------------------------
| QUANTITY CHANGE
|--------------------------------------------------------------------------
*/

$(document).on(
    'input',
    '.quantity-input',
    function()
    {

        let input =
            $(this);

        let row =
            input.closest('.product-row');


        let quantity =
            parseInt(
                input.val()
            ) || 0;


        let stock =
            parseInt(
                input.attr('max')
            ) || 0;


        /*
        |--------------------------------------------------------------------------
        | MELEBIHI STOK
        |--------------------------------------------------------------------------
        */

        if (
            stock > 0
            &&
            quantity > stock
        ) {

            input.val(stock);

            quantity = stock;


            row.find('.stock-info')
                .removeClass(
                    'text-muted'
                )
                .addClass(
                    'text-danger'
                )
                .text(
                    'Jumlah maksimal: ' + stock
                );

        } else {

            row.find('.stock-info')
                .removeClass(
                    'text-danger'
                )
                .addClass(
                    'text-muted'
                )
                .text(
                    'Stok tersedia: ' + stock
                );

        }


        /*
        |--------------------------------------------------------------------------
        | MINIMAL
        |--------------------------------------------------------------------------
        */

        if (
            quantity < 1
            &&
            input.val() !== ''
        ) {

            input.val(1);

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
        ) || 0;


    let quantity =
        parseInt(
            row.find('.quantity-input').val()
        ) || 0;


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
            ) || 0;


        let quantity =
            parseInt(
                $(this)
                    .find('.quantity-input')
                    .val()
            ) || 0;


        total +=
            price * quantity;

    });


    $('#grandTotal')
        .text(
            formatRupiah(total)
        );


    hitungKembalian();

}


/*
|--------------------------------------------------------------------------
| HITUNG KEMBALIAN
|--------------------------------------------------------------------------
*/

function hitungKembalian()
{

    let total = 0;


    $('.product-row').each(function() {

        let price =
            parseFloat(
                $(this)
                    .find('.price-input')
                    .val()
            ) || 0;


        let quantity =
            parseInt(
                $(this)
                    .find('.quantity-input')
                    .val()
            ) || 0;


        total +=
            price * quantity;

    });


    let paid =
        parseFloat(
            $('#paid').val()
        ) || 0;


    let change =
        paid - total;


    /*
    |--------------------------------------------------------------------------
    | TIDAK ADA PRODUK
    |--------------------------------------------------------------------------
    */

    let hasProduct = false;


    $('.product-select').each(function() {

        if ($(this).val()) {

            hasProduct = true;

        }

    });


    if (!hasProduct) {

        $('#changeDisplay')
            .removeClass(
                'text-success text-danger'
            )
            .addClass(
                'text-muted'
            )
            .text('Rp 0');


        $('#paymentStatus')
            .html(`

                <div class="alert alert-warning mb-0">

                    <i class="fas fa-box-open mr-1"></i>

                    Silakan pilih minimal satu produk.

                </div>

            `);


        $('#btnSimpan')
            .prop(
                'disabled',
                true
            );


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | PEMBAYARAN KURANG
    |--------------------------------------------------------------------------
    */

    if (paid < total) {

        let kurang =
            total - paid;


        $('#changeDisplay')
            .removeClass(
                'text-success text-muted'
            )
            .addClass(
                'text-danger'
            )
            .text(
                '- ' +
                formatRupiah(kurang)
            );


        $('#paymentStatus')
            .html(`

                <div class="alert alert-danger mb-0">

                    <i class="fas fa-exclamation-circle mr-1"></i>

                    Pembayaran masih kurang

                    <strong>
                        ${formatRupiah(kurang)}
                    </strong>

                </div>

            `);


        $('#btnSimpan')
            .prop(
                'disabled',
                true
            );


        return;

    }


    /*
    |--------------------------------------------------------------------------
    | CUKUP
    |--------------------------------------------------------------------------
    */

    $('#changeDisplay')
        .removeClass(
            'text-danger text-muted'
        )
        .addClass(
            'text-success'
        )
        .text(
            formatRupiah(change)
        );


    $('#paymentStatus')
        .html(`

            <div class="alert alert-success mb-0">

                <i class="fas fa-check-circle mr-1"></i>

                Pembayaran cukup.

                Kembalian:

                <strong>
                    ${formatRupiah(change)}
                </strong>

            </div>

        `);


    $('#btnSimpan')
        .prop(
            'disabled',
            false
        );

}


/*
|--------------------------------------------------------------------------
| UANG BAYAR BERUBAH
|--------------------------------------------------------------------------
*/

$(document).on(
    'input',
    '#paid',
    function() {

        hitungKembalian();

    }
);


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

        let row =
            $(this).closest('.product-row');


        row.fadeOut(
            150,
            function() {

                row.remove();


                updateEmptyMessage();

                updateProductOptions();

                hitungTotal();

            }
        );

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

    } else {

        $('#emptyProductMessage')
            .hide();

    }

}


/*
|--------------------------------------------------------------------------
| TAMBAH PRODUK
|--------------------------------------------------------------------------
*/

$(document).on(
    'click',
    '#btnTambahProduk',
    function()
    {

        let selectedIds =
            getSelectedProductIds();


        let availableProduct =
            products.some(
                function(product) {

                    let stock =
                        parseInt(
                            product.stock
                        ) || 0;


                    return (
                        stock > 0
                        &&
                        !selectedIds.includes(
                            String(product.id)
                        )
                    );

                }
            );


        if (!availableProduct) {

            alert(
                'Tidak ada produk lain yang tersedia untuk dipilih.'
            );


            return;

        }


        tambahProduk();

    }
);


/*
|--------------------------------------------------------------------------
| VALIDASI FORM
|--------------------------------------------------------------------------
*/

$('#formEditSale').on(
    'submit',
    function(event)
    {

        let valid = true;

        let hasProduct = false;


        /*
        |--------------------------------------------------------------------------
        | VALIDASI BARIS
        |--------------------------------------------------------------------------
        */

        $('.product-row').each(function() {

            let row =
                $(this);


            let productId =
                row.find(
                    '.product-select'
                ).val();


            let quantity =
                parseInt(
                    row.find(
                        '.quantity-input'
                    ).val()
                ) || 0;


            let stock =
                parseInt(
                    row.find(
                        '.quantity-input'
                    ).attr('max')
                ) || 0;


            /*
            |--------------------------------------------------------------------------
            | PRODUK
            |--------------------------------------------------------------------------
            */

            if (!productId) {

                valid = false;


                row.find('.product-error')
                    .removeClass('d-none')
                    .text(
                        'Produk wajib dipilih.'
                    );

            } else {

                hasProduct = true;


                row.find('.product-error')
                    .addClass('d-none')
                    .text('');

            }


            /*
            |--------------------------------------------------------------------------
            | JUMLAH
            |--------------------------------------------------------------------------
            */

            if (
                productId
                &&
                (
                    quantity < 1
                    ||
                    quantity > stock
                )
            ) {

                valid = false;


                row.find('.quantity-input')
                    .addClass('is-invalid');


                row.find('.stock-info')
                    .removeClass(
                        'text-muted'
                    )
                    .addClass(
                        'text-danger'
                    )
                    .text(
                        'Jumlah melebihi stok tersedia.'
                    );

            } else {

                row.find('.quantity-input')
                    .removeClass(
                        'is-invalid'
                    );

            }

        });


        /*
        |--------------------------------------------------------------------------
        | MINIMAL SATU PRODUK
        |--------------------------------------------------------------------------
        */

        if (!hasProduct) {

            valid = false;


            alert(
                'Minimal harus ada satu produk.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CEK DUPLIKAT
        |--------------------------------------------------------------------------
        */

        let selectedIds = [];


        $('.product-select').each(function() {

            let value =
                $(this).val();


            if (value) {

                if (
                    selectedIds.includes(
                        String(value)
                    )
                ) {

                    valid = false;

                }


                selectedIds.push(
                    String(value)
                );

            }

        });


        if (!valid) {

            event.preventDefault();

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | CEK PEMBAYARAN
        |--------------------------------------------------------------------------
        */

        let total = 0;


        $('.product-row').each(function() {

            let price =
                parseFloat(
                    $(this)
                        .find('.price-input')
                        .val()
                ) || 0;


            let quantity =
                parseInt(
                    $(this)
                        .find('.quantity-input')
                        .val()
                ) || 0;


            total +=
                price * quantity;

        });


        let paid =
            parseFloat(
                $('#paid').val()
            ) || 0;


        if (paid < total) {

            event.preventDefault();


            alert(
                'Uang pembayaran masih kurang.'
            );


            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | DISABLE BUTTON
        |--------------------------------------------------------------------------
        */

        $('#btnSimpan')
            .prop(
                'disabled',
                true
            )
            .html(`

                <i class="fas fa-spinner fa-spin mr-1"></i>

                Menyimpan...

            `);

    }
);


/*
|--------------------------------------------------------------------------
| DOCUMENT READY
|--------------------------------------------------------------------------
*/

$(document).ready(function() {


    /*
    |--------------------------------------------------------------------------
    | SETUP BARIS YANG SUDAH ADA
    |--------------------------------------------------------------------------
    */

    $('.product-row').each(function() {

        let row =
            $(this);


        let select =
            row.find(
                '.product-select'
            );


        let selected =
            select.find(
                ':selected'
            );


        let price =
            parseFloat(
                selected.attr(
                    'data-price'
                )
            ) || 0;


        let stock =
            parseInt(
                selected.attr(
                    'data-stock'
                )
            ) || 0;


        let image =
            selected.attr(
                'data-image'
            )
            ||
            row.find(
                '.product-preview'
            ).attr('src')
            ||
            '{{ asset('images/product-placeholder.png') }}';


        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */

        row.find('.product-preview')
            .attr(
                'src',
                image
            );


        /*
        |--------------------------------------------------------------------------
        | HARGA
        |--------------------------------------------------------------------------
        */

        row.find('.price-display')
            .val(
                formatRupiah(price)
            );


        row.find('.price-input')
            .val(price);


        /*
        |--------------------------------------------------------------------------
        | STOK
        |--------------------------------------------------------------------------
        */

        row.find('.quantity-input')
            .attr(
                'max',
                stock
            )
            .prop(
                'disabled',
                false
            );


        row.find('.stock-info')
            .text(
                'Stok tersedia: ' + stock
            );


        /*
        |--------------------------------------------------------------------------
        | BADGE STOK
        |--------------------------------------------------------------------------
        */

        row.find('.stock-badge-display')
            .removeClass(
                'badge-light badge-danger badge-warning badge-success'
            );


        if (stock <= 0) {

            row.find('.stock-badge-display')
                .addClass('badge-danger')
                .html(`
                    <i class="fas fa-times-circle mr-1"></i>
                    Stok Habis
                `);

        } else if (stock <= 5) {

            row.find('.stock-badge-display')
                .addClass('badge-warning')
                .html(`
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Stok Menipis: ${stock}
                `);

        } else {

            row.find('.stock-badge-display')
                .addClass('badge-success')
                .html(`
                    <i class="fas fa-check-circle mr-1"></i>
                    Stok: ${stock}
                `);

        }


        /*
        |--------------------------------------------------------------------------
        | SUBTOTAL
        |--------------------------------------------------------------------------
        */

        hitungSubtotal(row);

    });


    /*
    |--------------------------------------------------------------------------
    | UPDATE OPTION
    |--------------------------------------------------------------------------
    */

    updateProductOptions();


    /*
    |--------------------------------------------------------------------------
    | EMPTY
    |--------------------------------------------------------------------------
    */

    updateEmptyMessage();


    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    hitungTotal();

});

</script>

@stop