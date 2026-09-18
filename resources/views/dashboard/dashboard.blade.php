@extends('layouts.app')


@section('title', 'Dashboard POS Enterprise')


{{-- =========================================================
| CONTENT HEADER
========================================================= --}}

@section('content_header')

<div class="dashboard-header">

    <div class="header-left">

        <div class="header-icon">
            <i class="fas fa-chart-line"></i>
        </div>

        <div>

            <h2 class="dashboard-title">
                Dashboard
            </h2>

            <p class="dashboard-subtitle">
                Selamat datang kembali 👋
                Kelola penjualan dan stok dengan mudah.
            </p>

        </div>

    </div>


    <div class="header-actions">

        <a
            href="{{ route('sales.create') }}"
            class="btn btn-primary btn-new-sale"
        >

            <i class="fas fa-cash-register mr-2"></i>

            Transaksi Baru

        </a>

    </div>

</div>

@stop



{{-- =========================================================
| CONTENT
========================================================= --}}

@section('content')

<div class="dashboard-container">


    {{-- =====================================================
    | QUICK ACTION
    ====================================================== --}}

    <div class="quick-actions">

        <a
            href="{{ route('sales.create') }}"
            class="quick-action-item"
        >

            <div class="quick-action-icon bg-primary-soft">

                <i class="fas fa-cash-register text-primary"></i>

            </div>

            <div>

                <strong>
                    Kasir
                </strong>

                <small>
                    Buat transaksi baru
                </small>

            </div>

            <i class="fas fa-chevron-right ml-auto"></i>

        </a>


        <a
            href="{{ route('sales.index') }}"
            class="quick-action-item"
        >

            <div class="quick-action-icon bg-success-soft">

                <i class="fas fa-receipt text-success"></i>

            </div>

            <div>

                <strong>
                    Penjualan
                </strong>

                <small>
                    Lihat transaksi
                </small>

            </div>

            <i class="fas fa-chevron-right ml-auto"></i>

        </a>


        <a
            href="{{ route('products.index') }}"
            class="quick-action-item"
        >

            <div class="quick-action-icon bg-warning-soft">

                <i class="fas fa-box text-warning"></i>

            </div>

            <div>

                <strong>
                    Produk
                </strong>

                <small>
                    Kelola produk
                </small>

            </div>

            <i class="fas fa-chevron-right ml-auto"></i>

        </a>

    </div>



    {{-- =====================================================
    | STATISTIC CARDS
    ====================================================== --}}

    <div class="dashboard-section">

        @include('dashboard.partials.cards')

    </div>



    {{-- =====================================================
    | CHART + BEST PRODUCT
    ====================================================== --}}

    <div class="row dashboard-row">

        {{-- SALES CHART --}}

        <div class="col-lg-8 mb-4">

            <div class="modern-card h-100">

                <div class="modern-card-header">

                    <div>

                        <h5 class="modern-card-title">

                            <i class="fas fa-chart-area mr-2"></i>

                            Grafik Penjualan

                        </h5>

                        <p class="modern-card-subtitle">

                            Performa penjualan Anda

                        </p>

                    </div>


                    <div class="chart-filter">

                        <select
                            id="chartPeriod"
                            class="form-control form-control-sm"
                        >

                            <option value="monthly">
                                Bulanan
                            </option>

                            <option value="weekly">
                                Mingguan
                            </option>

                            <option value="daily">
                                Harian
                            </option>

                        </select>

                    </div>

                </div>


                <div class="modern-card-body chart-wrapper">

                    @include('dashboard.partials.chart')

                </div>

            </div>

        </div>



        {{-- BEST PRODUCT --}}

        <div class="col-lg-4 mb-4">

            <div class="modern-card h-100">

                <div class="modern-card-header">

                    <div>

                        <h5 class="modern-card-title">

                            <i class="fas fa-fire mr-2 text-danger"></i>

                            Produk Terlaris

                        </h5>

                        <p class="modern-card-subtitle">

                            Produk dengan penjualan tertinggi

                        </p>

                    </div>

                </div>


                <div class="modern-card-body">

                    @include('dashboard.partials.best')

                </div>

            </div>

        </div>

    </div>


