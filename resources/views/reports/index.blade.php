@extends('layouts.app')

@section('title', 'Laporan')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h2 class="fw-bold mb-1">
            Laporan
        </h2>

        <p class="text-muted mb-0">
            Analisis penjualan dan pembelian berdasarkan periode
        </p>

    </div>

</div>

@stop


@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>
        <h4 class="mb-0">
            {{ $reportTitle }}
        </h4>

        <small class="text-muted">
            {{ $periodDescription }}
        </small>
    </div>

    <div class="btn-group">

        {{-- EXPORT EXCEL --}}
        <a href="{{ route('reports.export.excel', request()->query()) }}"
           class="btn btn-success">

            <i class="fas fa-file-excel mr-1"></i>
            Export Excel

        </a>

        {{-- EXPORT PDF --}}
        <a href="{{ route('reports.pdf', request()->query()) }}"
           class="btn btn-danger">

            <i class="fas fa-file-pdf mr-1"></i>
            Export PDF

        </a>

    </div>

</div>
{{-- ========================================================= --}}
{{-- FILTER LAPORAN --}}
{{-- ========================================================= --}}

<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-filter mr-2"></i>

            Filter Laporan

        </h5>

    </div>


    <div class="card-body">

        <form
            action="{{ route('reports.index') }}"
            method="GET"
            id="filterForm"
        >

            <div class="row align-items-end">


                {{-- JENIS LAPORAN --}}

                <div class="col-md-3">

                    <div class="form-group">

                        <label>
                            Jenis Laporan
                        </label>

                        <select
                            name="report_type"
                            id="report_type"
                            class="form-control"
                        >

                            <option
                                value="sales"
                                {{ $reportType === 'sales' ? 'selected' : '' }}
                            >
                                Penjualan
                            </option>

                            <option
                                value="purchases"
                                {{ $reportType === 'purchases' ? 'selected' : '' }}
                            >
                                Pembelian
                            </option>

                        </select>

                    </div>

                </div>


                {{-- PERIODE --}}

                <div class="col-md-3">

                    <div class="form-group">

                        <label>
                            Periode
                        </label>

                        <select
                            name="period"
                            id="period"
                            class="form-control"
                        >

                            <option
                                value="daily"
                                {{ $period === 'daily' ? 'selected' : '' }}
                            >
                                Per Hari
                            </option>

                            <option
                                value="monthly"
                                {{ $period === 'monthly' ? 'selected' : '' }}
                            >
                                Per Bulan
                            </option>

                            <option
                                value="yearly"
                                {{ $period === 'yearly' ? 'selected' : '' }}
                            >
                                Per Tahun
                            </option>

                        </select>

                    </div>

                </div>


                {{-- TANGGAL --}}

                <div class="col-md-3 filter-date">

                    <div class="form-group">

                        <label>
                            Tanggal
                        </label>

                        <input
                            type="date"
                            name="date"
                            class="form-control"
                            value="{{ $selectedDate }}"
                        >

                    </div>

                </div>


                {{-- BULAN --}}

                <div class="col-md-3 filter-month">

                    <div class="form-group">

                        <label>
                            Bulan
                        </label>

                        <input
                            type="month"
                            name="month"
                            class="form-control"
                            value="{{ $selectedMonth }}"
                        >

                    </div>

                </div>


                {{-- TAHUN --}}

                <div class="col-md-3 filter-year">

                    <div class="form-group">

                        <label>
                            Tahun
                        </label>

                        <select
                            name="year"
                            class="form-control"
                        >

                            @for(
                                $year = date('Y');
                                $year >= 2020;
                                $year--
                            )

                                <option
                                    value="{{ $year }}"
                                    {{ (int) $selectedYear === $year ? 'selected' : '' }}
                                >
                                    {{ $year }}
                                </option>

                            @endfor

                        </select>

                    </div>

                </div>


                {{-- BUTTON --}}

               <div class="col-md-3">

    <div class="form-group">

        <button
            type="submit"
            class="btn btn-primary btn-block"
        >
            <i class="fas fa-search mr-1"></i>
            Tampilkan Laporan
        </button>

        

    </div>

</div>
                </div>

            </div>

        </form>

    </div>

</div>


{{-- ========================================================= --}}
{{-- PERIODE AKTIF --}}
{{-- ========================================================= --}}

<div class="alert alert-info">

    <i class="fas fa-calendar-alt mr-2"></i>

    Menampilkan

    <strong>
        {{ $reportTitle }}
    </strong>

    -

    <strong>
        {{ $periodTitle }}
    </strong>

    :

    <strong>
        {{ $periodDescription }}
    </strong>

