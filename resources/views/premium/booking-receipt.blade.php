<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Struk Reservasi · {{ $booking->code ?? 'Booking #'.$booking->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/reservation-receipt.css') }}">
    <style>@media print { @page { size: {{ $width }}mm auto; margin: 0; } }</style>
</head>
@php
    $remaining=max(0,$booking->total-$booking->paid);
    $paymentLabel=['paid'=>'Lunas','partial'=>'Sebagian dibayar','unpaid'=>'Belum dibayar'][$booking->payment_status]??$booking->payment_status;
    $address=$business->address ?: \App\Models\Setting::getValue('business_address');
    $phone=$business->phone ?: \App\Models\Setting::getValue('business_phone');
@endphp
<body data-width="{{ $width }}">
    <header class="receipt-toolbar">
        <div><h1>Struk Reservasi</h1><p>Pratinjau struk · gunakan printer thermal atau simpan sebagai PDF.</p></div>
        <div class="receipt-actions">
            <nav aria-label="Ukuran kertas"><a href="{{ route('bookings.receipt',[$booking,'width'=>80]) }}" @if($width==='80') aria-current="page" @endif>80 mm</a><a href="{{ route('bookings.receipt',[$booking,'width'=>58]) }}" @if($width==='58') aria-current="page" @endif>58 mm</a></nav>
            <a class="receipt-back" href="{{ route('bookings.show',$booking) }}">Kembali</a>
            <button type="button" onclick="window.print()">Cetak struk</button>
        </div>
    </header>
    <main class="receipt" aria-label="Isi struk reservasi">
        <div class="receipt-brand"><h2>{{ $business->name }}</h2>@if($address)<p>{{ $address }}</p>@endif @if($phone)<p>Telp. {{ $phone }}</p>@endif</div>
        <div class="receipt-heading"><strong>STRUK RESERVASI</strong><span>{{ $booking->code ?? 'Booking #'.$booking->id }}</span></div>
        <dl class="receipt-facts">
            <div><dt>Dibuat</dt><dd>{{ $booking->created_at?->locale('id')->translatedFormat('d M Y H:i') ?? '—' }}</dd></div>
            <div><dt>Pelanggan</dt><dd>{{ $booking->customer_name ?? $booking->customer?->name ?? '—' }}</dd></div>
            @if($booking->customer_phone ?? $booking->customer?->phone)<div><dt>WhatsApp</dt><dd>{{ $booking->customer_phone ?? $booking->customer?->phone }}</dd></div>@endif
            <div><dt>Status</dt><dd>{{ $statuses[$booking->status] ?? $booking->status }}</dd></div>
        </dl>
        <section class="receipt-service">
            <h3>{{ $booking->service_name }}</h3>
            <dl class="receipt-facts">
                <div><dt>Tanggal</dt><dd>{{ $booking->starts_at->locale('id')->translatedFormat('d M Y') }}</dd></div>
                <div><dt>Jam</dt><dd>{{ $booking->starts_at->format('H:i') }} – {{ $booking->ends_at->format('H:i') }}</dd></div>
                <div><dt>Peserta / unit</dt><dd>{{ $booking->capacity_units }}</dd></div>
                @if($booking->staff)<div><dt>Karyawan</dt><dd>{{ $booking->staff->name }}</dd></div>@endif
                @if($booking->resource)<div><dt>Fasilitas</dt><dd>{{ $booking->resource->name }}</dd></div>@endif
            </dl>
        </section>
        <section class="receipt-payment">
            <div class="receipt-total"><span>Total layanan</span><strong>Rp {{ number_format($booking->total,0,',','.') }}</strong></div>
            <dl class="receipt-facts">
                <div><dt>Wajib konfirmasi</dt><dd>Rp {{ number_format($booking->required_payment,0,',','.') }}</dd></div>
                <div><dt>Sudah dibayar</dt><dd>Rp {{ number_format($booking->paid,0,',','.') }}</dd></div>
                <div class="receipt-balance"><dt>Sisa tagihan</dt><dd>Rp {{ number_format($remaining,0,',','.') }}</dd></div>
                <div><dt>Metode</dt><dd>Cash</dd></div>
                <div><dt>Pembayaran</dt><dd>{{ $paymentLabel }}</dd></div>
                @if($booking->sale_id)<div><dt>Transaksi POS</dt><dd>#{{ $booking->sale_id }}</dd></div>@endif
            </dl>
        </section>
        @if($booking->payments->isNotEmpty())<section class="receipt-history"><h3>Penerimaan Cash</h3>@foreach($booking->payments as $payment)<div><span>{{ $payment->created_at->locale('id')->translatedFormat('d M Y H:i') }}</span><strong>Rp {{ number_format($payment->amount,0,',','.') }}</strong></div>@endforeach</section>@endif
        @if($booking->notes)<section class="receipt-note"><h3>Catatan</h3><p>{{ $booking->notes }}</p></section>@endif
        <footer><strong>{{ $booking->payment_status==='paid' && $remaining<=0 ? 'PEMBAYARAN LUNAS' : 'BUKAN BUKTI PELUNASAN' }}</strong><p>Simpan struk ini dan tunjukkan saat kedatangan.</p><p>Terima kasih telah melakukan reservasi.</p><small>Dicetak {{ now('Asia/Jakarta')->locale('id')->translatedFormat('d M Y H:i') }} WIB<br>Rumaakasir</small></footer>
    </main>
</body>
</html>