<div class="row">

    <div class="col-lg-8 mb-4">

        <div class="modern-card">

            <div class="modern-card-header">

                <div>

                    <h5 class="modern-card-title">

                        <i class="fas fa-history mr-2"></i>

                        Transaksi Terbaru

                    </h5>

                    <p class="modern-card-subtitle">

                        Aktivitas transaksi terakhir

                    </p>

                </div>


                <a
                    href="{{ route('sales.index') }}"
                    class="btn btn-sm btn-light"
                >

                    Lihat Semua

                    <i class="fas fa-arrow-right ml-1"></i>

                </a>

            </div>


            <div class="modern-card-body p-0">

                <div class="table-responsive">

                    <table class="table modern-table">

                        <thead>

                            <tr>

                                <th>
                                    Kode
                                </th>

                                <th>
                                    Tanggal
                                </th>

                                <th>
                                    Produk
                                </th>

                                <th>
                                    Total
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($recentSales as $sale)

                                <tr>

                                    <td>

                                        <a
                                            href="{{ route('sales.show', $sale->id) }}"
                                            class="sale-code"
                                        >

                                            {{ $sale->code }}

                                        </a>

                                    </td>


                                    <td>

                                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}

                                    </td>


                                    <td>

                                        {{ $sale->saleDetails->sum('quantity') }}

                                        item

                                    </td>


                                    <td>

                                        <strong>

                                            Rp
                                            {{ number_format($sale->total, 0, ',', '.') }}

                                        </strong>

                                    </td>


                                    <td>

                                        <span class="status-paid">

                                            <i class="fas fa-check-circle mr-1"></i>

                                            Lunas

                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-5"
                                    >

                                        <i class="fas fa-receipt fa-2x text-muted mb-2"></i>

                                        <p class="text-muted mb-0">

                                            Belum ada transaksi.

                                        </p>

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>


    <div class="col-lg-4 mb-4">

        <div class="modern-card h-100">

            <div class="modern-card-header">

                <div>

                    <h5 class="modern-card-title">

                        <i class="fas fa-boxes mr-2"></i>

                        Ringkasan Stok

                    </h5>

                    <p class="modern-card-subtitle">

                        Kondisi inventori

                    </p>

                </div>

            </div>


            <div class="modern-card-body">

                <div class="inventory-summary">

                    <div class="inventory-item">

                        <div class="inventory-icon available">

                            <i class="fas fa-check"></i>

                        </div>

                        <div>

                            <strong>
                                {{ $availableStock }}
                            </strong>

                            <span>
                                Stok tersedia
                            </span>

                        </div>

                    </div>


                    <div class="inventory-item">

                        <div class="inventory-icon warning">

                            <i class="fas fa-exclamation"></i>

                        </div>

                        <div>

                            <strong>
                                {{ $lowStock }}
                            </strong>

                            <span>
                                Stok menipis
                            </span>

                        </div>

                    </div>


                    <div class="inventory-item">

                        <div class="inventory-icon danger">

                            <i class="fas fa-times"></i>

                        </div>

                        <div>

                            <strong>
                                {{ $outOfStock }}
                            </strong>

                            <span>
                                Produk habis
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>
    {{-- =====================================================
    | STOCK
    ====================================================== --}}

    <div class="row dashboard-row">

        <div class="col-lg-12 mb-4">

            <div class="modern-card">

                <div class="modern-card-header">

                    <div>

                        <h5 class="modern-card-title">

                            <i class="fas fa-boxes mr-2"></i>

                            Monitoring Stok

                        </h5>

                        <p class="modern-card-subtitle">

                            Pantau produk yang membutuhkan perhatian

                        </p>

                    </div>


                    <a
                        href="{{ route('products.index') }}"
                        class="btn btn-outline-primary btn-sm"
                    >

                        Lihat Semua

                        <i class="fas fa-arrow-right ml-1"></i>

                    </a>

                </div>


                <div class="modern-card-body">

                    @include('dashboard.partials.stock')

                </div>

            </div>

        </div>

    </div>


</div>

@stop



{{-- =========================================================
| CSS
========================================================= --}}

@section('css')

<style>

/* =========================================================
   GLOBAL
========================================================= */

body {
    background: #f5f7fb;
}

.content-wrapper {
    background: #f5f7fb !important;
}

.dashboard-container {
    padding-bottom: 30px;
}


/* =========================================================
   HEADER
========================================================= */

.dashboard-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 25px;

}

.header-left {

    display: flex;

    align-items: center;

    gap: 15px;

}

.header-icon {

    width: 52px;

    height: 52px;

    border-radius: 14px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: linear-gradient(
        135deg,
        #2563eb,
        #3b82f6
    );

    color: #fff;

    font-size: 21px;

    box-shadow:
        0 8px 20px rgba(
            37,
            99,
            235,
            .22
        );

}

.dashboard-title {

    margin: 0;

    font-size: 28px;

    font-weight: 700;

    color: #111827;

}

.dashboard-subtitle {

    margin: 3px 0 0;

    color: #6b7280;

    font-size: 14px;

}

.btn-new-sale {

    border: none;

    border-radius: 10px;

    padding: 10px 18px;

    font-weight: 600;

    box-shadow:
        0 5px 15px rgba(
            37,
            99,
            235,
            .20
        );

    transition: .2s;

}

.btn-new-sale:hover {

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(
            37,
            99,
            235,
            .30
        );

}


/* =========================================================
   QUICK ACTION
========================================================= */

.quick-actions {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

    margin-bottom: 25px;

}

.quick-action-item {

    display: flex;

    align-items: center;

    gap: 13px;

    background: #fff;

    border: 1px solid #edf0f5;

    border-radius: 14px;

    padding: 14px 16px;

    color: #1f2937;

    text-decoration: none !important;

    transition:
        transform .2s,
        box-shadow .2s,
        border-color .2s;

}

