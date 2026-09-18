<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $reportTitle }}
    </title>

    <style>

        @page {
            margin: 25px 25px 35px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .header h2 {
            margin: 5px 0;
            font-size: 14px;
        }

        .header p {
            margin: 3px 0;
            color: #555;
        }

        .line {
            border-bottom: 2px solid #222;
            margin-top: 10px;
        }

        .period {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            background: #f2f2f2;
        }

        .period strong {
            font-size: 11px;
        }

        .summary {
            width: 100%;
            margin-bottom: 20px;
        }

        .summary td {
            width: 33.33%;
            padding: 5px;
            vertical-align: top;
        }

        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        .summary-title {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 13px;
            font-weight: bold;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px solid #333;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table.data th {
            background: #343a40;
            color: white;
            padding: 7px;
            border: 1px solid #222;
            text-align: left;
        }

        table.data td {
            padding: 6px;
            border: 1px solid #ccc;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
        }

        .empty {
            text-align: center;
            padding: 15px;
            color: #777;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #777;
        }

        .page-number:after {
            content: counter(page);
        }

        .avoid-break {
            page-break-inside: avoid;
        }

    </style>

</head>


<body>


{{-- ========================================================= --}}
{{-- HEADER --}}
{{-- ========================================================= --}}

<div class="header">

    <h1>
        LAPORAN
    </h1>

    <h2>
        {{ strtoupper($reportTitle) }}
    </h2>

    <p>
        Sistem Informasi Penjualan & Pembelian
    </p>

    <p>
        Dicetak:
        {{ now()->format('d/m/Y H:i') }}
    </p>

    <div class="line"></div>

</div>


{{-- ========================================================= --}}
{{-- PERIODE --}}
{{-- ========================================================= --}}

<div class="period">

    <strong>
        Periode Laporan:
    </strong>

    {{ $periodTitle }}

    &nbsp;|&nbsp;

    <strong>
        {{ $periodDescription }}
    </strong>

</div>


{{-- ========================================================= --}}
{{-- RINGKASAN PENJUALAN --}}
{{-- ========================================================= --}}

@if($reportType === 'sales')

<div class="section-title">
    Ringkasan Penjualan
</div>

<table class="summary">

    <tr>

        <td>

            <div class="summary-box">

                <div class="summary-title">
                    TOTAL TRANSAKSI
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $totalSalesTransactions,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </td>


        <td>

            <div class="summary-box">

                <div class="summary-title">
                    TOTAL PENJUALAN
                </div>

                <div class="summary-value">

                    Rp
                    {{ number_format(
                        $totalSales,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </td>


        <td>

            <div class="summary-box">

                <div class="summary-title">
                    PRODUK TERJUAL
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $totalProductsSold,
                        0,
                        ',',
                        '.'
                    ) }}

                    pcs

                </div>

            </div>

        </td>

    </tr>

</table>

@endif


{{-- ========================================================= --}}
{{-- RINGKASAN PEMBELIAN --}}
{{-- ========================================================= --}}

@if($reportType === 'purchases')

<div class="section-title">
    Ringkasan Pembelian
</div>

<table class="summary">

    <tr>

        <td>

            <div class="summary-box">

                <div class="summary-title">
                    TOTAL TRANSAKSI
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $totalPurchaseTransactions,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </td>


        <td>

            <div class="summary-box">

                <div class="summary-title">
                    TOTAL PEMBELIAN
                </div>

                <div class="summary-value">

                    Rp
                    {{ number_format(
                        $totalPurchases,
                        0,
                        ',',
                        '.'
                    ) }}

                </div>

            </div>

        </td>


        <td>

            <div class="summary-box">

                <div class="summary-title">
                    PRODUK DIBELI
                </div>

                <div class="summary-value">

                    {{ number_format(
                        $totalProductsPurchased,
                        0,
                        ',',
                        '.'
                    ) }}

                    pcs

                </div>

            </div>

        </td>

    </tr>

</table>

@endif


{{-- ========================================================= --}}
{{-- PRODUK TERLARIS --}}
{{-- ========================================================= --}}

@if($reportType === 'sales')

<div class="section-title">
    Produk Terlaris
</div>

<table class="data">

    <thead>

        <tr>

            <th width="40">
                No
            </th>

            <th>
                Produk
            </th>

            <th width="100">
                Jumlah
            </th>

            <th width="150">
                Total Penjualan
            </th>

        </tr>

    </thead>


    <tbody>

        @forelse($bestSalesProducts as $product)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $product->name }}
                </td>

                <td class="text-center">

                    {{ number_format(
                        $product->total_quantity,
                        0,
                        ',',
                        '.'
                    ) }}

                    pcs

                </td>

                <td class="text-right">

                    Rp
                    {{ number_format(
                        $product->total_sales,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="4"
                    class="empty"
                >
                    Tidak ada data produk terjual.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>

@endif


{{-- ========================================================= --}}
{{-- PRODUK PEMBELIAN --}}
{{-- ========================================================= --}}

@if($reportType === 'purchases')

<div class="section-title">
    Produk Paling Banyak Dibeli
</div>

<table class="data">

    <thead>

        <tr>

            <th width="40">
                No
            </th>

            <th>
                Produk
            </th>

            <th width="100">
                Jumlah
            </th>

            <th width="150">
                Total Pembelian
            </th>

        </tr>

    </thead>


    <tbody>

        @forelse($bestPurchaseProducts as $product)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $product->name }}
                </td>

                <td class="text-center">

                    {{ number_format(
                        $product->total_quantity,
                        0,
                        ',',
                        '.'
                    ) }}

                    pcs

                </td>

                <td class="text-right">

                    Rp
                    {{ number_format(
                        $product->total_purchases,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="4"
                    class="empty"
                >
                    Tidak ada data pembelian.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>


{{-- ========================================================= --}}
{{-- SUPPLIER --}}
{{-- ========================================================= --}}

<div class="section-title">
    Supplier Terbesar
</div>

<table class="data">

    <thead>

        <tr>

            <th width="40">
                No
            </th>

            <th>
                Supplier
            </th>

            <th width="100">
                Transaksi
            </th>

            <th width="150">
                Total Pembelian
            </th>

        </tr>

    </thead>


    <tbody>

        @forelse($topSuppliers as $supplier)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $supplier->name }}
                </td>

                <td class="text-center">

                    {{ number_format(
                        $supplier->total_transactions,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

                <td class="text-right">

                    Rp
                    {{ number_format(
                        $supplier->total_purchases,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="4"
                    class="empty"
                >
                    Tidak ada data supplier.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>

@endif


{{-- ========================================================= --}}
{{-- TRANSAKSI PENJUALAN --}}
{{-- ========================================================= --}}

@if($reportType === 'sales')

<div class="section-title">
    Daftar Transaksi Penjualan
</div>

<table class="data">

    <thead>

        <tr>

            <th width="35">
                No
            </th>

            <th width="90">
                Kode
            </th>

            <th width="75">
                Tanggal
            </th>

            <th>
                Produk
            </th>

            <th width="130">
                Total
            </th>

        </tr>

    </thead>


    <tbody>

        @forelse($sales as $sale)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $sale->code }}
                </td>

                <td>

                    {{ \Carbon\Carbon::parse(
                        $sale->sale_date
                    )->format('d/m/Y') }}

                </td>

                <td>

                    {{ $sale->saleDetails->count() }}
                    jenis /

                    {{ $sale->saleDetails->sum('quantity') }}
                    item

                </td>

                <td class="text-right total">

                    Rp
                    {{ number_format(
                        $sale->total,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="5"
                    class="empty"
                >
                    Tidak ada transaksi penjualan.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>

@endif


{{-- ========================================================= --}}
{{-- TRANSAKSI PEMBELIAN --}}
{{-- ========================================================= --}}

@if($reportType === 'purchases')

<div class="section-title">
    Daftar Transaksi Pembelian
</div>

<table class="data">

    <thead>

        <tr>

            <th width="35">
                No
            </th>

            <th width="90">
                Kode
            </th>

            <th width="75">
                Tanggal
            </th>

            <th>
                Supplier
            </th>

            <th>
                Produk
            </th>

            <th width="130">
                Total
            </th>

        </tr>

    </thead>


    <tbody>

        @forelse($purchases as $purchase)

            <tr>

                <td class="text-center">
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $purchase->code }}
                </td>

                <td>

                    {{ \Carbon\Carbon::parse(
                        $purchase->purchase_date
                    )->format('d/m/Y') }}

                </td>

                <td>

                    {{ $purchase->supplier->name ?? '-' }}

                </td>

                <td>

                    {{ $purchase->details->count() }}
                    jenis /

                    {{ $purchase->details->sum('quantity') }}
                    item

                </td>

                <td class="text-right total">

                    Rp
                    {{ number_format(
                        $purchase->total,
                        0,
                        ',',
                        '.'
                    ) }}

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="6"
                    class="empty"
                >
                    Tidak ada transaksi pembelian.
                </td>

            </tr>

        @endforelse

    </tbody>

</table>

@endif


{{-- ========================================================= --}}
{{-- FOOTER --}}
{{-- ========================================================= --}}

<div class="footer">

    {{ $reportTitle }}
    -
    {{ $periodDescription }}

    &nbsp;&nbsp;|&nbsp;&nbsp;

    Halaman
    <span class="page-number"></span>

</div>


</body>

</html>