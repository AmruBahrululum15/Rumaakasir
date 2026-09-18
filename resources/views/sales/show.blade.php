@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold mb-1">Detail Penjualan</h2>
        <p class="text-muted mb-0">Informasi transaksi penjualan</p>
    </div>

    <a href="{{ route('sales.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Kembali
    </a>
</div>
@stop

@section('content')
<div class="row">

    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="mb-0">Informasi Transaksi</h5>
            </div>

            <div class="card-body">

                <strong>Kode Penjualan</strong>
                <p>{{ $sale->code }}</p>

                <hr>

                <strong>Tanggal</strong>
                <p>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d F Y') }}</p>

                <hr>

                <strong>Pelanggan</strong><p>{{ $sale->customer_display_name }}</p>
                <strong>Kasir</strong><p>{{ $sale->cashier_display_name }}</p>
                <strong>Pembayaran</strong><p>{{ $sale->payment_method_display }} — {{ $sale->payment_status_display }}</p>
                <strong>Sumber Pesanan</strong><p>{{ $sale->order_source_display }}@if($sale->table) — Meja {{ $sale->table->name }}@endif</p>
                <hr>

                <strong>Total Pembayaran</strong>
                <h3 class="text-success">
                    Rp {{ number_format($sale->total,0,',','.') }}
                </h3>

                <hr>

                <strong>Catatan</strong>
                <p>{{ $sale->notes ?? '-' }}</p>

                <a href="{{ route('sales.receipt',$sale->id) }}"
                   class="btn btn-primary btn-block mt-3">
                    <i class="fas fa-print"></i>
                    Cetak Struk
                </a>

            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">

            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-cart"></i>
                    Produk Terjual
                </h5>
            </div>

            <div class="card-body table-responsive">

                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Produk</th>
                            <th>Harga Satuan</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($sale->saleDetails as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $detail->product->name }}</td>

                            <td>
                                Rp {{ number_format($detail->price,0,',','.') }}
                            </td>

                            <td>
                                <span class="badge badge-primary">
                                    {{ $detail->quantity }}
                                </span>
                            </td>

                            <td>
                                Rp {{ number_format($detail->subtotal,0,',','.') }}
                            </td>
                        </tr>
                        @endforeach

                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right">
                                TOTAL
                            </th>
                            <th class="text-success">
                                Rp {{ number_format($sale->total,0,',','.') }}
                            </th>
                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>
    </div>

</div>
@stop
