@extends('layouts.app')

@section('title', 'Struk Penjualan')


@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h2 class="fw-bold mb-1">
            Struk Penjualan
        </h2>

        <p class="text-muted mb-0">
            Detail transaksi penjualan
        </p>

    </div>


    <div>

        <button
            onclick="window.print()"
            class="btn btn-primary"
        >

            <i class="fas fa-print mr-1"></i>

            Cetak Struk

        </button>


        <a
            href="{{ route('sales.index') }}"
            class="btn btn-secondary"
        >

            <i class="fas fa-arrow-left mr-1"></i>

            Kembali

        </a>

    </div>

</div>

@stop



@section('content')

@php

    /*
    |--------------------------------------------------------------------------
    | AMBIL PENGATURAN STRUK
    |--------------------------------------------------------------------------
    */

    $receiptHeader =
        \App\Models\Setting::getValue(
            'receipt_header'
        );

    $receiptFooter =
        \App\Models\Setting::getValue(
            'receipt_footer'
        );

    $appName =
        \App\Models\Setting::getValue(
            'app_name'
        ) ?: 'POS ENTERPRISE';

    $companyName =
        \App\Models\Setting::getValue(
            'company_name'
        );

    $businessAddress = \App\Models\Setting::getValue('business_address');
    $businessPhone = \App\Models\Setting::getValue('business_phone');

@endphp



