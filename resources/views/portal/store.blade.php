@extends('portal.layout')
@section('title', $business->name)
@section('content')
<section class="hero">@if($business->logo)<img class="store-logo" src="{{ asset($business->logo) }}" alt="Logo {{ $business->name }}">@endif<span class="badge">{{ ucfirst($business->business_type) }}</span><h1>{{ $business->name }}</h1><p>{{ $business->address }}</p>
@auth('customer')<div class="search"><form method="post" action="{{ route('customer.join',$business->slug) }}">@csrf<button class="secondary">Ikuti toko · {{ $points }} poin</button></form>@if($portal->active($business,'customer_chat'))<form method="post" action="{{ route('customer.chat.start',$business->slug) }}">@csrf<button>Chat toko</button></form>@endif</div>@else<a class="btn" href="{{ route('customer.login') }}">Masuk untuk booking & chat</a>@endauth</section>
@if($portal->active($business,'customer_booking') && app(\App\Services\PackageAccess::class)->allows('booking',$business) && app(\App\Services\ReservationSchedule::class)->settings($business)['enabled'])
<section class="card" style="margin:20px 0"><h2>Reservasi di {{ $business->name }}</h2><p>Pilih layanan, karyawan atau fasilitas, dan lihat jadwal yang masih tersedia. Reservasi dapat dilakukan tanpa login.</p><a class="btn" href="{{ route('reservations.form',$business->slug) }}">Reservasi Sekarang →</a></section>
@endif
@if($services->isNotEmpty())<h2 class="store-section-title">Layanan & harga</h2><div class="grid">@foreach($services as $service)<article class="card"><h2>{{ $service->name }}</h2><div class="price">Rp {{ number_format($service->price,0,',','.') }}</div><p>Per {{ $service->unit }}</p></article>@endforeach</div>@endif
<div class="section-heading"><div><h2>Menu & produk</h2><p>Pilih produk untuk melihat detail atau menambahkannya ke keranjang.</p></div>@if(in_array($business->business_type,\App\Services\PortalOrderService::TYPES))<a class="btn secondary" href="{{ route('customer.cart',$business->slug) }}">Lihat keranjang →</a>@endif</div><form class="search" method="get"><input aria-label="Cari produk" name="q" value="{{ request('q') }}" placeholder="Cari produk / menu"><select aria-label="Kategori" name="category"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category')==$category->id)>{{ $category->name }}</option>@endforeach</select><button>Cari</button></form><br>
<div class="grid">@forelse($products as $product)<article class="card">@include('portal.partials.product-image')<h2><a href="{{ route('customer.product',[$business->slug,$product->id]) }}">{{ $product->name }}</a></h2><div class="price">Rp {{ number_format($product->price,0,',','.') }}</div><p>{{ $product->stock>0?'Tersedia':'Stok habis' }}</p>@if($product->stock>0 && in_array($business->business_type,\App\Services\PortalOrderService::TYPES))<form method="post" action="{{ route('customer.cart.add',$business->slug) }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><label>Jumlah</label><input type="number" name="quantity" value="1" min="1" max="100"><button>Tambah ke keranjang</button></form>@endif</article>@empty<div class="card empty">Belum ada produk yang tersedia.</div>@endforelse</div>{{ $products->links() }}
@if(in_array($business->business_type,['cafe','restaurant','kedai']) && app(\App\Services\PackageAccess::class)->allows('qr_order',$business))<section class="card"><h2>Pesan di meja</h2><p>Gunakan QR di meja saat berkunjung untuk melanjutkan order melalui checkout toko.</p></section>@endif
@if(!empty($markers))
<section id="store-location" class="card store-location-card" aria-labelledby="store-location-title">
    <div class="section-heading">
        <div><span class="store-location-label">KUNJUNGI TOKO</span><h2 id="store-location-title">Lokasi {{ $business->name }}</h2><p>{{ $business->address ?: 'Temukan lokasi toko pada peta berikut.' }}</p></div>
        <a class="btn secondary" href="{{ 'https://www.google.com/maps/dir/?api=1&destination='.$business->latitude.','.$business->longitude }}" target="_blank" rel="noopener noreferrer">Petunjuk arah ↗</a>
    </div>
    @include('portal.partials.map')
    <a class="store-location-open" href="{{ 'https://www.google.com/maps?q='.$business->latitude.','.$business->longitude }}" target="_blank" rel="noopener noreferrer">Buka lokasi di Google Maps ↗</a>
</section>
@endif
@endsection
