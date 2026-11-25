@extends('layouts.app')
@section('title','Detail Reservasi')
@section('content')
<link rel="stylesheet" href="{{ asset('css/reservations.css') }}">
<link rel="stylesheet" href="{{ asset('css/reservation-detail.css') }}">
@php
    $remaining = max(0, $booking->total - $booking->paid);
    $paymentLabel = ['paid'=>'Lunas','partial'=>'Sebagian dibayar','unpaid'=>'Belum dibayar'][$booking->payment_status] ?? $booking->payment_status;
    $actions = ['pending'=>['confirmed'=>'Terima','awaiting_payment'=>'Tunggu pembayaran','cancelled'=>'Tolak'],'awaiting_payment'=>['confirmed'=>'Konfirmasi','cancelled'=>'Batalkan'],'confirmed'=>['in_progress'=>'Check-in','completed'=>'Selesaikan','no_show'=>'Tidak hadir','cancelled'=>'Batalkan'],'in_progress'=>['completed'=>'Selesaikan','cancelled'=>'Batalkan']][$booking->status] ?? [];
@endphp
<div class="reservation-page reservation-detail">
    <div class="rd-heading">
        <div>
            <a class="rd-back" href="{{ route('bookings.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Daftar reservasi</a>
            <h1>Detail Reservasi</h1>
            <p>Informasi pelanggan, jadwal, dan pembayaran dalam satu halaman.</p>
        </div>
        <div class="rv-actions" style="margin-top:0"><span class="rd-reference"><i class="fas fa-ticket-alt" aria-hidden="true"></i>{{ $booking->code ?? 'Booking #'.$booking->id }}</span><a class="rv-btn rd-outline" href="{{ route('bookings.receipt',$booking) }}"><i class="fas fa-print" aria-hidden="true"></i> Cetak struk reservasi</a></div>
    </div>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif

    <div class="rd-grid">
        <div class="rd-main">
            <section class="rv-card rd-overview">
                <div class="rd-section-head">
                    <div class="rd-title-group"><span class="rd-icon"><i class="far fa-calendar-alt" aria-hidden="true"></i></span><div><span class="rd-eyebrow">Layanan reservasi</span><h2>{{ $booking->service_name }}</h2></div></div>
                    <span class="rv-badge" data-status="{{ $booking->status }}"><span class="rd-status-dot"></span>{{ $statuses[$booking->status] ?? $booking->status }}</span>
                </div>
                <div class="rd-schedule">
                    <div><span class="rd-eyebrow">Tanggal reservasi</span><strong>{{ $booking->starts_at->locale('id')->translatedFormat('d F Y') }}</strong></div>
                    <div><span class="rd-eyebrow">Waktu layanan</span><strong>{{ $booking->starts_at->format('H:i') }} <span>–</span> {{ $booking->ends_at->format('H:i') }}</strong></div>
                </div>
                <dl class="rd-facts">
                    <div><dt><i class="far fa-user" aria-hidden="true"></i> Karyawan</dt><dd>{{ $booking->staff?->name ?? 'Tanpa preferensi' }}</dd></div>
                    <div><dt><i class="fas fa-door-open" aria-hidden="true"></i> Meja / fasilitas</dt><dd>{{ $booking->resource?->name ?? 'Tanpa fasilitas khusus' }}</dd></div>
                    <div><dt><i class="fas fa-users" aria-hidden="true"></i> Peserta / unit</dt><dd>{{ $booking->capacity_units }} <span class="rd-muted">unit</span></dd></div>
                </dl>
            </section>

            <section class="rv-card">
                <div class="rd-title-group rd-card-title"><span class="rd-icon rd-icon-neutral"><i class="far fa-user" aria-hidden="true"></i></span><div><h2>Informasi Pelanggan</h2><p>Kontak pelanggan untuk reservasi ini.</p></div></div>
                <div class="rd-customer">
                    <span class="rd-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($booking->customer_name ?? $booking->customer?->name ?? 'P',0,1)) }}</span>
                    <div><span class="rd-eyebrow">Nama pelanggan</span><strong>{{ $booking->customer_name ?? $booking->customer?->name }}</strong></div>
                </div>
                <dl class="rd-contact">
                    <div><dt>WhatsApp</dt><dd>{{ ($booking->customer_phone ?? $booking->customer?->phone) ?: 'Belum dicantumkan' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $booking->customer_email ?: 'Belum dicantumkan' }}</dd></div>
                </dl>
                <div class="rd-note"><span class="rd-eyebrow"><i class="far fa-sticky-note" aria-hidden="true"></i> Catatan pelanggan</span><p>{{ $booking->notes ?: 'Tidak ada catatan khusus.' }}</p></div>
            </section>

            <section class="rv-card rd-action-card">
                <h2>Kelola Reservasi</h2><p>Perbarui status atau sesuaikan jadwal pelanggan.</p>
                <div class="rv-actions">
                    @foreach($actions as $key=>$label)
                    <form method="post" action="{{ route('bookings.status',$booking) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $key }}">
                        <button type="submit" class="{{ in_array($key,['cancelled','no_show']) ? 'rd-danger' : ($key==='awaiting_payment' ? 'rd-outline' : '') }}">{{ $label }}</button>
                    </form>
                    @endforeach
                    @if(in_array($booking->status,['pending','awaiting_payment','confirmed']))
                    <a class="rv-btn rd-outline" href="{{ route('bookings.edit',$booking) }}"><i class="far fa-calendar-alt" aria-hidden="true"></i> Ubah jadwal</a>
                    @endif
                </div>
                @if(!$actions)<div class="rd-note"><p>Reservasi ini {{ mb_strtolower($statuses[$booking->status] ?? $booking->status) }}.</p></div>@endif
            </section>
        </div>

        <aside class="rd-payment">
            <section class="rv-card">
                <div class="rd-section-head">
                    <div class="rd-title-group"><span class="rd-icon rd-icon-neutral"><i class="fas fa-wallet" aria-hidden="true"></i></span><div><h2>Pembayaran</h2><span class="rd-muted">Metode Cash</span></div></div>
                    <span class="rd-payment-badge" data-payment="{{ $booking->payment_status }}">{{ $paymentLabel }}</span>
                </div>
                <div class="rd-amount"><span>Total layanan</span><strong>Rp {{ number_format($booking->total,0,',','.') }}</strong></div>
                <dl class="rd-payment-lines">
                    <div><dt>Wajib untuk konfirmasi</dt><dd>Rp {{ number_format($booking->required_payment,0,',','.') }}</dd></div>
                    <div><dt>Sudah dibayar</dt><dd>Rp {{ number_format($booking->paid,0,',','.') }}</dd></div>
                    <div class="rd-balance"><dt>Sisa tagihan</dt><dd>Rp {{ number_format($remaining,0,',','.') }}</dd></div>
                </dl>
                @if(!$booking->sale_id && !in_array($booking->status,['cancelled','no_show']) && $booking->paid<$booking->total)
                <form class="rd-payment-form" method="post" action="{{ route('bookings.cash',$booking) }}">
                    @csrf<input type="hidden" name="request_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                    <label for="rd-cash-amount">Nominal Cash diterima</label>
                    <div class="rd-money-input"><span>Rp</span><input id="rd-cash-amount" type="number" name="amount" min="0.01" max="{{ $remaining }}" step="0.01" placeholder="0" required></div>
                    <span class="rd-field-help">Masukkan nominal yang diterima dari pelanggan.</span>
                    <button type="submit" class="rd-success"><i class="fas fa-check" aria-hidden="true"></i> Konfirmasi Cash diterima</button>
                </form>
                @endif

                @if(!$booking->sale_id && in_array($booking->status,['confirmed','in_progress','completed']))
                <form class="rd-payment-form" method="post" action="{{ route('bookings.checkout',$booking) }}">
                    @csrf<input type="hidden" name="request_key" value="{{ \Illuminate\Support\Str::uuid() }}">
                    <h3>Checkout ke POS</h3>
                    <label for="rd-checkout-cash">Cash untuk sisa tagihan</label>
                    <div class="rd-money-input"><span>Rp</span><input id="rd-checkout-cash" type="number" name="cash_received" min="{{ $remaining }}" value="{{ $remaining }}" step="0.01" required></div>
                    <button type="submit">Checkout Cash & Selesaikan</button>
                    <span class="rd-field-help">DP dikreditkan otomatis. Total reservasi dicatat satu kali di POS tanpa mengurangi stok produk.</span>
                </form>
                @endif
                @if($booking->sale_id)
                <div class="rd-linked"><i class="fas fa-check-circle" aria-hidden="true"></i><div>Terhubung dengan transaksi POS<strong>#{{ $booking->sale_id }}</strong></div></div>
                @can('sales.view')<a class="rv-btn rd-outline rd-full" href="{{ route('sales.receipt',$booking->sale_id) }}"><i class="fas fa-print" aria-hidden="true"></i> Cetak struk POS</a>@endcan
                @elseif($booking->status==='completed')
                <form class="rd-payment-form" method="post" action="{{ route('bookings.pos',$booking) }}">
                    @csrf<label for="rd-sale-id">ID transaksi POS Cash lunas</label><input id="rd-sale-id" type="number" name="sale_id" min="1" required>
                    <button type="submit" class="rd-outline">Hubungkan transaksi POS</button>
                    <span class="rd-field-help">Gunakan transaksi pelanggan yang sama. Pastikan pembayaran yang sama tidak ditagih ulang.</span>
                </form>
                @endif
            </section>
            @if($booking->payments->isNotEmpty())
            <section class="rv-card rd-receipts"><h2>Riwayat Pembayaran</h2>
                @foreach($booking->payments as $receipt)
                <div class="rd-receipt"><span class="rd-receipt-icon"><i class="fas fa-check" aria-hidden="true"></i></span><div><strong>Cash diterima</strong><span>{{ $receipt->created_at->locale('id')->translatedFormat('d M Y') }} · {{ $receipt->created_at->format('H:i') }}</span></div><b>Rp {{ number_format($receipt->amount,0,',','.') }}</b></div>
                @endforeach
            </section>
            @endif
        </aside>
    </div>
</div>
@endsection

