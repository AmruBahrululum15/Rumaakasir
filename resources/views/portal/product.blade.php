@extends('portal.layout')
@section('title', 'Detail Produk')
@section('content')
<a class="back-link" href="{{ route('customer.store',$business->slug) }}">← {{ $business->name }}</a>
<div class="product-layout">
    <section class="card" aria-label="Foto produk">@include('portal.partials.product-image')</section>
    <section class="card"><span class="badge">{{ $product->category?->name ?? 'Produk toko' }}</span><h1 style="margin-top:14px">{{ $product->name }}</h1><p>{{ $business->name }}</p><div class="price">Rp {{ number_format($product->price,0,',','.') }}</div><p>{{ $product->stock>0 ? 'Tersedia' : 'Stok habis' }}</p>
        @if($product->stock>0 && in_array($business->business_type,\App\Services\PortalOrderService::TYPES))
        <form method="post" action="{{ route('customer.cart.add',$business->slug) }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><label for="product-quantity">Jumlah</label><input id="product-quantity" type="number" name="quantity" min="1" max="100" value="1"><div><button type="submit">Tambah ke keranjang</button></div></form>
        @endif
        @if(app(\App\Services\CustomerPortal::class)->active($business,'customer_chat'))<form method="post" action="{{ route('customer.chat.start',$business->slug) }}">@csrf<button type="submit" class="secondary">Tanyakan ke toko</button></form>@endif
    </section>
</div>
@endsection