</div>


{{-- ========================================================= --}}
{{-- STATISTIK PENJUALAN --}}
{{-- ========================================================= --}}

@if($reportType === 'sales')

<div class="row">

    {{-- TRANSAKSI --}}

    <div class="col-md-4">

        <div class="small-box bg-primary">

            <div class="inner">

                <h3>
                    {{ number_format(
                        $totalSalesTransactions,
                        0,
                        ',',
                        '.'
                    ) }}
                </h3>

                <p>
                    Total Transaksi
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-shopping-cart"></i>

            </div>

        </div>

    </div>


    {{-- OMZET --}}

    <div class="col-md-4">

        <div class="small-box bg-success">

            <div class="inner">

                <h3>
                    Rp {{ number_format(
                        $totalSales,
                        0,
                        ',',
                        '.'
                    ) }}
                </h3>

                <p>
                    Total Penjualan
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-money-bill-wave"></i>

            </div>

        </div>

    </div>


    {{-- PRODUK --}}

    <div class="col-md-4">

        <div class="small-box bg-info">

            <div class="inner">

                <h3>
                    {{ number_format(
                        $totalProductsSold,
                        0,
                        ',',
                        '.'
                    ) }}
                </h3>

                <p>
                    Produk Terjual
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-box"></i>

            </div>

        </div>

    </div>

</div>

@endif


{{-- ========================================================= --}}
{{-- STATISTIK PEMBELIAN --}}
{{-- ========================================================= --}}

@if($reportType === 'purchases')

<div class="row">

    {{-- TRANSAKSI --}}

    <div class="col-md-4">

        <div class="small-box bg-primary">

            <div class="inner">

                <h3>
                    {{ number_format(
                        $totalPurchaseTransactions,
                        0,
                        ',',
                        '.'
                    ) }}
                </h3>

                <p>
                    Total Transaksi Pembelian
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-shopping-bag"></i>

            </div>

        </div>

    </div>


    {{-- TOTAL PEMBELIAN --}}

    <div class="col-md-4">

        <div class="small-box bg-danger">

            <div class="inner">

                <h3>
                    Rp {{ number_format(
                        $totalPurchases,
                        0,
                        ',',
                        '.'
                    ) }}
                </h3>

                <p>
                    Total Pembelian
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-money-bill-wave"></i>

            </div>

        </div>

    </div>


    {{-- PRODUK --}}

    <div class="col-md-4">

        <div class="small-box bg-warning">

            <div class="inner">

                <h3>
                    {{ number_format(
                        $totalProductsPurchased,
                        0,
                        ',',
                        '.'
                    ) }}
                </h3>

                <p>
                    Produk Dibeli
                </p>

            </div>

            <div class="icon">

                <i class="fas fa-boxes"></i>

            </div>

        </div>

    </div>

</div>

@endif


{{-- ========================================================= --}}
{{-- GRAFIK --}}
{{-- ========================================================= --}}

<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-chart-line mr-2"></i>

            Grafik
            {{ $reportType === 'sales'
                ? 'Penjualan'
                : 'Pembelian'
            }}

        </h5>

    </div>


    <div class="card-body">

        <div style="height: 350px;">

            <canvas id="reportChart"></canvas>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- PRODUK TERLARIS PENJUALAN --}}
{{-- ========================================================= --}}

@if($reportType === 'sales')

