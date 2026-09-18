@extends('layouts.app')

@section('title', 'Penjualan')


{{-- ========================================================= --}}
{{-- CONTENT HEADER --}}
{{-- ========================================================= --}}

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h2 class="font-weight-bold mb-1">
            Penjualan
        </h2>

        <p class="text-muted mb-0">
            Kelola transaksi penjualan
        </p>

    </div>


    <a
        href="{{ route('sales.create') }}"
        class="btn btn-primary"
    >

        <i class="fas fa-plus mr-1"></i>

        Tambah Penjualan

    </a>

</div>

@stop


{{-- ========================================================= --}}
{{-- CONTENT --}}
{{-- ========================================================= --}}

@section('content')


{{-- ========================================================= --}}
{{-- STATISTIK --}}
{{-- ========================================================= --}}

<div class="row sales-statistics">


    {{-- ===================================================== --}}
    {{-- TOTAL TRANSAKSI --}}
    {{-- ===================================================== --}}

    <div class="col-md-4 mb-4">

        <div class="sales-stat-card">

            <div class="sales-stat-icon">

                <i class="fas fa-receipt"></i>

            </div>


            <div class="sales-stat-content">

                <div class="sales-stat-title">

                    Total Transaksi

                </div>


                <div class="sales-stat-value">

                    {{ number_format($totalTransactions ?? 0, 0, ',', '.') }}

                </div>


                <div class="sales-stat-description">

                    Total transaksi penjualan

                </div>

            </div>


            <div class="sales-stat-arrow">

                <i class="fas fa-arrow-right"></i>

            </div>

        </div>

    </div>



    {{-- ===================================================== --}}
    {{-- TOTAL PENJUALAN --}}
    {{-- ===================================================== --}}

    <div class="col-md-4 mb-4">

        <div class="sales-stat-card">

            <div class="sales-stat-icon">

                <i class="fas fa-wallet"></i>

            </div>


            <div class="sales-stat-content">

                <div class="sales-stat-title">

                    Total Penjualan

                </div>


                <div class="sales-stat-value sales-stat-money">

                    Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}

                </div>


                <div class="sales-stat-description">

                    Total pendapatan penjualan

                </div>

            </div>


            <div class="sales-stat-arrow">

                <i class="fas fa-arrow-right"></i>

            </div>

        </div>

    </div>



    {{-- ===================================================== --}}
    {{-- PRODUK TERJUAL --}}
    {{-- ===================================================== --}}

    <div class="col-md-4 mb-4">

        <div class="sales-stat-card">

            <div class="sales-stat-icon">

                <i class="fas fa-box"></i>

            </div>


            <div class="sales-stat-content">

                <div class="sales-stat-title">

                    Produk Terjual

                </div>


                <div class="sales-stat-value">

                    {{ number_format($totalProducts ?? 0, 0, ',', '.') }}

                </div>


                <div class="sales-stat-description">

                    Total produk yang terjual

                </div>

            </div>


            <div class="sales-stat-arrow">

                <i class="fas fa-arrow-right"></i>

            </div>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- CARD --}}
{{-- ========================================================= --}}

