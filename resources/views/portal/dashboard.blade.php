@extends('portal.layout')
@section('title', 'Beranda')
@section('content')
<section class="welcome">
    <span class="badge">Selamat datang di Rumaakasir</span>
    <h1>Halo, {{ $account->name }}</h1>
    <p>Temukan toko favorit, pesan kebutuhan Anda, dan nikmati benefit pelanggan dalam satu tempat.</p>
    <form class="search" action="{{ route('customer.stores') }}"><input name="q" aria-label="Cari toko atau layanan" placeholder="Cari toko, makanan, atau layanan..."><button type="submit">Jelajahi Toko <span aria-hidden="true">→</span></button></form>
</section>
<nav class="quick-actions" aria-label="Akses cepat pelanggan">
    @foreach(['orders'=>['Pesanan Saya','Lihat status pesanan','▤'],'bookings'=>['Reservasi','Jadwal layanan Anda','◷'],'loyalty'=>['Loyalty Saya','Poin dari toko favorit','☆'],'vouchers'=>['Voucher','Benefit untuk belanja','%']] as $key=>[$label,$description,$icon])
        <a class="quick-action" href="{{ route('customer.'.$key) }}"><span aria-hidden="true">{{ $icon }}</span><div><strong>{{ $label }}</strong><small>{{ $description }}</small></div></a>
    @endforeach
</nav>
<section class="section-heading"><div><h2>Toko Terdekat dari Anda</h2><p>Temukan tempat baru di sekitar Anda.</p></div><a class="btn secondary" href="{{ route('customer.stores',['city'=>$city]) }}" data-nearby-link>Gunakan lokasi saya</a></section>
<p class="location-feedback" role="status"></p>
<div class="section-heading"><h2>Kategori</h2></div>
<div class="category-strip">@foreach(['cafe'=>'Cafe','restaurant'=>'Restoran','barbershop'=>'Barber','salon'=>'Salon','laundry'=>'Laundry','bengkel'=>'Bengkel','retail'=>'Retail','fashion'=>'Fashion'] as $key=>$label)<a href="{{ route('customer.stores',['type'=>$key]) }}">{{ $label }}</a>@endforeach</div>
<div class="section-heading"><div><h2>Rekomendasi Toko</h2><p>Pilihan toko dengan layanan online aktif.</p></div><a href="{{ route('customer.stores') }}">Lihat semua →</a></div>
<div class="grid">@forelse($stores as $store)@include('portal.partials.store-card')@empty<div class="card empty">Belum ada toko dengan layanan online aktif.</div>@endforelse</div>
<div class="dashboard-columns">
    <section><div class="section-heading"><h2>Reservasi Mendatang</h2><a href="{{ route('customer.bookings') }}">Lihat semua</a></div>
        @forelse($bookings as $booking)<article class="card activity-card"><div><span class="badge" data-status="{{ $booking->status }}">{{ \App\Services\BookingService::STATUSES[$booking->status]??$booking->status }}</span></div><h3>{{ $booking->service_name }}</h3><p>{{ $booking->business->name }} · {{ $booking->starts_at->format('d M Y H:i') }}</p><a href="{{ route('customer.bookings') }}">Lihat reservasi →</a></article>@empty<div class="card empty">Belum ada reservasi mendatang.</div>@endforelse
    </section>
    <section><div class="section-heading"><h2>Pesanan Terbaru</h2><a href="{{ route('customer.orders') }}">Lihat semua</a></div>
        @forelse($sales as $sale)<article class="card activity-card"><h3>{{ $sale->business->name }}</h3><span class="order-reference">{{ $sale->code }}</span><p>{{ $sale->sale_date->format('d M Y') }}</p><strong class="price">Rp {{ number_format($sale->total,0,',','.') }}</strong>@if($sale->order_source==='customer_portal')<a href="{{ route('customer.orders.show',$sale->id) }}">Lihat detail pesanan →</a>@endif</article>@empty<div class="card empty">Pesanan Anda akan tampil di sini.</div>@endforelse
    </section>
</div>
<div class="section-heading"><h2>Loyalty Saya</h2><a href="{{ route('customer.loyalty') }}">Lihat poin</a></div>
<div class="grid">@forelse($loyalty as $member)<article class="card"><h3>{{ $member->business->name }}</h3><strong class="price">{{ $member->points }} poin</strong><p>Poin berlaku di toko ini.</p></article>@empty<div class="card empty">Mulai berinteraksi dengan toko untuk melihat loyalty Anda.</div>@endforelse</div>
@endsection