<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-star mr-2"></i>

            Produk Terlaris

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="thead-light">

                    <tr>

                        <th width="70">
                            No
                        </th>

                        <th>
                            Produk
                        </th>

                        <th width="180">
                            Jumlah Terjual
                        </th>

                        <th width="220">
                            Total Penjualan
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($bestSalesProducts as $product)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <strong>
                                    {{ $product->name }}
                                </strong>
                            </td>

                            <td>

                                <span class="badge badge-info">

                                    {{ number_format(
                                        $product->total_quantity,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                    pcs

                                </span>

                            </td>

                            <td>

                                <strong>

                                    Rp {{ number_format(
                                        $product->total_sales,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-4"
                            >

                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>

                                <p class="text-muted mb-0">

                                    Belum ada data produk terjual.

                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endif


{{-- ========================================================= --}}
{{-- PRODUK PEMBELIAN --}}
{{-- ========================================================= --}}

@if($reportType === 'purchases')

<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-boxes mr-2"></i>

            Produk Paling Banyak Dibeli

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="thead-light">

                    <tr>

                        <th width="70">
                            No
                        </th>

                        <th>
                            Produk
                        </th>

                        <th width="180">
                            Jumlah Dibeli
                        </th>

                        <th width="220">
                            Total Pembelian
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($bestPurchaseProducts as $product)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>

                                <strong>
                                    {{ $product->name }}
                                </strong>

                            </td>

                            <td>

                                <span class="badge badge-warning">

                                    {{ number_format(
                                        $product->total_quantity,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                    pcs

                                </span>

                            </td>

                            <td>

                                <strong>

                                    Rp {{ number_format(
                                        $product->total_purchases,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-4"
                            >

                                <i class="fas fa-box-open fa-2x text-muted mb-2"></i>

                                <p class="text-muted mb-0">

                                    Belum ada data pembelian produk.

                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- SUPPLIER TERBANYAK --}}
{{-- ========================================================= --}}

<div class="card">

    <div class="card-header bg-white">

        <h5 class="mb-0">

            <i class="fas fa-truck mr-2"></i>

            Supplier Terbesar

        </h5>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="thead-light">

                    <tr>

                        <th width="70">
                            No
                        </th>

                        <th>
                            Supplier
                        </th>

                        <th width="200">
                            Transaksi
                        </th>

                        <th width="220">
                            Total Pembelian
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($topSuppliers as $supplier)

                        <tr>

                            <td>
                                {{ $loop->iteration }}
                            </td>

                            <td>

                                <strong>
                                    {{ $supplier->name }}
                                </strong>

                            </td>

                            <td>

                                <span class="badge badge-info">

                                    {{ number_format(
                                        $supplier->total_transactions,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                    transaksi

                                </span>

                            </td>

                            <td>

                                <strong>

                                    Rp {{ number_format(
                                        $supplier->total_purchases,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="text-center py-4"
                            >

                                <i class="fas fa-truck-loading fa-2x text-muted mb-2"></i>

                                <p class="text-muted mb-0">

                                    Belum ada data supplier.

                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endif


{{-- ========================================================= --}}
{{-- DAFTAR TRANSAKSI PENJUALAN --}}
{{-- ========================================================= --}}

@if($reportType === 'sales')

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="fas fa-list mr-2"></i>

                Daftar Transaksi Penjualan

            </h5>

            <span class="badge badge-primary">

                {{ $totalSalesTransactions }}

                Transaksi

            </span>

        </div>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="thead-light">

                    <tr>

                        <th width="60">
                            No
                        </th>

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

                        <th width="120">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($sales as $sale)

                        <tr>

                            <td>
                                {{ $sales->firstItem() + $loop->index }}
                            </td>

                            <td>

                                <strong class="text-primary">
                                    {{ $sale->code }}
                                </strong>

                            </td>

                            <td>

                                {{ $sale->sale_date->format('d/m/Y') }}

                            </td>

                            <td>

                                <span class="badge badge-info">

                                    {{ $sale->saleDetails->count() }}

                                    Jenis

                                </span>

                                /

                                {{ $sale->saleDetails->sum('quantity') }}

                                item

                            </td>

                            <td>

                                <strong>

                                    Rp {{ number_format(
                                        $sale->total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>

                            <td>

                                <a
                                    href="{{ route(
                                        'sales.show',
                                        $sale->id
                                    ) }}"
                                    class="btn btn-sm btn-info"
                                    title="Detail"
                                >

                                    <i class="fas fa-eye"></i>

                                </a>

                                <a
                                    href="{{ route(
                                        'sales.receipt',
                                        $sale->id
                                    ) }}"
                                    class="btn btn-sm btn-success"
                                    title="Struk"
                                >

                                    <i class="fas fa-receipt"></i>

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="text-center py-5"
                            >

                                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>

                                <h5 class="text-muted">

                                    Tidak ada transaksi penjualan

                                </h5>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="d-flex justify-content-end mt-3">

            {{ $sales->links() }}

        </div>

    </div>

</div>

@endif


{{-- ========================================================= --}}
{{-- DAFTAR TRANSAKSI PEMBELIAN --}}
{{-- ========================================================= --}}

@if($reportType === 'purchases')

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0">

                <i class="fas fa-list mr-2"></i>

                Daftar Transaksi Pembelian

            </h5>

            <span class="badge badge-danger">

                {{ $totalPurchaseTransactions }}

                Transaksi

            </span>

        </div>

    </div>


    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="thead-light">

                    <tr>

                        <th width="60">
                            No
                        </th>

                        <th>
                            Kode
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>
                            Supplier
                        </th>

                        <th>
                            Produk
                        </th>

                        <th>
                            Total
                        </th>

                        <th width="100">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($purchases as $purchase)

                        <tr>

                            <td>
                                {{ $purchases->firstItem() + $loop->index }}
                            </td>

                            <td>

                                <strong class="text-primary">

                                    {{ $purchase->code }}

                                </strong>

                            </td>

                            <td>

                                {{ \Carbon\Carbon::parse(
                                    $purchase->purchase_date
                                )->format('d/m/Y') }}

                            </td>

                            <td>

                                <span class="badge badge-secondary">

                                    {{ $purchase->supplier->name ?? '-' }}

                                </span>

                            </td>

                            <td>

                                <span class="badge badge-info">

                                    {{ $purchase->details->count() }}

                                    Jenis

                                </span>

                                /

                                {{ $purchase->details->sum('quantity') }}

                                item

                            </td>

                            <td>

                                <strong>

                                    Rp {{ number_format(
                                        $purchase->total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}

                                </strong>

                            </td>

                            <td>

                                <a
                                    href="{{ route(
                                        'purchases.show',
                                        $purchase->id
                                    ) }}"
                                    class="btn btn-sm btn-info"
                                    title="Detail"
                                >

                                    <i class="fas fa-eye"></i>

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>

                                <h5 class="text-muted">

                                    Tidak ada transaksi pembelian

                                </h5>

                                <p class="text-muted mb-0">

                                    Tidak ditemukan pembelian pada periode yang dipilih.

                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        <div class="d-flex justify-content-end mt-3">

            {{ $purchases->links() }}

        </div>

    </div>

</div>

@endif


@stop


@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
        |--------------------------------------------------------------------------
        | FILTER PERIODE
        |--------------------------------------------------------------------------
        */

        const period =
            document.getElementById('period');

        const dateFilter =
            document.querySelector('.filter-date');

        const monthFilter =
            document.querySelector('.filter-month');

        const yearFilter =
            document.querySelector('.filter-year');


        function updateFilter()
        {
            dateFilter.style.display = 'none';

            monthFilter.style.display = 'none';

            yearFilter.style.display = 'none';


            if (period.value === 'daily') {

                dateFilter.style.display = 'block';

            }

            else if (period.value === 'monthly') {

                monthFilter.style.display = 'block';

            }

            else if (period.value === 'yearly') {

                yearFilter.style.display = 'block';

            }
        }


        period.addEventListener(
            'change',
            updateFilter
        );


        updateFilter();


        /*
        |--------------------------------------------------------------------------
        | DATA GRAFIK
        |--------------------------------------------------------------------------
        */

        const labels =
            @json($chartLabels);

        const salesValues =
            @json($chartSalesValues);

        const purchaseValues =
            @json($chartPurchaseValues);


        /*
        |--------------------------------------------------------------------------
        | JENIS LAPORAN
        |--------------------------------------------------------------------------
        */

        const reportType =
            @json($reportType);


        /*
        |--------------------------------------------------------------------------
        | CANVAS
        |--------------------------------------------------------------------------
        */

        const canvas =
            document.getElementById('reportChart');


        if (canvas) {

            const ctx =
                canvas.getContext('2d');


            /*
            |--------------------------------------------------------------------------
            | DATASET
            |--------------------------------------------------------------------------
            */

            let datasets = [];


            if (reportType === 'sales') {

                datasets = [

                    {
                        label: 'Penjualan',

                        data: salesValues,

                        fill: true,

                        tension: 0.3,

                        borderWidth: 2
                    }

                ];

            } else {

                datasets = [

                    {
                        label: 'Pembelian',

                        data: purchaseValues,

                        fill: true,

                        tension: 0.3,

                        borderWidth: 2
                    }

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | CHART
            |--------------------------------------------------------------------------
            */

            new Chart(
                ctx,
                {

                    type: 'line',

                    data: {

                        labels: labels,

                        datasets: datasets

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

                                display: true

                            },

                            tooltip: {

                                callbacks: {

                                    label:
                                        function(context)
                                        {

                                            return context.dataset.label
                                                + ': Rp '
                                                + new Intl.NumberFormat(
                                                    'id-ID'
                                                ).format(
                                                    context.raw
                                                );

                                        }

                                }

                            }

                        },

                        scales: {

                            y: {

                                beginAtZero: true,

                                ticks: {

                                    callback:
                                        function(value)
                                        {

                                            return 'Rp '
                                                + new Intl.NumberFormat(
                                                    'id-ID'
                                                ).format(
                                                    value
                                                );

                                        }

                                }

                            }

                        }

                    }

                }
            );

        }

    }

);

</script>

@stop
