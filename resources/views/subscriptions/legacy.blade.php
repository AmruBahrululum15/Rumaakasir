@extends('layouts.app')
@section('title', 'Paket & Langganan')
@section('content')
<section class="content-header"><div class="container-fluid"><h1>Paket & Langganan</h1><p class="text-muted">Kelola paket dan masa aktif toko Anda.</p></div></section>
<section class="content"><div class="container-fluid">
    @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @php($current = $business->effectiveSubscriptionPlan())
    @if($business->subscription_source === 'store_registration' && $business->subscription_status === 'pending_payment')
        <div class="alert alert-info">Premium Menunggu Pembayaran. Toko Anda aktif menggunakan paket Free sampai pembayaran Premium berhasil.</div>
    @endif
    <div class="card"><div class="card-body">
        <p class="text-muted mb-1">Paket Saat Ini</p><h2 class="h3">{{ strtoupper($current) }}</h2>
        <span class="badge badge-{{ $current === 'premium' ? 'success' : 'secondary' }}">{{ $business->subscription_source === 'doku' && $current === 'free' ? 'Premium expired · Free aktif' : 'Aktif' }}</span>
        @if($current === 'premium')
            <p class="mt-3 mb-1">Mulai: {{ $business->subscription_started_at?->format('d M Y H:i') ?? 'Tidak tercatat' }}</p>
            <p class="mb-1">Aktif sampai: {{ $business->subscription_ends_at?->format('d M Y H:i') ?? 'Tanpa batas waktu' }}</p>
            @if($business->subscription_ends_at)<p class="text-muted mb-0">Sisa masa aktif: {{ max(0, (int) ceil(now()->diffInDays($business->subscription_ends_at, false))) }} hari.</p>@endif
        @else<p class="mt-3 mb-0">Upgrade ke Premium untuk membuka fitur tambahan bagi toko Anda.</p>@endif
    </div></div>
    @if($pending)
        <div class="alert alert-info">{{ $pending->status === 'pending' ? 'Menunggu Pembayaran' : 'Pembuatan pembayaran belum selesai' }}: {{ $pending->plan_name }} {{ $pending->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }} — Rp{{ number_format($pending->amount, 0, ',', '.') }}.
            <a class="btn btn-sm btn-light ml-2" href="{{ route('subscriptions.result', $pending->order_id) }}">Lihat pembayaran pending</a>
        </div>
    @endif
    @if($current === 'premium' && ! $business->subscription_ends_at && $business->subscription_source !== 'doku')
        <div class="alert alert-info">Premium tanpa batas waktu dari pengelola platform sudah aktif. Perpanjangan tidak diperlukan.</div>
    @elseif($premium->is_active)
    <div class="row">
        @foreach(['monthly' => ['Bulanan', 'bulan', $premium->price_monthly], 'yearly' => ['Tahunan', 'tahun', $premium->price_yearly]] as $cycle => [$label, $unit, $price])
            <div class="col-md-6"><div class="card h-100"><div class="card-body">
                <h2 class="h5">{{ $premium->name }} {{ $label }}</h2>
                <p class="h3 my-3">Rp{{ number_format($price, 0, ',', '.') }} <small class="h6 text-muted">/ {{ $unit }}</small></p>
                <p class="text-muted">{{ $cycle === 'monthly' ? '1 bulan' : '12 bulan' }} masa aktif. Perpanjangan ditambahkan ke akhir masa aktif saat ini.</p>
                <p>{{ $current === 'premium' ? 'Perpanjang Premium' : 'Aktifkan Premium' }}</p>
                <p class="small text-muted">Pembayaran melalui {{ config('doku.subscription_gateway') === 'midtrans' ? 'Midtrans' : 'DOKU' }}. Pilih QRIS atau Virtual Account yang tersedia di halaman resmi {{ config('doku.subscription_gateway') === 'midtrans' ? 'Midtrans' : 'DOKU' }}.</p>
                <form method="GET" action="{{ route('subscriptions.payment-options') }}"><input type="hidden" name="billing_cycle" value="{{ $cycle }}"><button type="submit" class="btn btn-warning btn-block" @disabled($pending)>{{ $pending ? 'Menunggu Pembayaran' : 'Lanjut ke Pembayaran' }}</button></form>
            </div></div></div>
        @endforeach
    </div>
    @else<div class="alert alert-info">Pembelian Premium sedang tidak tersedia.</div>@endif
    <div class="card mt-4"><div class="card-body"><h2 class="h5">Manfaat {{ $premium->name }}</h2><ul class="mb-0">
        @foreach(['products' => 'Produk', 'users' => 'Pengguna', 'tables' => 'Meja'] as $key => $label)<li>{{ $label }}: {{ $premium->{'max_'.$key} ?? 'Unlimited' }}</li>@endforeach
        @foreach(['export_excel' => 'Export Excel', 'export_pdf' => 'Export PDF', 'custom_branding' => 'Custom Branding', 'ai_agent' => 'AI Agent', 'qr_order' => 'Meja & QR Order'] as $key => $label)
            @if($premium->$key)<li>{{ $label }}</li>@endif
        @endforeach
    </ul></div></div>
    <div class="card"><div class="card-header"><h2 class="card-title">Riwayat Langganan</h2></div>
        @include('subscriptions.history', ['platform' => false])
    </div>
</div></section>
@endsection
