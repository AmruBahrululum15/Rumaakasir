@if($product->image)
<img class="product-img" src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" data-portal-product-image>
@endif
<div class="product-placeholder" @if($product->image) hidden @endif aria-label="Foto produk belum tersedia"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 9 5-9 5-9-5 9-5Z M3 8v9l9 5 9-5V8 M12 13v9"/></svg></div>