<div class="row justify-content-center">

    <div class="col-md-5">


        <div class="card receipt-card">

            <div class="card-body">


                {{-- ================================================= --}}
                {{-- HEADER STRUK --}}
                {{-- ================================================= --}}

                <div class="text-center receipt-header">


                    {{-- LOGO --}}
                    @php

                        $logo =
                            \App\Models\Setting::getValue(
                                'app_logo'
                            );

                    @endphp


                    @if($logo)

                        <div class="mb-2">

                            <img
                                src="{{ asset('storage/' . $logo) }}"
                                alt="Logo"
                                class="receipt-logo"
                            >

                        </div>

                    @endif


                    {{-- CUSTOM HEADER --}}
                    <h4 class="font-weight-bold mb-1">{{ $appName }}</h4>
                    @if($businessAddress)<div class="text-muted">{{ $businessAddress }}</div>@endif
                    @if($businessPhone)<div class="text-muted">Telp. {{ $businessPhone }}</div>@endif
                    @if(trim((string) $receiptHeader) !== '')

                        <div class="receipt-custom-header">

                            {!! nl2br(
                                e($receiptHeader)
                            ) !!}

                        </div>

                    @else

                        {{-- DEFAULT HEADER --}}

                        @if($companyName)

                            <div class="text-muted">

                                {{ $companyName }}

                            </div>

                        @endif


                        <div class="text-muted">

                            Struk Penjualan

                        </div>

                    @endif


                </div>



                <hr>



                {{-- ================================================= --}}
                {{-- INFORMASI TRANSAKSI --}}
                {{-- ================================================= --}}

                <div class="d-flex justify-content-between">

                    <span>
                        Kode
                    </span>

                    <strong>
                        {{ $sale->code }}
                    </strong>

                </div>

                <div class="d-flex justify-content-between"><span>Pelanggan</span><strong>{{ $sale->customer_display_name }}</strong></div>
                <div class="d-flex justify-content-between"><span>Kasir</span><strong>{{ $sale->cashier_display_name }}</strong></div>
                <div class="d-flex justify-content-between"><span>Pembayaran</span><strong>{{ $sale->payment_method_display }}</strong></div>
                <div class="d-flex justify-content-between"><span>Status</span><strong>{{ $sale->payment_status_display }}</strong></div>
                <div class="d-flex justify-content-between"><span>Sumber Pesanan</span><strong>{{ $sale->order_source_display }}</strong></div>
                @if($sale->order_source === 'table_qr' || $sale->table)<div class="d-flex justify-content-between"><span>Meja</span><strong>{{ $sale->table?->name ?: '-' }}</strong></div>@endif


                <div class="d-flex justify-content-between">

                    <span>
                        Tanggal
                    </span>

                    <span>

                        @if($sale->sale_date)

                            {{ $sale->sale_date->format('d/m/Y') }}

                        @else

                            -

                        @endif

                    </span>

                </div>



                <hr>



                {{-- ================================================= --}}
                {{-- DETAIL PRODUK --}}
                {{-- ================================================= --}}

                @foreach($sale->saleDetails as $detail)

                    <div class="mb-3">


                        <div class="font-weight-bold">

                            {{ $detail->product->name }}

                        </div>


                        <div class="d-flex justify-content-between">

                            <span>

                                {{ $detail->quantity }}

                                x

                                Rp
                                {{ number_format(
                                    $detail->price,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </span>


                            <strong>

                                Rp
                                {{ number_format(
                                    $detail->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </strong>

                        </div>


                        {{-- CATATAN PRODUK --}}
                        @if(!empty($detail->note))

                            <div class="receipt-item-note">

                                Catatan:
                                {{ $detail->note }}

                            </div>

                        @endif


                    </div>

                @endforeach



                <hr>



                {{-- ================================================= --}}
                {{-- TOTAL --}}
                {{-- ================================================= --}}

                <div class="d-flex justify-content-between mb-2">

                    <strong>
                        TOTAL
                    </strong>

                    <strong>

                        Rp
                        {{ number_format(
                            $sale->total,
                            0,
                            ',',
                            '.'
                        ) }}

                    </strong>

                </div>



                {{-- ================================================= --}}
                {{-- BAYAR --}}
                {{-- ================================================= --}}

                <div class="d-flex justify-content-between mb-2">

                    <strong>
                        BAYAR
                    </strong>

                    <strong>

                        Rp
                        {{ number_format(
                            $sale->paid,
                            0,
                            ',',
                            '.'
                        ) }}

                    </strong>

                </div>



                {{-- ================================================= --}}
                {{-- KEMBALIAN --}}
                {{-- ================================================= --}}

                <div class="d-flex justify-content-between">

                    <strong>
                        KEMBALI
                    </strong>

                    <strong>

                        Rp
                        {{ number_format(
                            $sale->change,
                            0,
                            ',',
                            '.'
                        ) }}

                    </strong>

                </div>



                <hr>



                {{-- ================================================= --}}
                {{-- FOOTER STRUK --}}
                {{-- ================================================= --}}

                <div class="text-center mt-4 receipt-footer">


                    @if(trim((string) $receiptFooter) !== '')

                        {!! nl2br(
                            e($receiptFooter)
                        ) !!}

                    @else

                        {{-- DEFAULT FOOTER --}}

                        <strong>

                            Terima kasih atas

                        </strong>

                        <br>

                        <strong>

                            kunjungan Anda

                        </strong>

                    @endif


                </div>



               
            </div>

        </div>

    </div>

</div>

@stop



@section('css')

<style>

    /*
    |--------------------------------------------------------------------------
    | RECEIPT
    |--------------------------------------------------------------------------
    */

    .receipt-card {

        max-width: 420px;

        margin: auto;

    }


    .receipt-card .card-body {

        padding: 30px;

    }


    /*
    |--------------------------------------------------------------------------
    | LOGO
    |--------------------------------------------------------------------------
    */

    .receipt-logo {

        max-width: 90px;

        max-height: 70px;

        object-fit: contain;

    }


    /*
    |--------------------------------------------------------------------------
    | HEADER CUSTOM
    |--------------------------------------------------------------------------
    */

    .receipt-custom-header {

        font-weight: 700;

        line-height: 1.5;

        white-space: normal;

    }


    /*
    |--------------------------------------------------------------------------
    | FOOTER CUSTOM
    |--------------------------------------------------------------------------
    */

    .receipt-footer {

        line-height: 1.6;

        white-space: normal;

    }


    /*
    |--------------------------------------------------------------------------
    | CATATAN ITEM
    |--------------------------------------------------------------------------
    */

    .receipt-item-note {

        font-size: 12px;

        color: #666;

        margin-top: 3px;

    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    @media print {


        body {

            background: white !important;

        }


        .main-header,
        .main-sidebar,
        .main-footer,
        .content-header,
        .btn,
        .breadcrumb {

            display: none !important;

        }


        .content-wrapper {

            margin-left: 0 !important;

            background: white !important;

        }


        .content {

            padding: 0 !important;

        }


        .receipt-card {

            width: 80mm;

            max-width: 80mm;

            margin: 0 auto;

            border: none !important;

            box-shadow: none !important;

        }


        .receipt-card .card-body {

            padding: 5mm;

        }


        .receipt-card hr {

            border-top: 1px dashed #000;

        }


        .receipt-logo {

            max-width: 25mm;

            max-height: 20mm;

        }


        * {

            color: #000 !important;

        }

    }

</style>

@stop
