@extends('layouts.app')

@section('title', 'Dashboard POS Enterprise')

{{-- =========================================================
    HEADER
========================================================= --}}

@section('content_header')

<div class="dashboard-header">

    <div>

        <div class="dashboard-eyebrow">
            <i class="fas fa-chart-line"></i>
            POS ENTERPRISE
        </div>

        <h1 class="dashboard-title">
            Dashboard
        </h1>

        <p class="dashboard-subtitle">
            Pantau bisnis, penjualan, dan stok Anda dalam satu tempat.
        </p>

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
    CONTENT
========================================================= --}}

@section('content')

<div class="dashboard-container">

    {{-- =====================================================
        QUICK ACTION
    ====================================================== --}}

    <div class="quick-actions">

        {{-- KASIR --}}
        <a
            href="{{ route('sales.create') }}"
            class="quick-card"
        >

            <div class="quick-icon orange">
                <i class="fas fa-cash-register"></i>
            </div>

            <div class="quick-content">

                <span class="quick-title">
                    Kasir
                </span>

                <span class="quick-description">
                    Buat transaksi baru
                </span>

            </div>

            <i class="fas fa-arrow-right quick-arrow"></i>

        </a>


        {{-- PRODUK --}}
        <a
            href="{{ route('products.index') }}"
            class="quick-card"
        >

            <div class="quick-icon orange">
                <i class="fas fa-box"></i>
            </div>

            <div class="quick-content">

                <span class="quick-title">
                    Produk
                </span>

                <span class="quick-description">
                    Kelola inventori produk
                </span>

            </div>

            <i class="fas fa-arrow-right quick-arrow"></i>

        </a>


        {{-- PENJUALAN --}}
        <a
            href="{{ route('sales.index') }}"
            class="quick-card"
        >

            <div class="quick-icon green">
                <i class="fas fa-receipt"></i>
            </div>

            <div class="quick-content">

                <span class="quick-title">
                    Penjualan
                </span>

                <span class="quick-description">
                    Lihat semua transaksi
                </span>

            </div>

            <i class="fas fa-arrow-right quick-arrow"></i>

        </a>


        {{-- LAPORAN --}}
        <a
            href="{{ route('reports.index') }}"
            class="quick-card"
        >

            <div class="quick-icon purple">
                <i class="fas fa-chart-pie"></i>
            </div>

            <div class="quick-content">

                <span class="quick-title">
                    Laporan
                </span>

                <span class="quick-description">
                    Analisis bisnis
                </span>

            </div>

            <i class="fas fa-arrow-right quick-arrow"></i>

        </a>

    </div>


    {{-- =====================================================
        STATISTIC CARDS
    ====================================================== --}}

    <div class="row stats-row">

        {{-- PRODUK --}}
        <div class="col-xl-3 col-md-6">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon orange">
                        <i class="fas fa-box"></i>
                    </div>

                    <span class="stat-label">
                        TOTAL PRODUK
                    </span>

                </div>

                <div class="stat-value">

                    {{ number_format($totalProducts, 0, ',', '.') }}

                </div>

                <div class="stat-footer">

                    <span>
                        Produk terdaftar
                    </span>

                    <a href="{{ route('products.index') }}">
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>


        {{-- KATEGORI --}}
        <div class="col-xl-3 col-md-6">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon purple">
                        <i class="fas fa-tags"></i>
                    </div>

                    <span class="stat-label">
                        KATEGORI
                    </span>

                </div>

                <div class="stat-value">

                    {{ number_format($totalCategories, 0, ',', '.') }}

                </div>

                <div class="stat-footer">

                    <span>
                        Kategori produk
                    </span>

                    <a href="{{ route('categories.index') }}">
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>


        @businessModule('suppliers')
        {{-- SUPPLIER --}}
        <div class="col-xl-3 col-md-6">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon orange">
                        <i class="fas fa-truck"></i>
                    </div>

                    <span class="stat-label">
                        SUPPLIER
                    </span>

                </div>

                <div class="stat-value">

                    {{ number_format($totalSuppliers, 0, ',', '.') }}

                </div>

                <div class="stat-footer">

                    <span>
                        Supplier terdaftar
                    </span>

                    <a href="{{ route('suppliers.index') }}">
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>


        @endbusinessModule
        {{-- TRANSAKSI --}}
        <div class="col-xl-3 col-md-6">

            <div class="stat-card">

                <div class="stat-card-top">

                    <div class="stat-icon green">
                        <i class="fas fa-shopping-cart"></i>
                    </div>

                    <span class="stat-label">
                        TRANSAKSI
                    </span>

                </div>

                <div class="stat-value">

                    {{ number_format($totalTransactions, 0, ',', '.') }}

                </div>

                <div class="stat-footer">

                    <span>
                        Total transaksi
                    </span>

                    <a href="{{ route('sales.index') }}">
                        <i class="fas fa-arrow-right"></i>
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
        REVENUE SUMMARY
    ====================================================== --}}

    <div class="row">

        {{-- CHART --}}
        <div class="col-lg-8 mb-4">

            <div class="modern-card chart-card">

                <div class="card-header-modern">

                    <div>

                        <h3>
                            <i class="fas fa-chart-area"></i>
                            Performa Penjualan
                        </h3>

                        <p>
                            Pendapatan 7 hari terakhir
                        </p>

                    </div>

                    <div class="revenue-summary">

                        <span>
                            Total Pendapatan
                        </span>

                        <strong>
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </strong>

                    </div>

                </div>

                <div class="chart-container">

                    <canvas id="salesChart"></canvas>

                </div>

            </div>

        </div>


        {{-- PENJUALAN HARI INI --}}
        <div class="col-lg-4 mb-4">

            <div class="modern-card today-card">

                <div class="today-header">

                    <div>

                        <span class="today-label">
                            PENJUALAN HARI INI
                        </span>

                        <h3>
                            Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                        </h3>

                    </div>

                    <div class="today-icon">

                        <i class="fas fa-wallet"></i>

                    </div>

                </div>


                <div class="today-divider"></div>


                <div class="today-stat">

                    <div>

                        <span>
                            Transaksi
                        </span>

                        <strong>
                            {{ number_format($todayTransactions, 0, ',', '.') }}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Produk terjual
                        </span>

                        <strong>
                            {{ number_format($totalProductsSold, 0, ',', '.') }}
                        </strong>

                    </div>

                </div>


                <a
                    href="{{ route('sales.create') }}"
                    class="today-button"
                >

                    <i class="fas fa-plus"></i>

                    Buat Transaksi

                </a>

            </div>

        </div>

    </div>


    {{-- =====================================================
        BEST PRODUCT + STOCK
    ====================================================== --}}

    <div class="row">

        {{-- PRODUK TERLARIS --}}
        <div class="col-lg-6 mb-4">

            <div class="modern-card">

                <div class="card-header-modern">

                    <div>

                        <h3>
                            <i class="fas fa-fire"></i>
                            Produk Terlaris
                        </h3>

                        <p>
                            Produk dengan penjualan tertinggi
                        </p>

                    </div>

                    <a
                        href="{{ route('products.index') }}"
                        class="view-all"
                    >
                        Lihat produk
                    </a>

                </div>


                <div class="product-list">

                    @forelse($bestProducts as $index => $product)

                        <div class="product-item">

                            <div class="product-rank">
                                {{ $index + 1 }}
                            </div>

                            <div class="product-avatar">
                                <i class="fas fa-box"></i>
                            </div>

                            <div class="product-info">

                                <strong>
                                    {{ $product->name }}
                                </strong>

                                <span>
                                    {{ number_format($product->total_sold, 0, ',', '.') }}
                                    terjual
                                </span>

                            </div>

                            <div class="product-revenue">

                                Rp
                                {{ number_format($product->total_revenue, 0, ',', '.') }}

                            </div>

                        </div>

                    @empty

                        <div class="empty-state">

                            <i class="fas fa-box-open"></i>

                            <p>
                                Belum ada data penjualan.
                            </p>

                        </div>

                    @endforelse

                </div>

            </div>

        </div>


        {{-- STOK --}}
        <div class="col-lg-6 mb-4">

            <div class="modern-card">

                <div class="card-header-modern">

                    <div>

                        <h3>
                            <i class="fas fa-boxes"></i>
                            Monitoring Stok
                        </h3>

                        <p>
                            Kondisi stok produk
                        </p>

                    </div>

                    <a
                        href="{{ route('products.index') }}"
                        class="view-all"
                    >
                        Kelola stok
                    </a>

                </div>


                <div class="stock-overview">

                    <div class="stock-box available">

                        <div class="stock-box-icon">
                            <i class="fas fa-check"></i>
                        </div>

                        <div>

                            <strong>
                                {{ number_format($availableStock, 0, ',', '.') }}
                            </strong>

                            <span>
                                Unit tersedia
                            </span>

                        </div>

                    </div>


                    <div class="stock-box warning">

                        <div class="stock-box-icon">
                            <i class="fas fa-exclamation"></i>
                        </div>

                        <div>

                            <strong>
                                {{ $lowStock }}
                            </strong>

                            <span>
                                Produk menipis
                            </span>

                        </div>

                    </div>


                    <div class="stock-box danger">

                        <div class="stock-box-icon">
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


                @if($lowStockProducts->count() > 0)

                    <div class="low-stock-list">

                        <div class="low-stock-title">

                            <i class="fas fa-exclamation-triangle"></i>

                            Perlu perhatian

                        </div>


                        @foreach($lowStockProducts as $product)

                            <div class="low-stock-item">

                                <span>
                                    {{ $product->name }}
                                </span>


                                @if($product->stock <= 0)

                                    <span class="stock-badge danger">
                                        Habis
                                    </span>

                                @else

                                    <span class="stock-badge warning">
                                        {{ $product->stock }} tersisa
                                    </span>

                                @endif

                            </div>

                        @endforeach

                    </div>

                @else

                    <div class="stock-safe">

                        <i class="fas fa-check-circle"></i>

                        Semua stok dalam kondisi aman.

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- =====================================================
        TRANSAKSI TERBARU
    ====================================================== --}}

    <div class="modern-card mb-4">

        <div class="card-header-modern">

            <div>

                <h3>
                    <i class="fas fa-history"></i>
                    Transaksi Terbaru
                </h3>

                <p>
                    Aktivitas penjualan terakhir
                </p>

            </div>

            <a
                href="{{ route('sales.index') }}"
                class="view-all"
            >
                Lihat semua
                <i class="fas fa-arrow-right ml-1"></i>
            </a>

        </div>


        <div class="table-responsive">

            <table class="modern-table">

                <thead>

                    <tr>

                        <th>
                            KODE
                        </th>

                        <th>
                            TANGGAL
                        </th>

                        <th>
                            ITEM
                        </th>

                        <th>
                            TOTAL
                        </th>

                        <th>
                            STATUS
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

                                <div class="date-cell">

                                    <strong>
                                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
                                    </strong>

                                </div>

                            </td>


                            <td>

                                {{ $sale->saleDetails->sum('quantity') }}
                                item

                            </td>


                            <td>

                                <strong class="table-total">

                                    Rp
                                    {{ number_format($sale->total, 0, ',', '.') }}

                                </strong>

                            </td>


                            <td>

                                <span class="paid-badge">

                                    <i class="fas fa-check-circle"></i>

                                    Lunas

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="empty-table"
                            >

                                <i class="fas fa-receipt"></i>

                                <span>
                                    Belum ada transaksi.
                                </span>

                                <a
                                    href="{{ route('sales.create') }}"
                                    class="btn btn-primary btn-sm"
                                >
                                    Buat transaksi pertama
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@stop


