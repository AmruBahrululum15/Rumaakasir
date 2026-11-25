@extends('layouts.app')
@section('title', 'Paket & Langganan')
@section('css')<link rel="stylesheet" href="{{ asset('css/subscription.css') }}">@endsection
@section('content')
@php
    $access = app(\App\Services\PackageAccess::class);
    $current = $access->effectiveCode($business);
    $typeLabel = app(\App\Services\BusinessModules::class)->label($business->business_type);
    $expired = $business->subscription_plan === 'premium' && $business->subscription_ends_at?->isPast();
    $lifetime = $current === 'premium' && !$business->subscription_ends_at && $business->subscription_source !== 'doku';
    $cta = $current === 'premium' ? 'Perpanjang Premium' : ($expired ? 'Aktifkan Premium Kembali' : 'Upgrade ke Premium');
    $saving = max(0, 12 * $premium->price_monthly - $premium->price_yearly);
    $status = $current === 'premium' ? 'Aktif' : ($pending || $business->subscription_status === 'pending_payment' ? 'Menunggu Pembayaran' : ($expired ? 'Berakhir' : ($business->subscription_status === 'cancelled' ? 'Dibatalkan' : 'Free')));
@endphp
<div class="subscription-page">
<header class="subscription-header"><div><p class="subscription-eyebrow">PAKET & FITUR</p><h1>Paket & Langganan</h1><p class="text-muted">Gunakan fitur yang sesuai untuk mengembangkan bisnis Anda.</p></div><span class="subscription-type"><i class="fas fa-store" aria-hidden="true"></i> {{ $typeLabel }}</span></header>
@if(session('info'))<div class="alert alert-info" role="status">{{ session('info') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<p class="mb-0">{{ $error }}</p>@endforeach</div>@endif
@if($expired)<div class="subscription-notice"><strong>Paket Premium Anda telah berakhir.</strong><p class="mb-0">Toko tetap dapat digunakan dengan fitur Free.</p></div>@endif
@if($business->subscription_source === 'store_registration' && $business->subscription_status === 'pending_payment')<div class="subscription-notice">Premium Menunggu Pembayaran. Toko tetap menggunakan Free sampai pembayaran berhasil.</div>@endif
<div class="subscription-grid">
<section class="subscription-card current-plan"><div class="subscription-card-heading"><span class="subscription-icon"><i class="fas fa-{{ $current === 'premium' ? 'crown' : 'box' }}" aria-hidden="true"></i></span><span class="subscription-status">{{ $status }}</span></div><p class="subscription-eyebrow mt-4">PAKET SAAT INI</p><h2>{{ strtoupper($current) }}</h2><p class="text-muted">{{ $current === 'premium' ? 'Premium '.$typeLabel : 'Paket dasar untuk operasional bisnis Anda.' }}</p>
@if($current === 'premium')<dl class="subscription-facts"><div><dt>Mulai</dt><dd>{{ $business->subscription_started_at?->format('d M Y') ?? 'Dikelola platform' }}</dd></div><div><dt>Berakhir</dt><dd>{{ $business->subscription_ends_at?->format('d M Y') ?? 'Tanpa batas waktu' }}</dd></div>@if($business->subscription_ends_at)<div><dt>Sisa masa aktif</dt><dd>{{ max(0, (int) ceil(now()->diffInDays($business->subscription_ends_at, false))) }} hari</dd></div>@endif
@if($latestPaid)<div><dt>Periode pembayaran terakhir</dt><dd>{{ $latestPaid->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}</dd></div>@endif</dl>@else<p class="small text-muted">Data toko tetap tersimpan saat paket berubah. Batas paket berlaku untuk penambahan data baru.</p>@endif
@if($lifetime)<p class="subscription-notice mb-0">Premium tanpa batas waktu dari pengelola platform sudah aktif. Perpanjangan tidak diperlukan.</p>@endif
</section>
<section class="subscription-card premium-offer" id="premium-offer"><div class="subscription-card-heading"><p class="subscription-eyebrow mb-0"><i class="fas fa-crown" aria-hidden="true"></i> PREMIUM</p><span class="text-muted small">Untuk {{ $typeLabel }}</span></div><h2 class="h4 mt-3">{{ $premium->name }} {{ $typeLabel }}</h2>
<form method="GET" action="{{ route('subscriptions.payment-options') }}">
<fieldset class="billing-options"><legend class="sr-only">Periode pembayaran</legend><label><input type="radio" name="billing_cycle" value="monthly" checked> Bulanan</label><label><input type="radio" name="billing_cycle" value="yearly"> Tahunan</label></fieldset>
<div data-billing-panel="monthly"><p class="subscription-price">Rp{{ number_format($premium->price_monthly, 0, ',', '.') }} <small>/ bulan</small></p><p class="small text-muted">1 bulan masa aktif</p></div>
<div data-billing-panel="yearly" hidden><p class="subscription-price">Rp{{ number_format($premium->price_yearly, 0, ',', '.') }} <small>/ tahun</small></p><p class="small text-muted">12 bulan masa aktif @if($saving > 0)<span class="subscription-saving">Hemat Rp{{ number_format($saving, 0, ',', '.') }} ({{ (int) floor(100 * $saving / max(1, 12 * $premium->price_monthly)) }}%)</span>@endif</p></div>
@if(!$premium->is_active)<p class="subscription-notice">Pembelian Premium sedang tidak tersedia.</p>@elseif(!$lifetime)<p class="h6">{{ $cta }}</p><p class="small text-muted">Pembayaran melalui {{ config('doku.subscription_gateway') === 'midtrans' ? 'Midtrans' : 'DOKU' }}. Pilih QRIS atau Virtual Account yang tersedia di halaman resmi {{ config('doku.subscription_gateway') === 'midtrans' ? 'Midtrans' : 'DOKU' }}.</p><button type="submit" class="btn subscription-primary btn-block" @disabled($pending)>{{ $pending ? 'Menunggu Pembayaran' : 'Lanjut ke Pembayaran' }}</button><p class="small text-muted mt-3 mb-0">Perpanjangan ditambahkan ke akhir masa aktif. Premium aktif setelah pembayaran terverifikasi.</p>@endif
</form>
<h3 class="h6 mt-4">Tersedia sekarang</h3>
@foreach(collect($comparison)->filter(fn ($row) => $row['premium'] !== false && $row['premium'] !== $row['free'])->groupBy('group') as $benefitGroup=>$benefitRows)
<h4 class="subscription-eyebrow mt-3">{{ $benefitGroup }}</h4><ul class="subscription-benefits">
@foreach($benefitRows as $row)<li><i class="fas fa-check-circle" aria-hidden="true"></i><span>{{ $row['label'] }}{{ is_bool($row['premium']) ? '' : ': '.$row['premium'] }}</span></li>@endforeach
</ul>
@endforeach
</section></div>
@if($pending)<section class="subscription-card pending-payment"><div><p class="subscription-eyebrow">PEMBAYARAN MENUNGGU</p><h2 class="h5">{{ $pending->plan_name }} {{ $pending->billing_cycle === 'monthly' ? 'Bulanan' : 'Tahunan' }}</h2><p class="subscription-order">{{ $pending->order_id }}</p><strong>Rp{{ number_format($pending->amount, 0, ',', '.') }}</strong><p class="small text-muted mt-2">{{ $pending->status === 'pending' ? 'Menunggu Pembayaran' : 'Pembuatan pembayaran sedang diverifikasi' }}</p></div><div class="pending-actions">@if($pending->displayStatus() === 'pending' && $pending->validPaymentUrl())<a class="btn subscription-primary" href="{{ $pending->payment_url }}">Lanjutkan Pembayaran</a>@endif<a class="btn btn-outline-secondary" href="{{ route('subscriptions.result', $pending->order_id) }}">Detail Pembayaran</a></div></section>@endif
<section class="subscription-card free-summary"><div><p class="subscription-eyebrow">FREE</p><h2 class="h4">Rp0</h2><p class="text-muted">Tepat untuk memulai operasional bisnis.</p></div><ul class="subscription-benefits">@foreach(collect($comparison)->filter(fn ($row) => $row['free'] !== false)->take(5) as $row)<li><i class="fas fa-check-circle" aria-hidden="true"></i>{{ $row['label'] }}{{ is_bool($row['free']) ? '' : ': '.$row['free'] }}</li>@endforeach</ul></section>
<section class="subscription-card"><h2 class="h5">Bandingkan paket untuk {{ $typeLabel }}</h2><p class="text-muted small">Fitur yang tersedia saat ini. Akses pengguna tetap mengikuti permission akun.</p><div class="table-responsive"><table class="table subscription-comparison"><thead><tr><th>Fitur</th><th>Free</th><th>Premium</th></tr></thead><tbody>
@foreach(collect($comparison)->groupBy('group') as $group => $rows)<tr class="comparison-group"><th colspan="3">{{ $group }}</th></tr>@foreach($rows as $row)<tr><td>{{ $row['label'] }}</td>@foreach(['free','premium'] as $code)<td>@if(is_bool($row[$code]))<span class="{{ $row[$code] ? 'feature-yes' : 'text-muted' }}">{{ $row[$code] ? 'Termasuk' : 'Tidak termasuk' }}</span>@else{{ $row[$code] }}@endif</td>@endforeach</tr>@endforeach
@endforeach
</tbody></table></div></section>
<section class="subscription-card subscription-history"><h2 class="h5">Riwayat Langganan</h2><p class="small text-muted">Pembayaran dan masa aktif paket toko Anda.</p>@include('subscriptions.history', ['platform'=>false, 'friendlyStatuses'=>true])</section>
</div>
@endsection
@push('scripts')<script>document.querySelectorAll('.billing-options input').forEach(input=>input.addEventListener('change',()=>{document.querySelectorAll('[data-billing-panel]').forEach(panel=>panel.hidden=panel.dataset.billingPanel!==input.value)}));</script>@endpush
