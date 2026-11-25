@extends('layouts.app')
@section('title', 'Status Pembayaran')
@section('css')
<link rel="stylesheet" href="{{ asset('css/subscription-payment.css') }}">
@endsection
@section('content')
@php
    $state = $order->displayStatus();
    $tone = match($state) { 'paid'=>'success', 'failed'=>'danger', 'expired'=>'muted', default=>'waiting' };
    $label = match($state) { 'paid'=>'BERHASIL', 'failed'=>'GAGAL', 'expired'=>'KADALUARSA', default=>'MENUNGGU PEMBAYARAN' };
    $uncertain = in_array($state, ['creating', 'create_unknown'], true);
@endphp
<div class="subscription-payment">
    <section class="payment-card" aria-labelledby="payment-title">
        <header class="payment-heading">
            <span class="payment-icon payment-tone-{{ $tone }}" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"/>
                    @if($state==='paid')<path d="m8 12 3 3 5-6"/>
                    @elseif($state==='failed')<path d="m9 9 6 6m0-6-6 6"/>
                    @else<path d="M12 7v5l3 2"/>@endif
                </svg>
            </span>
            <h1 id="payment-title">Status Pembayaran</h1>
            <p>Langganan Premium Rumaakasir</p>
            <span class="payment-badge payment-tone-{{ $tone }}" role="status">{{ $label }}</span>
        </header>
        <dl class="payment-details">
            <div><dt>Order ID</dt><dd class="payment-code">{{ $order->order_id }}</dd></div>
            <div><dt>Paket</dt><dd>{{ $order->plan_name }} {{ $order->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}</dd></div>
            <div><dt>Metode</dt><dd>{{ $order->payment_provider === 'midtrans' ? 'Midtrans' : 'DOKU' }}</dd></div>
            <div class="payment-total"><dt>Total</dt><dd>Rp {{ number_format($order->amount, 0, ',', '.') }}</dd></div>
        </dl>
        <div class="payment-message">
            @if($state==='paid')
                <strong>Pembayaran berhasil diverifikasi.</strong>
                <p>Masa Premium: {{ $order->started_at?->format('d M Y') }} – {{ $order->expires_at?->format('d M Y') }}.</p>
            @elseif($state==='failed')
                <strong>Pembayaran belum dapat diproses.</strong>
                <p>Silakan coba kembali atau pilih metode pembayaran lain.</p>
            @elseif($state==='expired')
                <strong>Waktu pembayaran telah berakhir.</strong>
                <p>Silakan buat pembayaran baru untuk melanjutkan langganan.</p>
            @elseif($uncertain)
                <strong>Pembayaran sedang diperiksa.</strong>
                <p>Periksa status beberapa saat lagi. Pembayaran baru belum dibuat agar tagihan tidak terduplikasi.</p>
            @else
                <strong>Selesaikan pembayaran melalui {{ $order->payment_provider === 'midtrans' ? 'Midtrans' : 'DOKU' }}.</strong>
                <p>Sudah membayar? Status akan diperbarui setelah pembayaran terverifikasi.</p>
                @if($order->last_payment_status==='FAILED')<p>Percobaan sebelumnya belum berhasil. Pilih metode lain pada halaman {{ $order->payment_provider === 'midtrans' ? 'Midtrans' : 'DOKU' }}.</p>@endif
                <p class="payment-deadline">Batas pembayaran: {{ $order->payment_due_at->format('d M Y H:i') }}.</p>
            @endif
        </div>
        <div class="payment-actions">
            @if($state==='paid')
                <a class="payment-button payment-primary" href="{{ route('subscriptions.invoice', $order->order_id) }}">Lihat Invoice</a>
            @elseif(in_array($state, ['failed','expired'], true))
                <form method="POST" action="{{ route('subscriptions.retry', $order->order_id) }}">@csrf<button class="payment-button payment-primary" type="submit">Coba Bayar Lagi</button></form>
                <small>Harga mengikuti paket yang berlaku saat pembayaran baru dibuat.</small>
            @elseif($state==='pending' && $order->validPaymentUrl())
                <a class="payment-button payment-primary" href="{{ $order->payment_url }}" rel="noreferrer">{{ $order->last_payment_status==='FAILED' ? 'Coba Bayar Lagi' : 'Lanjutkan Pembayaran' }}</a>
            @endif
            <div class="payment-secondary-actions">
                <a class="payment-button payment-secondary" href="{{ route('subscriptions.index') }}">Kembali ke Paket</a>
                <a class="payment-button payment-text" href="{{ route('subscriptions.result', $order->order_id) }}">Periksa Status</a>
            </div>
        </div>
        <p class="payment-footnote">Premium aktif setelah pembayaran terverifikasi.</p>
    </section>
</div>
@endsection