{{-- =========================================================
    CSS
========================================================= --}}

@section('css')

<style>

/* =========================================================
   GLOBAL DASHBOARD
========================================================= */

body {
    background: #f4f3fb !important;
}

.content-wrapper {
    background: #f4f3fb !important;
}

.content {
    background: #f4f3fb !important;
}

.dashboard-container {
    padding-bottom: 40px;
}


/* =========================================================
   HEADER
========================================================= */

.dashboard-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 26px;
}

.dashboard-eyebrow {
    color: #ff7518;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    margin-bottom: 7px;
}

.dashboard-eyebrow i {
    margin-right: 5px;
}

.dashboard-title {
    margin: 0;
    color: #111827;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.dashboard-subtitle {
    color: #64748b;
    margin: 6px 0 0;
    font-size: 14px;
}


/* =========================================================
   PRIMARY BUTTON
========================================================= */

.btn-primary {
    background: linear-gradient(
        135deg,
        #ff7a18,
        #ff8c2a
    ) !important;

    border-color: #ff7a18 !important;

    color: #ffffff !important;

    box-shadow:
        0 8px 20px rgba(255, 117, 24, 0.20);
}

.btn-primary:hover {
    background: linear-gradient(
        135deg,
        #f56b08,
        #ff7a18
    ) !important;

    border-color: #f56b08 !important;

    transform: translateY(-1px);
}

