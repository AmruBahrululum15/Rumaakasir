@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm"><div class="card-body text-center py-5">
        <h4>Menyiapkan pembayaran {{ strtoupper($sale->payment_method) }}</h4>
        <p class="text-muted mb-4">Transaksi {{ $sale->code }} dibuat oleh {{ $cashier }}. Selesaikan pembayaran melalui Midtrans.</p>
        <button id="pay-button" class="btn btn-primary px-4">Lanjutkan Pembayaran</button>
        <a class="btn btn-outline-secondary ml-2" href="{{ route('sales.show', $sale) }}">Detail Transaksi</a>
    </div></div>
</div>
<script src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
<script>document.getElementById('pay-button').addEventListener('click', function () { snap.pay(@json($snapToken), { onPending: function(){ window.location=@json(route('sales.show', $sale)); }, onSuccess: function(){ window.location=@json(route('sales.receipt', $sale)); }, onError: function(){ window.location=@json(route('sales.show', $sale)); } }); });</script>
@endsection