<div class="card shadow-sm">


    {{-- HEADER --}}
    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h5 class="mb-0 font-weight-bold">

                <i class="fas fa-receipt mr-2 text-primary"></i>

                Daftar Penjualan

            </h5>


            <span class="badge badge-primary px-3 py-2">

                {{ $sales->total() }}

                Transaksi

            </span>

        </div>

    </div>



    {{-- BODY --}}
    <div class="card-body">


        {{-- ================================================= --}}
        {{-- SUCCESS --}}
        {{-- ================================================= --}}

        @if(session('success'))

            <div
                class="alert alert-success alert-dismissible fade show"
                role="alert"
            >

                <i class="fas fa-check-circle mr-2"></i>

                {{ session('success') }}


                <button
                    type="button"
                    class="close"
                    data-dismiss="alert"
                >

                    <span>
                        &times;
                    </span>

                </button>

            </div>

        @endif



        {{-- ================================================= --}}
        {{-- ERROR --}}
        {{-- ================================================= --}}

        @if(session('error'))

            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >

                <i class="fas fa-exclamation-circle mr-2"></i>

                {{ session('error') }}


                <button
                    type="button"
                    class="close"
                    data-dismiss="alert"
                >

                    <span>
                        &times;
                    </span>

                </button>

            </div>

        @endif



        {{-- ================================================= --}}
        {{-- VALIDATION --}}
        {{-- ================================================= --}}

        @if($errors->any())

            <div
                class="alert alert-danger alert-dismissible fade show"
                role="alert"
            >

                <i class="fas fa-exclamation-triangle mr-2"></i>

                <strong>
                    Terjadi kesalahan:
                </strong>


                <ul class="mb-0 mt-2">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>


                <button
                    type="button"
                    class="close"
                    data-dismiss="alert"
                >

                    <span>
                        &times;
                    </span>

                </button>

            </div>

        @endif



        {{-- ================================================= --}}
        {{-- FILTER --}}
        {{-- ================================================= --}}

        <form
            method="GET"
            action="{{ route('sales.index') }}"
            id="filterForm"
            class="mb-4"
        >

            <div class="card bg-light border">

                <div class="card-header bg-white">

                    <h6 class="mb-0 font-weight-bold">

                        <i class="fas fa-filter mr-2 text-primary"></i>

                        Filter Penjualan

                    </h6>

                </div>


                <div class="card-body">

                    <div class="row">


                        {{-- PERIODE --}}
                        <div class="col-md-3">

                            <div class="form-group">

                                <label
                                    for="period"
                                    class="font-weight-bold"
                                >

                                    <i class="fas fa-calendar-alt mr-1"></i>

                                    Periode

                                </label>


                                <select
                                    name="period"
                                    id="period"
                                    class="form-control"
                                >

                                    <option
                                        value="all"
                                        {{ ($period ?? 'all') === 'all' ? 'selected' : '' }}
                                    >
                                        Semua Periode
                                    </option>


                                    <option
                                        value="daily"
                                        {{ ($period ?? '') === 'daily' ? 'selected' : '' }}
                                    >
                                        Per Hari
                                    </option>


                                    <option
                                        value="monthly"
                                        {{ ($period ?? '') === 'monthly' ? 'selected' : '' }}
                                    >
                                        Per Bulan
                                    </option>


                                    <option
                                        value="yearly"
                                        {{ ($period ?? '') === 'yearly' ? 'selected' : '' }}
                                    >
                                        Per Tahun
                                    </option>

                                </select>

                            </div>

                        </div>



                        {{-- TANGGAL --}}
                        <div
                            class="col-md-3"
                            id="dailyFilter"
                        >

                            <div class="form-group">

                                <label
                                    for="date"
                                    class="font-weight-bold"
                                >

                                    <i class="fas fa-calendar-day mr-1"></i>

                                    Tanggal

                                </label>


                                <input
                                    type="date"
                                    name="date"
                                    id="date"
                                    class="form-control"
                                    value="{{ request('date', date('Y-m-d')) }}"
                                >

                            </div>

                        </div>



                        {{-- BULAN --}}
                        <div
                            class="col-md-3"
                            id="monthlyFilter"
                        >

                            <div class="form-group">

                                <label
                                    for="month"
                                    class="font-weight-bold"
                                >

                                    <i class="fas fa-calendar mr-1"></i>

                                    Bulan

                                </label>


                                <input
                                    type="month"
                                    name="month"
                                    id="month"
                                    class="form-control"
                                    value="{{ request('month', date('Y-m')) }}"
                                >

                            </div>

                        </div>



                        {{-- TAHUN --}}
                        <div
                            class="col-md-3"
                            id="yearlyFilter"
                        >

                            <div class="form-group">

                                <label
                                    for="year"
                                    class="font-weight-bold"
                                >

                                    <i class="fas fa-calendar mr-1"></i>

                                    Tahun

                                </label>


                                <select
                                    name="year"
                                    id="year"
                                    class="form-control"
                                >

                                    <option value="">
                                        -- Pilih Tahun --
                                    </option>


                                    @foreach(($years ?? []) as $year)

                                        <option
                                            value="{{ $year }}"
                                            {{ request('year') == $year ? 'selected' : '' }}
                                        >

                                            {{ $year }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        </div>



                        {{-- SEARCH --}}
                        <div class="col-md-3">

                            <div class="form-group">

                                <label
                                    for="search"
                                    class="font-weight-bold"
                                >

                                    <i class="fas fa-search mr-1"></i>

                                    Cari

                                </label>


                                <input
                                    type="text"
                                    name="search"
                                    id="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="No. transaksi / pelanggan / kasir..."
                                >

                            </div>

                        </div>



                        {{-- STATUS --}}
                        <div class="col-md-3">

                            <div class="form-group">

                                <label
                                    for="payment_status"
                                    class="font-weight-bold"
                                >

                                    <i class="fas fa-credit-card mr-1"></i>

                                    Status Pembayaran

                                </label>


                                <select
                                    name="payment_status"
                                    id="payment_status"
                                    class="form-control"
                                >

                                    <option value="">
                                        Semua Status
                                    </option>


                                    <option
                                        value="PAID"
                                        {{ request('payment_status') === 'PAID' ? 'selected' : '' }}
                                    >
                                        Lunas
                                    </option>


                                    <option
                                        value="PENDING"
                                        {{ request('payment_status') === 'PENDING' ? 'selected' : '' }}
                                    >
                                        Menunggu Pembayaran
                                    </option>


                                    <option
                                        value="CASH_PENDING"
                                        {{ request('payment_status') === 'CASH_PENDING' ? 'selected' : '' }}
                                    >
                                        Cash / Belum Bayar
                                    </option>


                                    <option
                                        value="EXPIRED"
                                        {{ request('payment_status') === 'EXPIRED' ? 'selected' : '' }}
                                    >
                                        Expired
                                    </option>


                                    <option
                                        value="CANCELLED"
                                        {{ request('payment_status') === 'CANCELLED' ? 'selected' : '' }}
                                    >
                                        Dibatalkan
                                    </option>


                                    <option
                                        value="DENIED"
                                        {{ request('payment_status') === 'DENIED' ? 'selected' : '' }}
                                    >
                                        Ditolak
                                    </option>

                                </select>

                            </div>

                        </div>

                        <div class="col-md-3"><div class="form-group"><label>Pembayaran</label><select name="payment_method" class="form-control"><option value="">Semua</option>@foreach(['cash' => 'Cash', 'qris' => 'QRIS', 'va' => 'VA'] as $value => $label)<option value="{{ $value }}" {{ request('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Sumber</label><select name="order_source" class="form-control"><option value="">Semua</option><option value="cashier" {{ request('order_source') === 'cashier' ? 'selected' : '' }}>Kasir</option><option value="table_qr" {{ request('order_source') === 'table_qr' ? 'selected' : '' }}>QR Meja</option></select></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Dari tanggal</label><input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>Sampai tanggal</label><input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}"></div></div>

                    </div>



                    <div class="d-flex flex-wrap mt-2">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2 mb-2"
                        >

                            <i class="fas fa-search mr-1"></i>

                            Tampilkan

                        </button>


                        <a
                            href="{{ route('sales.index') }}"
                            class="btn btn-secondary mb-2"
                        >

                            <i class="fas fa-sync-alt mr-1"></i>

                            Reset

                        </a>

                    </div>

                </div>

            </div>

        </form>



        {{-- ================================================= --}}
        {{-- TABLE --}}
        {{-- ================================================= --}}

        <div class="table-responsive">

            <table
                class="table table-hover table-bordered align-middle"
            >

                <thead class="thead-light">

                    <tr>

                        <th
                            width="55"
                            class="text-center"
                        >
                            No
                        </th>

                        <th>
                            Kode
                        </th>

                        <th>
                            Tanggal
                        </th>

                        <th>Pelanggan</th>
                        <th>Kasir</th>
                        <th>Sumber</th>

                        <th>
                            Produk
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Bayar
                        </th>

                        <th>
                            Status Pembayaran
                        </th>

                        <th>
                            Metode
                        </th>

                        <th>
                            Kembalian
                        </th>

                        <th
                            width="180"
                            class="text-center"
                        >
                            Aksi
                        </th>

                    </tr>

                </thead>



                <tbody>


                    @forelse($sales as $sale)

                        @php

                            $paymentStatus = $sale->payment_status_display;

                            $paymentMethod = $sale->payment_method_display;


                            $notes =
                                (string) (
                                    $sale->notes ?? ''
                                );


                            $noteLines =
                                preg_split(
                                    '/\r\n|\r|\n/',
                                    $notes
                                );


                            foreach (
                                $noteLines
                                as $line
                            ) {

                                $line =
                                    trim($line);


                                if (
                                    str_starts_with(
                                        $line,
                                        'PAYMENT_STATUS='
                                    )
                                ) {

                                    $paymentStatus =
                                        trim(
                                            substr(
                                                $line,
                                                strlen(
                                                    'PAYMENT_STATUS='
                                                )
                                            )
                                        );
                                }


                                if (
                                    str_starts_with(
                                        $line,
                                        'PAYMENT_METHOD='
                                    )
                                ) {

                                    $paymentMethod =
                                        trim(
                                            substr(
                                                $line,
                                                strlen(
                                                    'PAYMENT_METHOD='
                                                )
                                            )
                                        );
                                }

                            }


                            if (
                                $paymentStatus ===
                                'UNKNOWN'
                            ) {

                                if (
                                    (float) (
                                        $sale->paid ?? 0
                                    )
                                    >=
                                    (float) (
                                        $sale->total ?? 0
                                    )
                                    &&
                                    (float) (
                                        $sale->total ?? 0
                                    ) > 0
                                ) {

                                    $paymentStatus =
                                        'PAID';

                                    $paymentMethod =
                                        'CASH';
                                }

                            }


                            switch (
                                strtoupper(
                                    $paymentStatus
                                )
                            ) {

                                case 'PAID':

                                    $statusLabel =
                                        'LUNAS';

                                    $statusClass =
                                        'success';

                                    $statusIcon =
                                        'fa-check-circle';

                                    break;


                                case 'PENDING':

                                    $statusLabel =
                                        'MENUNGGU PEMBAYARAN';

                                    $statusClass =
                                        'warning';

                                    $statusIcon =
                                        'fa-clock';

                                    break;


                                case 'CASH_PENDING':

                                    $statusLabel =
                                        'CASH / BELUM BAYAR';

                                    $statusClass =
                                        'info';

                                    $statusIcon =
                                        'fa-money-bill-wave';

                                    break;


                                case 'EXPIRED':

                                    $statusLabel =
                                        'EXPIRED';

                                    $statusClass =
                                        'danger';

                                    $statusIcon =
                                        'fa-clock';

                                    break;


                                case 'CANCELLED':

                                    $statusLabel =
                                        'DIBATALKAN';

                                    $statusClass =
                                        'danger';

                                    $statusIcon =
                                        'fa-times-circle';

                                    break;


                                case 'DENIED':

                                    $statusLabel =
                                        'DITOLAK';

                                    $statusClass =
                                        'danger';

                                    $statusIcon =
                                        'fa-ban';

                                    break;


                                default:

                                    $statusLabel =
                                        'BELUM DIKETAHUI';

                                    $statusClass =
                                        'secondary';

                                    $statusIcon =
                                        'fa-question-circle';

                                    break;

                            }


                            switch (
                                strtoupper(
                                    $paymentMethod
                                )
                            ) {

                                case 'QRIS':

                                    $methodLabel =
                                        'QRIS';

                                    $methodClass =
                                        'success';

                                    break;


                                case 'VA':

                                    $methodLabel =
                                        'VIRTUAL ACCOUNT';

                                    $methodClass =
                                        'primary';

                                    break;


                                case 'CASH':

                                    $methodLabel =
                                        'CASH';

                                    $methodClass =
                                        'info';

                                    break;


                                case '':

                                case '-':

                                    $methodLabel =
                                        '-';

                                    $methodClass =
                                        'secondary';

                                    break;


                                default:

                                    $methodLabel =
                                        strtoupper(
                                            $paymentMethod
                                        );

                                    $methodClass =
                                        'secondary';

                                    break;

                            }

                        @endphp



                        <tr>


                            {{-- NO --}}

                            <td class="text-center">

                                {{ $sales->firstItem() + $loop->index }}

                            </td>



                            {{-- KODE --}}

                            <td>

                                <a
                                    href="{{ route('sales.show', $sale->id) }}"
                                    class="font-weight-bold text-primary"
                                >

                                    {{ $sale->code }}

                                </a>

                            </td>



                            {{-- TANGGAL --}}

                            <td>

                                @if($sale->sale_date)

                                    {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}

                                @else

                                    -

                                @endif

                            </td>



                            {{-- PRODUK --}}

                            <td>{{ $sale->customer_display_name }}</td>
                            <td>{{ $sale->cashier_display_name }}</td>
                            <td>{{ $sale->order_source_display }}@if($sale->table)<br><small>Meja {{ $sale->table->name }}</small>@endif</td>

                            <td>

                                <span class="badge badge-info">

                                    {{ $sale->saleDetails->count() }}

                                    Jenis

                                </span>


                                <span class="text-muted">

                                    /

                                    {{ $sale->saleDetails->sum('quantity') }}

                                    item

                                </span>

                            </td>



                            {{-- TOTAL --}}

                            <td>

                                <strong>

                                    Rp
                                    {{ number_format($sale->total ?? 0, 0, ',', '.') }}

                                </strong>

                            </td>



                            {{-- BAYAR --}}

                            <td>

                                Rp
                                {{ number_format($sale->paid ?? 0, 0, ',', '.') }}

                            </td>



                            {{-- STATUS --}}

                            <td>

                                <span
                                    class="badge badge-{{ $statusClass }} px-2 py-2"
                                >

                                    <i
                                        class="fas {{ $statusIcon }} mr-1"
                                    ></i>

                                    {{ $statusLabel }}

                                </span>

                            </td>



                            {{-- METODE --}}

                            <td>

                                <span
                                    class="badge badge-{{ $methodClass }}"
                                >

                                    {{ $methodLabel }}

                                </span>

                            </td>



                            {{-- KEMBALIAN --}}

                            <td>

                                @if(($sale->change ?? 0) > 0)

                                    <strong class="text-success">

                                        Rp
                                        {{ number_format($sale->change, 0, ',', '.') }}

                                    </strong>

                                @else

                                    Rp 0

                                @endif

                            </td>



                            {{-- AKSI --}}

                            <td class="text-center">

                                <a
                                    href="{{ route('sales.show', $sale->id) }}"
                                    class="btn btn-sm btn-info"
                                    title="Detail"
                                >

                                    <i class="fas fa-eye"></i>

                                </a>


                                <a
                                    href="{{ route('sales.receipt', $sale->id) }}"
                                    class="btn btn-sm btn-success"
                                    title="Struk"
                                    target="_blank"
                                >

                                    <i class="fas fa-receipt"></i>

                                </a>


                                <a
                                    href="{{ route('sales.edit', $sale->id) }}"
                                    class="btn btn-sm btn-warning"
                                    title="Edit"
                                >

                                    <i class="fas fa-edit"></i>

                                </a>


                                <form
                                    action="{{ route('sales.destroy', $sale->id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus transaksi ini? Stok akan dikembalikan.')"
                                >

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="btn btn-sm btn-danger"
                                        title="Hapus"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="10"
                                class="text-center py-5"
                            >

                                <div class="py-3">

                                    <i
                                        class="fas fa-shopping-cart fa-3x text-muted mb-3"
                                    ></i>


                                    <h5 class="text-muted">

                                        Tidak ada transaksi

                                    </h5>


                                    <p class="text-muted mb-3">

                                        Tidak ditemukan transaksi
                                        berdasarkan filter yang dipilih.

                                    </p>


                                    <a
                                        href="{{ route('sales.create') }}"
                                        class="btn btn-primary"
                                    >

                                        <i class="fas fa-plus mr-1"></i>

                                        Tambah Penjualan

                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>



        {{-- ================================================= --}}
        {{-- PAGINATION --}}
        {{-- ================================================= --}}

        @if($sales->hasPages())

            <div
                class="d-flex justify-content-between align-items-center mt-3"
            >

                <div class="text-muted small">

                    Menampilkan

                    <strong>
                        {{ $sales->firstItem() }}
                    </strong>

                    sampai

                    <strong>
                        {{ $sales->lastItem() }}
                    </strong>

                    dari

                    <strong>
                        {{ $sales->total() }}
                    </strong>

                    transaksi

                </div>


                <div>

                    {{ $sales->appends(request()->query())->links() }}

                </div>

            </div>

        @endif


    </div>

</div>

@stop



{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

@section('js')

<script>

$(document).ready(function () {

    const period =
        $('#period');

    const dailyFilter =
        $('#dailyFilter');

    const monthlyFilter =
        $('#monthlyFilter');

    const yearlyFilter =
        $('#yearlyFilter');


    function updatePeriodFilter()
    {

        const value =
            period.val();


        dailyFilter.hide();

        monthlyFilter.hide();

        yearlyFilter.hide();


        if (
            value ===
            'daily'
        ) {

            dailyFilter.show();

        }


        if (
            value ===
            'monthly'
        ) {

            monthlyFilter.show();

        }


        if (
            value ===
            'yearly'
        ) {

            yearlyFilter.show();

        }

    }


    period.on(
        'change',
        function () {

            updatePeriodFilter();

        }
    );


    updatePeriodFilter();


    /*
    |--------------------------------------------------------------------------
    | AUTO CLOSE ALERT
    |--------------------------------------------------------------------------
    */

    setTimeout(
        function () {

            $('.alert-dismissible')
                .alert('close');

        },
        7000
    );

});

</script>

@stop



{{-- ========================================================= --}}
{{-- CSS --}}
{{-- ========================================================= --}}

@section('css')

<style>

/* ========================================================= */
/* GENERAL */
/* ========================================================= */

.table td,
.table th {

    vertical-align: middle;

}


.badge {

    font-weight: 600;

}


.card {

    border-radius: 12px;

}


.btn {

    border-radius: 7px;

}


.table-hover tbody tr:hover {

    background-color: #faf8f4;

}


.badge-success {

    background-color: #28a745;

}


.badge-warning {

    color: #212529;

}


/* ========================================================= */
/* SALES STATISTICS */
/* ========================================================= */

.sales-statistics {

    margin-top: 20px;

}


/* ========================================================= */
/* STAT CARD */
/* ========================================================= */

.sales-stat-card {

    position: relative;

    min-height: 145px;

    display: flex;

    align-items: center;

    padding: 24px 58px 24px 24px;

    background:

        linear-gradient(
            135deg,
            #fffdf9 0%,
            #faf6ef 100%
        );

    border:

        1px solid
        #eee6da;

    border-radius: 16px;

    box-shadow:

        0 5px 18px
        rgba(92, 72, 48, 0.07);

    overflow: hidden;

    transition:

        transform 0.2s ease,
        box-shadow 0.2s ease,
        border-color 0.2s ease;

}


/* ========================================================= */
/* HOVER */
/* ========================================================= */

.sales-stat-card:hover {

    transform:

        translateY(-2px);

    box-shadow:

        0 9px 25px
        rgba(92, 72, 48, 0.11);

    border-color:

        #e6dccd;

}


/* ========================================================= */
/* ICON */
/* ========================================================= */

.sales-stat-icon {

    width: 58px;

    height: 58px;

    min-width: 58px;

    display: flex;

    align-items: center;

    justify-content: center;

    margin-right: 18px;

    background:

        #f4ecdf;

    border-radius: 17px;

    color:

        #8c7b68;

    font-size: 22px;

}


/* ========================================================= */
/* CONTENT */
/* ========================================================= */

.sales-stat-content {

    min-width: 0;

    position: relative;

    z-index: 2;

}


/* ========================================================= */
/* TITLE */
/* ========================================================= */

.sales-stat-title {

    margin-bottom: 5px;

    color:

        #75695c;

    font-size: 15px;

    font-weight: 600;

    letter-spacing: 0.1px;

}


/* ========================================================= */
/* VALUE */
/* ========================================================= */

.sales-stat-value {

    color:

        #27303a;

    font-size: 29px;

    line-height: 1.2;

    font-weight: 800;

    letter-spacing: -0.5px;

}


/* ========================================================= */
/* MONEY */
/* ========================================================= */

.sales-stat-money {

    font-size: 25px;

    letter-spacing: -0.3px;

}


/* ========================================================= */
/* DESCRIPTION */
/* ========================================================= */

.sales-stat-description {

    margin-top: 6px;

    color:

        #a09689;

    font-size: 12px;

}


/* ========================================================= */
/* ARROW */
/* ========================================================= */

.sales-stat-arrow {

    position: absolute;

    right: 18px;

    bottom: 18px;

    width: 34px;

    height: 34px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:

        #ffffff;

    border:

        1px solid
        #eee5d8;

    border-radius: 10px;

    color:

        #8d7c68;

    font-size: 13px;

    box-shadow:

        0 2px 8px
        rgba(92, 72, 48, 0.06);

}


/* ========================================================= */
/* SUBTLE DECORATIVE SHAPE */
/* Cream only — no orange */
/* ========================================================= */

.sales-stat-card::after {

    content: "";

    position: absolute;

    width: 125px;

    height: 125px;

    right: -55px;

    top: -60px;

    border-radius: 50%;

    background:

        rgba(239, 231, 219, 0.48);

}


/* ========================================================= */
/* RESPONSIVE */
/* ========================================================= */

@media (max-width: 991.98px) {

    .sales-stat-card {

        min-height: 135px;

    }

}


@media (max-width: 767.98px) {

    .sales-statistics {

        margin-top: 15px;

    }


    .sales-stat-card {

        min-height: 125px;

        padding: 20px 55px 20px 20px;

    }


    .sales-stat-icon {

        width: 52px;

        height: 52px;

        min-width: 52px;

        margin-right: 14px;

        font-size: 19px;

    }


    .sales-stat-title {

        font-size: 14px;

    }


    .sales-stat-value {

        font-size: 25px;

    }


    .sales-stat-money {

        font-size: 21px;

    }


    .sales-stat-description {

        font-size: 11px;

    }

}

</style>

@stop
