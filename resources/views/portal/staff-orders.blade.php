@extends('layouts.app')
@section('title', 'Order Customer')
@section('css')
<link rel="stylesheet" href="{{ asset('css/customer-orders.css') }}">
@endsection
@section('content')
@php
    $periodLabels = ['today'=>'Hari Ini', 'week'=>'Minggu Ini', 'month'=>'Bulan Ini', 'year'=>'Tahun Ini'];
@endphp
<div class="customer-orders">
    <header class="orders-heading"><h1>Order Customer</h1><p>Kelola pesanan yang masuk dari pelanggan.</p></header>
    <div class="orders-summary">
        <section><span>Pesanan Hari Ini</span><strong>{{ number_format($summary['today'], 0, ',', '.') }}</strong><small>Semua status</small></section>
        <section><span>Belum Selesai</span><strong>{{ number_format($summary['unfinished'], 0, ',', '.') }}</strong><small>{{ $periodLabels[$period] }}</small></section>
        <section><span>Selesai</span><strong>{{ number_format($summary['completed'], 0, ',', '.') }}</strong><small>{{ $periodLabels[$period] }}</small></section>
        <section><span>Total Penjualan Hari Ini</span><strong class="orders-money">Rp {{ number_format($summary['sales_today'], 0, ',', '.') }}</strong><small>Pesanan hari ini yang sudah lunas</small></section>
    </div>
    <section class="orders-filters" aria-label="Filter pesanan">
        <nav class="orders-periods" aria-label="Periode pesanan">
            @foreach($periodLabels as $key=>$label)
                <a href="{{ route('portal-orders.index', ['period'=>$key, 'status'=>$status, 'search'=>$search]) }}" @if($period===$key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>
        <form method="get" action="{{ route('portal-orders.index') }}" class="orders-filter-form">
            <input type="hidden" name="period" value="{{ $period }}">
            <div><label for="order-search">Cari Pesanan</label><input id="order-search" name="search" value="{{ $search }}" maxlength="100" placeholder="Nomor order / nama pelanggan" type="search"></div>
            <div><label for="order-status">Status Pesanan</label><select id="order-status" name="status">
                @foreach(['all'=>'Semua Status', 'unfinished'=>'Belum Selesai', 'completed'=>'Selesai', 'cancelled'=>'Dibatalkan'] as $key=>$label)
                    <option value="{{ $key }}" @selected($status===$key)>{{ $label }}</option>
                @endforeach
            </select></div>
            <button class="orders-button orders-primary" type="submit">Terapkan</button>
            <a class="orders-reset" href="{{ route('portal-orders.index') }}">Reset</a>
        </form>
    </section>
    <div class="orders-result-heading"><h2>Daftar Pesanan</h2><span>{{ number_format($orders->total(), 0, ',', '.') }} pesanan · {{ $periodLabels[$period] }}</span></div>
    <div class="orders-list">
        @forelse($orders as $order)
            @php
                $orderStatus = $order->order_status ?? 'pending';
                $unfinished = in_array($orderStatus, ['pending', 'confirmed', 'processing'], true);
                $statusLabel = $unfinished ? 'BELUM SELESAI' : ($orderStatus === 'completed' ? 'SELESAI' : ($orderStatus === 'cancelled' ? 'DIBATALKAN' : strtoupper($orderStatus)));
                $tone = $unfinished ? 'amber' : ($orderStatus === 'completed' ? 'green' : 'red');
                $method = ['cash'=>'Cash', 'qris'=>'QRIS', 'va'=>'Virtual Account'][$order->payment_method] ?? strtoupper($order->payment_method);
                $paymentLabel = ['PAID'=>'LUNAS', 'CASH_PENDING'=>'BELUM LUNAS', 'PENDING'=>'MENUNGGU PEMBAYARAN', 'CANCELLED'=>'DIBATALKAN', 'EXPIRED'=>'KEDALUWARSA'][$order->payment_status] ?? $order->payment_status;
            @endphp
            <article class="orders-card">
                <div class="orders-card-top"><h3>{{ $order->code }}</h3><span class="orders-badge orders-badge--{{ $tone }}">{{ $statusLabel }}</span></div>
                <p class="orders-customer">{{ $order->customer?->name ?? 'Pelanggan' }}</p>
                <div class="orders-items">
                    @foreach($order->saleDetails->take(2) as $detail)<p>{{ $detail->product?->name ?? 'Produk' }} <span>× {{ $detail->quantity }}</span></p>@endforeach
                    @if($order->saleDetails->count()>2)<small>+{{ $order->saleDetails->count()-2 }} item lainnya · lihat detail</small>@endif
                </div>
                <div class="orders-card-payment"><strong>Rp {{ number_format($order->total, 0, ',', '.') }}</strong><span>{{ $method }} · <b class="{{ $order->payment_status==='PAID' ? 'orders-paid' : '' }}">{{ $paymentLabel }}</b></span></div>
                <time datetime="{{ $order->created_at->toIso8601String() }}">{{ $order->created_at->format('d M Y') }} · {{ $order->created_at->format('H:i') }}</time>
                <div class="orders-card-actions">
                    <a class="orders-button {{ $unfinished && auth()->user()->can('sales.create') ? 'orders-primary' : 'orders-secondary' }}" href="{{ route('portal-orders.show', $order->id) }}">{{ $unfinished && auth()->user()->can('sales.create') ? 'Proses Pesanan' : 'Lihat Detail' }}</a>
                    <a class="orders-receipt" href="{{ route('sales.receipt', $order->id) }}" target="_blank" rel="noopener">Cetak Struk</a>
                </div>
                @if($order->payment_status==='CASH_PENDING' && auth()->user()->can('sales.create'))
                    <form method="post" action="{{ route('portal-orders.update', $order->id) }}" class="orders-quick-actions">@csrf @method('patch')
                        <button name="action" value="paid" class="orders-confirm">Konfirmasi pembayaran diterima</button>
                        <button name="action" value="cancelled" class="orders-cancel">Batalkan</button>
                    </form>
                @endif
            </article>
        @empty
            <div class="orders-empty"><h3>Tidak ada pesanan</h3><p>Belum ada pesanan yang sesuai dengan periode, status, atau pencarian ini.</p></div>
        @endforelse
    </div>
    @if($orders->hasPages())
        <nav class="orders-pagination" aria-label="Halaman pesanan">
            <span>Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan</span>
            <div>
                @if($orders->onFirstPage())<span aria-disabled="true">Sebelumnya</span>@else<a href="{{ $orders->previousPageUrl() }}" rel="prev">Sebelumnya</a>@endif
                <span>Halaman {{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span aria-disabled="true">Berikutnya</span>@endif
            </div>
        </nav>
    @endif
</div>
@endsection
