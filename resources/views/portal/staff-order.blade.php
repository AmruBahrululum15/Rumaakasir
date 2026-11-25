@extends('layouts.app')
@section('title', 'Detail Pesanan')
@section('css')
<style>
.customer-order-detail{max-width:1200px;margin:0 auto;padding:24px 20px 40px;color:#253247}
.customer-order-detail .order-heading{margin-bottom:24px}
.customer-order-detail h1{font-size:28px;font-weight:750;margin:0 0 10px;letter-spacing:-.6px}
.customer-order-detail .order-code{display:inline-block;max-width:100%;padding:6px 10px;border:1px solid #e4e8ed;border-radius:8px;background:#f8fafc;color:#647184;font-size:12px;overflow-wrap:anywhere}
.customer-order-detail .order-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,1fr);gap:22px;align-items:start}
.customer-order-detail .order-column{display:grid;gap:22px;min-width:0}
.customer-order-detail .order-panel{min-width:0;padding:24px;background:#fff;border:1px solid #e6eaf0;border-radius:16px;box-shadow:0 3px 14px #25324705}
.customer-order-detail h2{font-size:16px;font-weight:700;margin:0 0 22px;color:#253247}
.customer-order-detail .order-facts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:22px 24px;margin:0}
.customer-order-detail dt{font-size:12px;color:#738094;font-weight:500;margin-bottom:7px}
.customer-order-detail dd{font-size:14px;font-weight:600;margin:0;overflow-wrap:anywhere}
.customer-order-detail .order-fact-wide{grid-column:1/-1}
.customer-order-detail .order-badge{display:inline-flex;padding:6px 10px;border-radius:8px;font-size:12px;font-weight:600;line-height:1.5;background:#f1f4f8;color:#526176;max-width:100%;overflow-wrap:anywhere}
.customer-order-detail .order-badge--amber{background:#fff4db;color:#936010}
.customer-order-detail .order-badge--green{background:#e8f7ee;color:#237449}
.customer-order-detail .order-badge--red{background:#fff0ee;color:#b43838}
.customer-order-detail .order-badge--blue{background:#edf4ff;color:#34679f}
.customer-order-detail .order-items{width:100%;border-collapse:collapse;table-layout:fixed;font-size:14px}
.customer-order-detail .order-items th{padding:12px 10px;background:#f8fafc;color:#748094;font-size:12px;font-weight:600;border-bottom:1px solid #e6eaf0;text-align:right}
.customer-order-detail .order-items td{padding:18px 10px;border-bottom:1px solid #edf0f4;text-align:right;overflow-wrap:anywhere;font-variant-numeric:tabular-nums}
.customer-order-detail .order-items th:first-child,.customer-order-detail .order-items td:first-child{text-align:left;width:40%;font-weight:600}
.customer-order-detail .order-items th:nth-child(2){width:12%}
.customer-order-detail .order-items tr:last-child td{border-bottom:0}
.customer-order-detail .order-total-label{font-size:13px;color:#738094;margin:0 0 8px}
.customer-order-detail .order-total{font-size:30px;font-weight:800;letter-spacing:-.6px;color:#d65313;overflow-wrap:anywhere;font-variant-numeric:tabular-nums}
.customer-order-detail .order-payment-summary{display:flex;gap:8px;flex-wrap:wrap;margin-top:18px;padding-top:18px;border-top:1px solid #edf0f4}
.customer-order-detail .order-actions{display:flex;flex-direction:column;gap:12px;margin:0}
.customer-order-detail .order-button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:11px 16px;border:1px solid transparent;border-radius:10px;font-size:14px;line-height:1.5;font-weight:600;white-space:normal;text-align:center;cursor:pointer}
.customer-order-detail .order-button--primary{background:#e65d17;color:white;border-color:#e65d17}
.customer-order-detail .order-button--primary:hover{background:#c94b0c}
.customer-order-detail .order-button--green{background:#238454;color:white;border-color:#238454}
.customer-order-detail .order-button--green:hover{background:#196c42}
.customer-order-detail .order-button--danger{background:#fff;color:#b63c3c;border-color:#efc8c8}
.customer-order-detail .order-button--danger:hover{background:#fff3f2}
.customer-order-detail .order-back{display:flex;margin-top:16px;color:#637084;background:#f7f9fb;border-color:#e7ebf0;text-decoration:none}
.customer-order-detail .order-back:hover{background:#eef2f6;color:#253247}
.customer-order-detail .order-button:focus-visible{outline:3px solid #fdba74;outline-offset:3px}
.customer-order-detail .order-notice{font-size:13px;color:#738094;line-height:1.6;margin:0}
@media(max-width:767px){
 .customer-order-detail{padding:20px 12px 32px}
 .customer-order-detail .order-grid{grid-template-columns:minmax(0,1fr);gap:18px}
 .customer-order-detail .order-column{gap:18px}
 .customer-order-detail .order-panel{padding:20px 16px}
 .customer-order-detail h1{font-size:24px}
 .customer-order-detail .order-facts{grid-template-columns:minmax(0,1fr);gap:18px}
 .customer-order-detail .order-items thead{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%)}
 .customer-order-detail .order-items,.customer-order-detail .order-items tbody{display:block;width:100%}
 .customer-order-detail .order-items tr{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;padding:16px 0;border-bottom:1px solid #edf0f4}
 .customer-order-detail .order-items tr:first-child{padding-top:0}
 .customer-order-detail .order-items tr:last-child{border:0;padding-bottom:0}
 .customer-order-detail .order-items td{display:block;padding:0;border:0;text-align:left;font-size:13px}
 .customer-order-detail .order-items td:first-child{grid-column:1/-1;width:auto;font-size:14px}
 .customer-order-detail .order-items td[data-label]:before{content:attr(data-label);display:block;margin-bottom:6px;font-size:11px;font-weight:500;color:#738094}
}
</style>
@endsection
@section('content')
@php
    $orderStatus = $order->order_status ?? 'pending';
    $orderLabels = ['pending'=>'Pending', 'confirmed'=>'Diterima', 'processing'=>'Diproses', 'completed'=>'Selesai', 'cancelled'=>'Dibatalkan'];
    $orderTone = match ($orderStatus) { 'confirmed', 'completed'=>'green', 'cancelled'=>'red', 'processing'=>'blue', default=>'amber' };
    $paymentTone = match ($order->payment_status) { 'PAID'=>'green', 'CANCELLED', 'FAILED', 'EXPIRED'=>'red', 'PENDING', 'CASH_PENDING'=>'amber', default=>'' };
    $methodLabel = ['cash'=>'Cash', 'qris'=>'QRIS', 'va'=>'Virtual Account'][$order->payment_method] ?? strtoupper($order->payment_method);
@endphp
<div class="customer-order-detail">
    <header class="order-heading">
        <h1>Detail Pesanan</h1>
        <span class="order-code">{{ $order->code }}</span>
    </header>
    <div class="order-grid">
        <div class="order-column">
            <section class="order-panel" aria-labelledby="order-info-heading">
                <h2 id="order-info-heading">Informasi Pesanan</h2>
                <dl class="order-facts">
                    <div class="order-fact-wide"><dt>Nomor Order</dt><dd>{{ $order->code }}</dd></div>
                    <div><dt>Waktu Pesanan</dt><dd>{{ $order->created_at->format('d M Y H:i') }}</dd></div>
                    <div><dt>Status Pesanan</dt><dd><span class="order-badge order-badge--{{ $orderTone }}">{{ $orderLabels[$orderStatus] ?? $orderStatus }}</span></dd></div>
                    <div><dt>Metode Pembayaran</dt><dd><span class="order-badge">{{ $methodLabel }}</span></dd></div>
                    <div><dt>Status Pembayaran</dt><dd><span class="order-badge order-badge--{{ $paymentTone }}">{{ $order->payment_status }}</span></dd></div>
                </dl>
            </section>
            <section class="order-panel" aria-labelledby="customer-info-heading">
                <h2 id="customer-info-heading">Informasi Pelanggan</h2>
                <dl class="order-facts"><div><dt>Pelanggan</dt><dd>{{ $order->customer?->name ?? '—' }}</dd></div></dl>
            </section>
            <section class="order-panel" aria-labelledby="order-items-heading">
                <h2 id="order-items-heading">Daftar Item</h2>
                <table class="order-items" aria-labelledby="order-items-heading">
                    <thead><tr><th scope="col">Nama Produk</th><th scope="col">Qty</th><th scope="col">Harga</th><th scope="col">Subtotal</th></tr></thead>
                    <tbody>
                    @foreach($order->saleDetails as $detail)
                        <tr>
                            <td>{{ $detail->product?->name ?? 'Produk' }}</td>
                            <td data-label="Qty">{{ $detail->quantity }}</td>
                            <td data-label="Harga">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td data-label="Subtotal">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </section>
        </div>
        <div class="order-column">
            <section class="order-panel" aria-labelledby="order-payment-heading">
                <h2 id="order-payment-heading">Ringkasan Pembayaran</h2>
                <p class="order-total-label">Total Pesanan</p>
                <div class="order-total">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                <div class="order-payment-summary"><span class="order-badge">{{ $methodLabel }}</span><span class="order-badge order-badge--{{ $paymentTone }}">{{ $order->payment_status }}</span></div>
            </section>
            <section class="order-panel" aria-labelledby="order-actions-heading">
                <h2 id="order-actions-heading">Aksi Pesanan</h2>
                @if(auth()->user()->can('sales.create'))
                    <form method="post" class="order-actions" action="{{ route('portal-orders.update', $order->id) }}">
                        @csrf @method('patch')
                        @php($next=['pending'=>['confirmed','Terima'],'confirmed'=>['processing','Proses'],'processing'=>['completed','Selesai']][$order->order_status??'pending']??null)
                        @if($next && ($next[0]!=='completed'||$order->payment_status==='PAID'))
                            <button class="order-button order-button--primary" name="action" value="{{ $next[0] }}">{{ $next[1] }}</button>
                        @endif
                        @if(in_array($order->payment_status,['PENDING','CASH_PENDING']))
                            @if($order->payment_method==='cash'||$payment?->provider==='manual_qris')
                                <button class="order-button order-button--green" name="action" value="paid" onclick="return confirm('Dana sudah benar-benar diterima?')">Konfirmasi {{ strtoupper($order->payment_method) }} diterima</button>
                            @endif
                            @if($payment?->provider!=='midtrans'||$payment->gateway_state==='new')
                                <button class="order-button order-button--danger" name="action" value="cancelled">Tolak / Batalkan</button>
                            @else
                                <p class="order-notice">Pembayaran gateway telah dimulai. Pembatalan menunggu konfirmasi gateway.</p>
                            @endif
                        @endif
                    </form>
                @endif
                <a class="order-button order-back" href="{{ route('portal-orders.index') }}">Kembali ke daftar</a>
            </section>
        </div>
    </div>
</div>
@endsection
