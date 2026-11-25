@extends('portal.layout')
@section('title', 'Detail Pesanan')
@section('content')
<a class="back-link" href="{{ route('customer.orders') }}">← Kembali ke pesanan saya</a>
<section class="hero"><h1>Detail Pesanan</h1><span class="order-reference">{{ $sale->code }}</span><p>{{ $business->name }} · {{ $sale->created_at->format('d M Y H:i') }}</p></section>
<section class="card"><h2>Informasi pesanan</h2>
<dl class="order-facts">
<div><dt>Status pesanan</dt><dd><span class="badge" data-status="{{ $sale->order_status ?? 'pending' }}">{{ ['pending'=>'Menunggu konfirmasi','confirmed'=>'Diterima','processing'=>'Diproses','completed'=>'Selesai','cancelled'=>'Dibatalkan','rejected'=>'Ditolak'][$sale->order_status ?? 'pending'] ?? $sale->order_status }}</span></dd></div>
<div><dt>Status pembayaran</dt><dd><span class="badge" data-status="{{ $sale->payment_status }}">{{ ['PAID'=>'Lunas','CASH_PENDING'=>'Menunggu pembayaran tunai','PENDING'=>'Menunggu pembayaran','CANCELLED'=>'Dibatalkan','FAILED'=>'Gagal','EXPIRED'=>'Kedaluwarsa','REFUNDED'=>'Dikembalikan','VOIDED'=>'Dibatalkan'][$sale->payment_status] ?? $sale->payment_status }}</span></dd></div>
<div><dt>Metode pembayaran</dt><dd>{{ ['cash'=>'Cash','qris'=>'QRIS','va'=>'Virtual Account'][$sale->payment_method] ?? strtoupper($sale->payment_method) }}</dd></div>
<div><dt>Waktu pesanan</dt><dd>{{ $sale->created_at->format('d M Y H:i') }}</dd></div>
</dl>
<div class="order-items"><h2>Ringkasan Pesanan</h2>@foreach($sale->saleDetails as $detail)<div class="order-item"><div><strong>{{ $detail->product?->name ?? 'Produk' }}</strong><small>{{ $detail->quantity }} item</small></div><span>Rp {{ number_format($detail->subtotal,0,',','.') }}</span></div>@endforeach</div>
<div class="checkout-total"><span>Total pembayaran</span><strong class="price">Rp {{ number_format($sale->total,0,',','.') }}</strong></div></section>
@if(in_array($sale->payment_status,['PENDING','CASH_PENDING']))
<section class="card order-payment" style="margin-top:20px"><h2>Pembayaran</h2>
@if($sale->payment_method==='cash')<p>Pembayaran dilakukan secara tunai. Bayar kepada kasir saat pesanan diterima atau diambil.</p>
@elseif($payment?->provider==='manual_qris')<p>Scan QRIS {{ $business->name }}. Total Rp{{ number_format($sale->total,0,',','.') }} untuk {{ $sale->code }}.</p><img src="{{ route('customer.orders.qris',$sale->id) }}" alt="QRIS {{ $business->name }}" style="max-width:100%;width:320px"><p>Menunggu pembayaran dan verifikasi kasir. Halaman ini tidak menandai pembayaran lunas otomatis.</p>
@elseif($payment?->provider==='midtrans')
@if($payment->redirect_url)<a class="btn" href="{{ $payment->redirect_url }}" rel="noreferrer">{{ $sale->payment_method==='va'?'Pilih Bank & Lihat Nomor VA':'Tampilkan QRIS' }}</a><p>Nomor VA/QRIS, batas waktu, dan instruksi pembayaran ditampilkan oleh Midtrans. Kembali ke halaman ini untuk memeriksa status.</p>
@elseif($payment->gateway_state==='new')<form method="post" action="{{ route('customer.orders.pay',$sale->id) }}">@csrf<button>{{ $sale->payment_method==='va'?'Buat Pembayaran VA':'Buat Pembayaran QRIS' }}</button></form>
@else<p>Permintaan pembayaran sedang diverifikasi. Hubungi toko bila instruksi belum tersedia; jangan membuat pembayaran ulang.</p>@endif
@foreach($payment->instructions['va_numbers']??[] as $va)<p>Bank: {{ strtoupper($va['bank']??'') }}<br>Nomor VA: {{ $va['va_number']??'' }}</p>@endforeach
@if(isset($payment->instructions['permata_va_number']))<p>Permata VA: {{ $payment->instructions['permata_va_number'] }}</p>@endif
@if(isset($payment->instructions['bill_key']))<p>Mandiri: {{ $payment->instructions['biller_code']??'' }} / {{ $payment->instructions['bill_key'] }}</p>@endif
@if(isset($payment->instructions['expiry_time']))<p>Batas pembayaran: {{ $payment->instructions['expiry_time'] }}</p>@endif
@endif</section>@endif
<div class="order-actions"><a class="btn secondary" href="{{ route('customer.orders.show',$sale->id) }}">Perbarui status</a><a class="btn secondary" href="{{ route('customer.orders') }}">Semua pesanan</a></div>
@endsection
