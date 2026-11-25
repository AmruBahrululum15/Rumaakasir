@extends('layouts.app')

@section('title', 'Laporan')

@section('content_header')
<div class="reports-heading">
    <span class="reports-eyebrow">ANALISIS BISNIS</span>
    <h2>Laporan</h2>
    <p>Analisis penjualan dan pembelian berdasarkan periode.</p>
</div>
@stop

@section('content')
<link rel="stylesheet" href="{{ asset('css/reports.css') }}">
<div class="reports-page">
    <div class="reports-toolbar">
        <div><h3>{{ $reportTitle }}</h3><span class="reports-muted">{{ $periodDescription }}</span></div>
        <div class="reports-exports">
            <a href="{{ route('reports.export.excel', request()->query()) }}" class="reports-export reports-excel"><i class="fas fa-file-excel" aria-hidden="true"></i> Export Excel</a>
            <a href="{{ route('reports.pdf', request()->query()) }}" class="reports-export reports-pdf"><i class="fas fa-file-pdf" aria-hidden="true"></i> Export PDF</a>
        </div>
    </div>
    <section class="card reports-filter">
        <div class="card-header"><h5><i class="fas fa-sliders-h" aria-hidden="true"></i> Filter laporan</h5><p>Tentukan jenis dan periode laporan yang ingin Anda lihat.</p></div>
        <div class="card-body">
            <form action="{{ route('reports.index') }}" method="GET" id="filterForm" class="reports-filter-grid">
                <div><label for="report_type">Jenis laporan</label><select name="report_type" id="report_type" class="form-control">
                    <option value="sales" {{ $reportType === 'sales' ? 'selected' : '' }}>Penjualan</option>
                    <option value="purchases" {{ $reportType === 'purchases' ? 'selected' : '' }}>Pembelian</option>
                </select></div>
                <div><label for="period">Periode</label><select name="period" id="period" class="form-control">
                    <option value="daily" {{ $period === 'daily' ? 'selected' : '' }}>Per Hari</option>
                    <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Per Bulan</option>
                    <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Per Tahun</option>
                </select></div>
                <div class="filter-date" style="display: {{ $period === 'daily' ? 'block' : 'none' }}"><label for="report-date">Tanggal</label><input type="date" id="report-date" name="date" class="form-control" value="{{ $selectedDate }}"></div>
                <div class="filter-month" style="display: {{ $period === 'monthly' ? 'block' : 'none' }}"><label for="report-month">Bulan</label><input type="month" id="report-month" name="month" class="form-control" value="{{ $selectedMonth }}"></div>
                <div class="filter-year" style="display: {{ $period === 'yearly' ? 'block' : 'none' }}"><label for="report-year">Tahun</label><select id="report-year" name="year" class="form-control">
                    @for($year = date('Y'); $year >= 2020; $year--)
                        <option value="{{ $year }}" {{ (int) $selectedYear === $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select></div>
                <button type="submit" class="reports-submit"><i class="fas fa-search" aria-hidden="true"></i> Tampilkan Laporan</button>
            </form>
        </div>
    </section>
    <div class="reports-period"><span><i class="far fa-calendar-alt" aria-hidden="true"></i> {{ $periodTitle }} <strong>{{ $periodDescription }}</strong></span><span>{{ $reportTitle }}</span></div>
    <div class="reports-stats">
        <article class="reports-stat">
            <div class="reports-stat-top"><span>Total transaksi{{ $reportType === 'purchases' ? ' pembelian' : '' }}</span><span class="reports-stat-icon"><i class="fas fa-receipt" aria-hidden="true"></i></span></div>
            <strong>{{ number_format($reportType === 'sales' ? $totalSalesTransactions : $totalPurchaseTransactions, 0, ',', '.') }}</strong>
            <small>Transaksi pada periode terpilih</small>
        </article>
        <article class="reports-stat reports-stat-revenue">
            <div class="reports-stat-top"><span>Total {{ $reportType === 'sales' ? 'penjualan' : 'pembelian' }}</span><span class="reports-stat-icon"><i class="fas fa-wallet" aria-hidden="true"></i></span></div>
            <strong>Rp {{ number_format($reportType === 'sales' ? $totalSales : $totalPurchases, 0, ',', '.') }}</strong>
            <small>Nilai {{ $reportType === 'sales' ? 'penjualan' : 'pembelian' }} pada periode terpilih</small>
        </article>
        <article class="reports-stat">
            <div class="reports-stat-top"><span>Produk {{ $reportType === 'sales' ? 'terjual' : 'dibeli' }}</span><span class="reports-stat-icon reports-stat-icon-neutral"><i class="fas fa-box" aria-hidden="true"></i></span></div>
            <strong>{{ number_format($reportType === 'sales' ? $totalProductsSold : $totalProductsPurchased, 0, ',', '.') }}</strong>
            <small>Total unit produk</small>
        </article>
    </div>
    <section class="card reports-chart">
        <div class="card-header"><h5><i class="fas fa-chart-line" aria-hidden="true"></i> Grafik {{ $reportType === 'sales' ? 'Penjualan' : 'Pembelian' }}</h5><p>Pergerakan nilai transaksi selama periode terpilih.</p></div>
        <div class="card-body"><div class="reports-chart-canvas"><canvas id="reportChart" role="img" aria-label="Grafik nilai transaksi {{ $periodDescription }}"></canvas></div></div>
    </section>

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


</div>
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

                        borderWidth: 2,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.08)',
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f97316',
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }

                ];

            } else {

                datasets = [

                    {
                        label: 'Pembelian',

                        data: purchaseValues,

                        fill: true,

                        tension: 0.3,

                        borderWidth: 2,
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.08)',
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#f97316',
                        pointRadius: 3,
                        pointHoverRadius: 5
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

                                display: true,
                                labels: { usePointStyle: true, boxWidth: 8, color: '#64748b' }

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
                            x: { grid: { display: false }, ticks: { color: '#94a3b8', maxRotation: 0, autoSkip: true, maxTicksLimit: 12 } },

                            y: {

                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: '#f1f5f9' },

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
