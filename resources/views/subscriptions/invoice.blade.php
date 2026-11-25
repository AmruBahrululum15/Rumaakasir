@extends('layouts.app')
@section('title', 'Invoice Langganan')
@section('content')
<section class="content-header"><div class="container-fluid"><h1>Invoice Langganan</h1></div></section>
<section class="content"><div class="container-fluid"><div class="card"><div class="card-body">
    <h2 class="h4">{{ $order->business_name }}</h2><span class="badge badge-success mb-4">Paid</span>
    <dl class="row">
        @foreach(['Order ID' => $order->order_id, 'Paket' => $order->plan_name, 'Periode' => $order->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan', 'Nominal' => 'Rp'.number_format($order->amount, 0, ',', '.'), 'Gateway' => 'DOKU', 'Metode pembayaran' => $order->payment_method ?: '-', 'Tanggal pembayaran' => $order->paid_at->format('d M Y H:i'), 'Masa aktif mulai' => $order->started_at->format('d M Y H:i'), 'Masa aktif berakhir' => $order->expires_at->format('d M Y H:i')] as $label => $value)
            <dt class="col-sm-4">{{ $label }}</dt><dd class="col-sm-8" style="overflow-wrap:anywhere">{{ $value }}</dd>
        @endforeach
    </dl>
    <a class="btn btn-outline-secondary" href="{{ route('subscriptions.index') }}">Kembali</a>
</div></div></div></section>
@endsection