.quick-action-item:hover {

    transform: translateY(-2px);

    border-color: #dbe4f0;

    box-shadow:
        0 8px 25px rgba(
            15,
            23,
            42,
            .07
        );

}

.quick-action-item strong {

    display: block;

    font-size: 14px;

}

.quick-action-item small {

    display: block;

    color: #9ca3af;

    font-size: 12px;

    margin-top: 2px;

}

.quick-action-item > i {

    color: #c4cbd5;

    font-size: 12px;

}

.quick-action-icon {

    width: 42px;

    height: 42px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 17px;

}

.bg-primary-soft {

    background: #eff6ff;

}

.bg-success-soft {

    background: #ecfdf5;

}

.bg-warning-soft {

    background: #fffbeb;

}


/* =========================================================
   SECTION
========================================================= */

.dashboard-section {

    margin-bottom: 25px;

}


/* =========================================================
   ROW
========================================================= */

.dashboard-row {

    margin-left: -8px;

    margin-right: -8px;

}

.dashboard-row > [class*="col-"] {

    padding-left: 8px;

    padding-right: 8px;

}


/* =========================================================
   MODERN CARD
========================================================= */

.modern-card {

    background: #fff;

    border: 1px solid #edf0f5;

    border-radius: 16px;

    box-shadow:
        0 3px 15px rgba(
            15,
            23,
            42,
            .04
        );

    overflow: hidden;

}

.modern-card-header {

    min-height: 76px;

    padding: 18px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    border-bottom: 1px solid #f0f2f6;

}

.modern-card-title {

    margin: 0;

    font-size: 16px;

    font-weight: 700;

    color: #1f2937;

}

.modern-card-title i {

    color: #2563eb;

}

.modern-card-subtitle {

    margin: 4px 0 0;

    color: #9ca3af;

    font-size: 12px;

}

.modern-card-body {

    padding: 20px;

}


/* =========================================================
   CHART
========================================================= */

.chart-wrapper {

    min-height: 330px;

    position: relative;

}

.chart-filter select {

    min-width: 110px;

    border-radius: 8px;

    border-color: #e5e7eb;

    font-size: 12px;

}


/* =========================================================
   PARTIAL CARD OVERRIDE
========================================================= */

.dashboard-section .card {

    border: 1px solid #edf0f5 !important;

    border-radius: 16px !important;

    box-shadow:
        0 3px 15px rgba(
            15,
            23,
            42,
            .04
        ) !important;

    overflow: hidden;

}

.dashboard-section .small-box {

    border-radius: 15px !important;

    box-shadow:
        0 4px 15px rgba(
            15,
            23,
            42,
            .06
        ) !important;

    overflow: hidden;

}


/* =========================================================
   TABLE
========================================================= */

.modern-card table {

    margin-bottom: 0;

}

.modern-card table thead th {

    border-top: none;

    background: #f8fafc;

    color: #6b7280;

    font-size: 12px;

    text-transform: uppercase;

    letter-spacing: .3px;

}

.modern-card table tbody td {

    vertical-align: middle;

    border-color: #f1f3f6;

    font-size: 13px;

}


/* =========================================================
   BADGE
========================================================= */

.modern-card .badge {

    border-radius: 20px;

    padding: 5px 9px;

    font-size: 11px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 991px) {

    .quick-actions {

        grid-template-columns:
            repeat(2, 1fr);

    }

}

@media (max-width: 767px) {

    .dashboard-header {

        align-items: flex-start;

        flex-direction: column;

        gap: 15px;

    }

    .header-actions {

        width: 100%;

    }

    .btn-new-sale {

        width: 100%;

    }

    .quick-actions {

        grid-template-columns: 1fr;

    }

    .dashboard-title {

        font-size: 23px;

    }

}

</style>

@stop



{{-- =========================================================
| JAVASCRIPT
========================================================= --}}

@section('js')

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | ANIMASI CARD
        |--------------------------------------------------------------------------
        */

        const cards =
            document.querySelectorAll(
                '.modern-card, .quick-action-item'
            );


        cards.forEach(
            function (card, index) {

                card.style.opacity = '0';

                card.style.transform =
                    'translateY(10px)';


                setTimeout(
                    function () {

                        card.style.transition =
                            'all .35s ease';

                        card.style.opacity = '1';

                        card.style.transform =
                            'translateY(0)';

                    },
                    index * 60
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CHART FILTER
        |--------------------------------------------------------------------------
        */

        const chartPeriod =
            document.getElementById(
                'chartPeriod'
            );


        if (chartPeriod) {

            chartPeriod.addEventListener(
                'change',
                function () {

                    /*
                    | Untuk tahap berikutnya filter ini
                    | akan kita hubungkan ke DashboardController.
                    */

                    console.log(
                        'Periode:',
                        this.value
                    );

                }
            );

        }

    }

);

</script>

@stop