.btn-new-sale {
    border: none;
    border-radius: 11px;
    padding: 11px 20px;
    font-weight: 700;
    transition: all .2s ease;
}


/* =========================================================
   QUICK ACTION
========================================================= */

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
    margin-bottom: 22px;
}

.quick-card {
    background: #ffffff;

    border: 1px solid #e6e3ee;

    border-radius: 15px;

    padding: 15px;

    display: flex;
    align-items: center;

    gap: 12px;

    color: #1f2937;

    text-decoration: none !important;

    transition: all .2s ease;
}

.quick-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(30, 27, 75, .07);

    border-color: #ddd8e8;

    color: #1f2937;
}

.quick-icon {
    width: 43px;
    height: 43px;

    min-width: 43px;

    border-radius: 12px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 15px;
}

.quick-icon.orange {
    background: #fff1e7;
    color: #ff7518;
}

.quick-icon.green {
    background: #eafaf1;
    color: #16a34a;
}

.quick-icon.purple {
    background: #f1edff;
    color: #7357f5;
}

.quick-content {
    min-width: 0;
}

.quick-title {
    display: block;

    font-weight: 700;

    font-size: 13px;

    color: #1f2937;
}

.quick-description {
    display: block;

    color: #94a3b8;

    font-size: 11px;

    margin-top: 2px;
}

