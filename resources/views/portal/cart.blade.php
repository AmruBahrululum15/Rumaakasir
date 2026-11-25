@extends('portal.layout')
@section('title', 'Keranjang')
@section('content')
<a class="back-link" href="{{ route('customer.store',$business->slug) }}">← Kembali ke toko</a>
<section class="hero"><h1>Keranjang Anda</h1><p>{{ $business->name }} · Periksa pilihan produk dan jumlah sebelum membuat pesanan.</p></section>
@if($products->isNotEmpty())
<div class="checkout-layout">
    <section class="cart-items" aria-label="Produk dalam keranjang">
        @foreach($products as $product)
            <article class="card cart-item"><h2>{{ $product->name }}</h2><p>Rp {{ number_format($product->price,0,',','.') }} / produk</p>
                <form method="post" action="{{ route('customer.cart.add',$business->slug) }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><div><label for="cart-qty-{{ $product->id }}">Jumlah</label><input id="cart-qty-{{ $product->id }}" type="number" name="quantity" min="0" max="100" value="{{ $cart[$product->id] }}" aria-describedby="cart-help-{{ $product->id }}"></div><button type="submit" class="secondary">Perbarui</button></form>
                <small id="cart-help-{{ $product->id }}">Isi 0 lalu perbarui untuk menghapus produk.</small>
            </article>
        @endforeach
    </section>
    <section class="card checkout-summary" aria-labelledby="checkout-title">
        <h2 id="checkout-title">Ringkasan pesanan</h2><p>{{ $products->sum(fn($p)=>$cart[$p->id]) }} item dari {{ $business->name }}</p>
        <div class="checkout-total"><span>Total produk</span><strong class="price">Rp {{ number_format($products->sum(fn($p)=>$p->price*$cart[$p->id]),0,',','.') }}</strong></div>
        <form method="post" action="{{ route('customer.cart.checkout',$business->slug) }}">@csrf<input type="hidden" name="token" value="{{ $token }}">
            <label for="checkout-voucher">Kode voucher <small>(opsional)</small></label><input id="checkout-voucher" name="voucher" maxlength="60" placeholder="Masukkan kode voucher" value="{{ old('voucher') }}">
            <fieldset class="customer-checkout-payments"><legend>Metode Pembayaran</legend>@forelse($methods as $value=>$label)<label class="payment-choice"><input type="radio" name="payment_method" value="{{ $value }}" required @checked(old('payment_method',array_key_first($methods))===$value)><span class="checkout-payment-text"><strong>{{ $label }}</strong><small>{{ match($value) { 'qris'=>'Scan QRIS dan selesaikan pembayaran pesanan.', 'va'=>'Pilih bank dan bayar melalui Virtual Account.', default=>'Bayar tunai langsung kepada kasir toko.' } }}</small></span><span class="checkout-payment-tag">{{ $value === 'cash' ? 'Tunai' : ($value === 'qris' ? 'QR' : 'Bank') }}</span></label>@empty<p>Toko belum mengaktifkan metode pembayaran.</p>@endforelse</fieldset>
            <button type="submit" class="checkout-submit" @disabled(!$methods)>Buat Pesanan <span aria-hidden="true">→</span></button>
            <p class="checkout-hint">Total akhir, voucher, dan ketersediaan produk diperiksa saat pesanan dibuat.</p>
        </form>
    </section>
</div>
@else
<section class="card empty"><h2>Keranjang masih kosong.</h2><p>Pilih produk dari toko ini untuk mulai memesan.</p><a class="btn" href="{{ route('customer.store',$business->slug) }}">Pilih produk</a></section>
@endif
@endsection