.quick-arrow {
    margin-left: auto;

    color: #cbd5e1;

    font-size: 11px;
}


/* =========================================================
   STATISTICS
========================================================= */

.stats-row {
    margin-left: -7px;
    margin-right: -7px;
}

.stats-row > div {
    padding-left: 7px;
    padding-right: 7px;
    margin-bottom: 14px;
}

.stat-card {
    background: #ffffff;

    border: 1px solid #e6e3ee;

    border-radius: 15px;

    padding: 18px;

    transition: all .2s ease;

    box-shadow:
        0 3px 12px rgba(30, 27, 75, .025);
}

.stat-card:hover {
    transform: translateY(-2px);

    box-shadow:
        0 10px 25px rgba(30, 27, 75, .07);
}

.stat-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stat-icon {
    width: 40px;
    height: 40px;

    border-radius: 11px;

    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-icon.orange {
    background: #fff1e7;
    color: #ff7518;
}

.stat-icon.green {
    background: #eafaf1;
    color: #16a34a;
}

.stat-icon.purple {
    background: #f1edff;
    color: #7357f5;
}

.stat-label {
    font-size: 10px;

    font-weight: 800;

    color: #9ca3af;

    letter-spacing: .6px;
}

.stat-value {
    color: #111827;

    font-size: 27px;

    font-weight: 800;

    margin-top: 13px;
}

.stat-footer {
    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-top: 7px;

    color: #9ca3af;

    font-size: 11px;
}

.stat-footer a {
    color: #ff7518;

    transition: .2s ease;
}

.stat-footer a:hover {
    color: #ea5d00;
}


/* =========================================================
   MODERN CARD
========================================================= */

.modern-card {
    background: #ffffff;

    border: 1px solid #e6e3ee;

    border-radius: 17px;

    overflow: hidden;

    box-shadow:
        0 4px 15px rgba(30, 27, 75, .035);
}

.card-header-modern {
    min-height: 75px;

    padding: 18px 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    border-bottom: 1px solid #eeeaf3;
}

.card-header-modern h3 {
    margin: 0;

    color: #111827;

    font-size: 15px;

    font-weight: 750;
}

.card-header-modern h3 i {
    color: #ff7518;

    margin-right: 7px;
}

.card-header-modern p {
    margin: 4px 0 0;

    color: #9ca3af;

    font-size: 11px;
}

.view-all {
    color: #ff7518;

    font-size: 11px;

    font-weight: 700;

    text-decoration: none !important;
}

.view-all:hover {
    color: #ea5d00;
}


/* =========================================================
   CHART
========================================================= */

.chart-card {
    min-height: 400px;
}

.revenue-summary {
    text-align: right;
}

.revenue-summary span {
    display: block;

    color: #9ca3af;

    font-size: 10px;
}

.revenue-summary strong {
    display: block;

    color: #111827;

    font-size: 16px;

    margin-top: 3px;
}

.chart-container {
    height: 300px;

    padding: 15px 20px 20px;
}


/* =========================================================
   TODAY SALES
========================================================= */

.today-card {
    padding: 22px;

    height: 100%;
}

.today-header {
    display: flex;

    justify-content: space-between;

    align-items: center;
}

.today-label {
    color: #9ca3af;

    font-size: 10px;

    font-weight: 800;

    letter-spacing: .6px;
}

.today-header h3 {
    margin: 7px 0 0;

    color: #111827;

    font-size: 22px;

    font-weight: 800;
}

.today-icon {
    width: 48px;
    height: 48px;

    border-radius: 13px;

    background: #fff1e7;

    color: #ff7518;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;
}

.today-divider {
    height: 1px;

    background: #eeeaf3;

    margin: 22px 0;
}

.today-stat {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;
}

.today-stat span {
    display: block;

    color: #9ca3af;

    font-size: 11px;
}

.today-stat strong {
    display: block;

    color: #111827;

    font-size: 18px;

    margin-top: 5px;
}

.today-button {
    display: block;

    text-align: center;

    background: linear-gradient(
        135deg,
        #ff7518,
        #ff8c2a
    );

    color: #ffffff;

    border-radius: 10px;

    padding: 10px;

    margin-top: 22px;

    font-size: 12px;

    font-weight: 700;

    text-decoration: none !important;

    transition: all .2s ease;
}

.today-button:hover {
    color: #ffffff;

    transform: translateY(-1px);

    box-shadow:
        0 7px 18px rgba(255, 117, 24, .20);
}


/* =========================================================
   PRODUCT
========================================================= */

.product-list {
    padding: 5px 20px;
}

.product-item {
    display: flex;

    align-items: center;

    padding: 13px 0;

    border-bottom: 1px solid #f0edf5;
}

.product-item:last-child {
    border-bottom: none;
}

.product-rank {
    width: 24px;

    color: #a1a1aa;

    font-size: 12px;

    font-weight: 800;
}

.product-avatar {
    width: 37px;
    height: 37px;

    border-radius: 10px;

    background: #fff1e7;

    color: #ff7518;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-right: 11px;
}

.product-info {
    flex: 1;
}

.product-info strong {
    display: block;

    color: #374151;

    font-size: 12px;
}

.product-info span {
    display: block;

    color: #9ca3af;

    font-size: 10px;

    margin-top: 3px;
}

.product-revenue {
    color: #111827;

    font-size: 11px;

    font-weight: 800;
}


/* =========================================================
   STOCK
========================================================= */

.stock-overview {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 10px;

    padding: 18px 20px 10px;
}

.stock-box {
    border-radius: 11px;

    padding: 12px;

    display: flex;

    align-items: center;

    gap: 9px;
}

.stock-box.available {
    background: #ecfdf5;
}

.stock-box.warning {
    background: #fff8e7;
}

.stock-box.danger {
    background: #fff0f0;
}

.stock-box-icon {
    width: 29px;
    height: 29px;

    border-radius: 8px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 11px;
}

.available .stock-box-icon {
    background: #d1fae5;

    color: #059669;
}

.warning .stock-box-icon {
    background: #ffedbd;

    color: #d97706;
}

.danger .stock-box-icon {
    background: #fee2e2;

    color: #dc2626;
}

.stock-box strong {
    display: block;

    font-size: 16px;

    color: #111827;
}

.stock-box span {
    display: block;

    color: #9ca3af;

    font-size: 9px;

    margin-top: 2px;
}

.low-stock-list {
    margin: 8px 20px 18px;

    border-top: 1px solid #eeeaf3;
}

.low-stock-title {
    padding: 12px 0 8px;

    color: #b45309;

    font-size: 11px;

    font-weight: 800;
}

.low-stock-title i {
    margin-right: 5px;
}

.low-stock-item {
    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 7px 0;

    font-size: 11px;

    border-bottom: 1px solid #f5f3f8;
}

.stock-badge {
    border-radius: 20px;

    padding: 4px 8px;

    font-size: 9px;

    font-weight: 700;
}

.stock-badge.warning {
    background: #fff1e7;

    color: #c2410c;
}

.stock-badge.danger {
    background: #fff0f0;

    color: #dc2626;
}

.stock-safe {
    margin: 10px 20px 20px;

    background: #ecfdf5;

    color: #047857;

    padding: 12px;

    border-radius: 9px;

    font-size: 11px;
}


/* =========================================================
   TABLE
========================================================= */

.modern-table {
    width: 100%;

    border-collapse: collapse;
}

.modern-table thead th {
    background: #faf9fc;

    color: #9ca3af;

    font-size: 9px;

    font-weight: 800;

    letter-spacing: .7px;

    padding: 12px 20px;

    border: none;
}

.modern-table tbody td {
    padding: 14px 20px;

    border-top: 1px solid #f0edf5;

    color: #64748b;

    font-size: 11px;

    vertical-align: middle;
}

.modern-table tbody tr:hover {
    background: #fffaf6;
}

.sale-code {
    color: #ff7518;

    font-weight: 800;

    text-decoration: none !important;
}

.sale-code:hover {
    color: #ea5d00;
}

.date-cell strong {
    color: #374151;

    font-weight: 600;
}

.table-total {
    color: #111827;
}

.paid-badge {
    display: inline-flex;

    align-items: center;

    gap: 5px;

    background: #ecfdf5;

    color: #059669;

    border-radius: 20px;

    padding: 5px 9px;

    font-size: 9px;

    font-weight: 800;
}

.empty-table {
    text-align: center;

    padding: 40px !important;
}

.empty-table i {
    display: block;

    color: #cbd5e1;

    font-size: 25px;

    margin-bottom: 8px;
}

.empty-table span {
    display: block;

    color: #9ca3af;

    margin-bottom: 12px;
}

.empty-state {
    text-align: center;

    padding: 40px 20px;

    color: #9ca3af;
}

.empty-state i {
    font-size: 30px;

    display: block;

    margin-bottom: 10px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 767px) {

    .dashboard-header {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

    .header-actions,
    .btn-new-sale {
        width: 100%;
    }

    .quick-actions {
        grid-template-columns: 1fr;
    }

    .dashboard-title {
        font-size: 25px;
    }

    .stock-overview {
        grid-template-columns: 1fr;
    }

    .card-header-modern {
        align-items: flex-start;

        gap: 10px;
    }

    .revenue-summary {
        display: none;
    }

}

</style>

@stop


{{-- =========================================================
    JAVASCRIPT
========================================================= --}}

@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const canvas =
            document.getElementById('salesChart');

        if (!canvas) {
            return;
        }


        const labels =
            @json($salesChartLabels);

        const data =
            @json($salesChartData);


        new Chart(
            canvas,
            {

                type: 'line',

                data: {

                    labels: labels,

                    datasets: [

                        {

                            label: 'Penjualan',

                            data: data,

                            borderColor: '#ff7518',

                            backgroundColor:
                                'rgba(255, 117, 24, .10)',

                            borderWidth: 3,

                            fill: true,

                            tension: .4,

                            pointRadius: 4,

                            pointHoverRadius: 6,

                            pointBackgroundColor:
                                '#ffffff',

                            pointBorderColor:
                                '#ff7518',

                            pointBorderWidth: 3

                        }

                    ]

                },


                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    interaction: {

                        intersect: false,

                        mode: 'index'

                    },


                    plugins: {

                        legend: {

                            display: false

                        },


                        tooltip: {

                            backgroundColor: '#171717',

                            padding: 12,

                            displayColors: false,

                            callbacks: {

                                label:
                                    function (context) {

                                        return 'Rp ' +
                                            new Intl.NumberFormat(
                                                'id-ID'
                                            ).format(
                                                context.raw
                                            );

                                    }

                            }

                        }

                    },


                    scales: {

                        x: {

                            grid: {

                                display: false

                            },

                            border: {

                                display: false

                            },

                            ticks: {

                                color: '#8b8b95',

                                font: {

                                    size: 10

                                }

                            }

                        },


                        y: {

                            beginAtZero: true,

                            border: {

                                display: false

                            },

                            grid: {

                                color:
                                    'rgba(148, 163, 184, .12)'

                            },

                            ticks: {

                                color: '#8b8b95',

                                font: {

                                    size: 10

                                },

                                callback:
                                    function (value) {

                                        if (value >= 1000000) {

                                            return 'Rp ' +
                                                (
                                                    value / 1000000
                                                ).toFixed(1)
                                                + ' jt';

                                        }


                                        if (value >= 1000) {

                                            return 'Rp ' +
                                                (
                                                    value / 1000
                                                ).toFixed(0)
                                                + ' rb';

                                        }


                                        return 'Rp ' + value;

                                    }

                            }

                        }

                    }

                }

            }
        );


        /* =====================================================
           ANIMASI
        ====================================================== */

        const elements =
            document.querySelectorAll(
                '.stat-card, .quick-card, .modern-card'
            );


        elements.forEach(
            function (element, index) {

                element.style.opacity = '0';

                element.style.transform =
                    'translateY(10px)';


                setTimeout(
                    function () {

                        element.style.transition =
                            'all .35s ease';

                        element.style.opacity = '1';

                        element.style.transform =
                            'translateY(0)';

                    },
                    index * 50
                );

            }
        );

    }

);

</script>

@stop