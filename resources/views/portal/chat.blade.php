@extends('portal.layout')
@section('title', 'Chat Toko')
@section('content')
<link rel="stylesheet" href="{{ asset('css/customer-store-chat.css') }}">
<div class="customer-store-chat">
    <div class="ct-page-header"><span class="ct-page-icon" aria-hidden="true">✉</span><div><h1>Chat Toko</h1><p>Tanyakan menu, ketersediaan produk, atau informasi lainnya langsung ke toko.</p></div></div>
    <div class="ct-layout">
        <aside class="ct-card ct-inbox" aria-label="Percakapan dengan toko">
            <div class="ct-inbox-header"><h2>Percakapan</h2><span class="ct-count">{{ $conversations->total() }}</span></div>
            <div class="ct-conversations">
                @forelse($conversations as $item)
                    <a class="ct-conversation {{ $conversation?->id === $item->id ? 'is-active' : '' }}" href="{{ route('customer.chat.show', $item) }}" @if($conversation?->id === $item->id) aria-current="page" @endif><span class="ct-store-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->business->name, 0, 1)) }}</span><span class="ct-conversation-copy"><strong>{{ $item->business->name }}</strong><small>{{ \Illuminate\Support\Str::limit($item->latestMessage?->message, 50) ?: 'Mulai percakapan dengan toko' }}</small></span>@if($item->unread_count)<span class="ct-unread">{{ $item->unread_count }}</span>@endif</a>
                @empty
                    <div class="ct-list-empty">Belum ada percakapan.<br><a href="{{ route('customer.stores') }}">Cari toko untuk memulai chat</a></div>
                @endforelse
            </div>
            @if($conversations->hasPages())<div class="ct-pagination">{{ $conversations->links() }}</div>@endif
        </aside>
        <section class="ct-card ct-thread" aria-label="Chat toko">
            @if($conversation)
                @php($canSend = $conversation->status === 'open' && app(\App\Services\CustomerPortal::class)->active($conversation->business, 'customer_chat'))
                <div class="ct-thread-header"><span class="ct-store-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($conversation->business->name, 0, 1)) }}</span><div><h2>{{ $conversation->business->name }}</h2><p>Percakapan pribadi Anda dengan toko</p></div><a href="{{ route('customer.store', $conversation->business->slug) }}">Lihat toko</a></div>
                <details class="ct-menu" open>
                    <summary><span>Menu & produk toko<small>Pilih produk yang ingin Anda tanyakan</small></span><span class="ct-menu-count">{{ $products->total() }} produk</span></summary>
                    <div class="ct-menu-body">
                        <form method="get" action="{{ route('customer.chat.show', $conversation) }}" class="ct-menu-search"><input name="menu_search" value="{{ request('menu_search') }}" maxlength="100" aria-label="Cari menu toko" placeholder="Cari menu atau produk..."><button type="submit">Cari</button></form>
                        <div class="ct-products">
                            @forelse($products as $product)
                                <label class="ct-product" for="ct-product-{{ $product->id }}">
                                    @if($product->image)<img src="{{ $product->image_url }}" alt="" loading="lazy">@endif
                                    <span class="ct-product-placeholder" @if($product->image) hidden @endif aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="m12 3 9 5-9 5-9-5 9-5Z M3 8v9l9 5 9-5V8 M12 13v9"/></svg></span>
                                    <span class="ct-product-copy"><strong>{{ $product->name }}</strong><span class="ct-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span><span class="ct-product-choice"><input id="ct-product-{{ $product->id }}" type="radio" name="product_id" value="{{ $product->id }}" form="ct-message-form" data-product-name="{{ $product->name }}" @checked((string) old('product_id') === (string) $product->id) @disabled(!$canSend)>Pilih produk</span></span>
                                </label>
                            @empty
                                <div class="ct-menu-empty">{{ request('menu_search') ? 'Produk tidak ditemukan. Coba kata pencarian lain.' : 'Toko belum memiliki menu atau produk yang tersedia. Anda tetap dapat mengirim pertanyaan umum.' }}</div>
                            @endforelse
                        </div>
                        @if($products->hasPages())<div class="ct-pagination">{{ $products->links() }}</div>@endif
                    </div>
                </details>
                @if($messages->count() === 100 || request('before'))<div class="ct-history-nav">@if($messages->count() === 100)<a href="{{ request()->url().'?before='.$messages->first()->id }}">Pesan sebelumnya</a>@endif @if(request('before'))<a href="{{ request()->url() }}">Pesan terbaru</a>@endif</div>@endif
                <div class="messages" id="messages" aria-live="polite" aria-label="Riwayat percakapan">
                    @foreach($messages as $message)<div class="bubble {{ $message->sender_type === 'customer' ? 'mine' : '' }}">{{ $message->message }}<small>{{ $message->created_at->format('d M H:i') }}{{ $message->read_at ? ' · Dibaca' : '' }}</small></div>@endforeach
                    @if($messages->isEmpty())<div class="ct-message-empty">Mulai percakapan dengan {{ $conversation->business->name }}.<br>Pilih produk di atas atau tulis pertanyaan Anda.</div>@endif
                </div>
                @if($canSend)
                    <form id="ct-message-form" class="ct-composer" method="post" action="{{ route('customer.chat.send', $conversation) }}">
                        @csrf
                        <div id="ct-selected-product" class="ct-selected-product" hidden><span>Pertanyaan tentang: <strong id="ct-selected-name"></strong></span><button id="ct-clear-product" type="button">Hapus pilihan</button></div>
                        <label for="message">Pertanyaan Anda</label>
                        <textarea id="message" name="message" required maxlength="1800" rows="3" placeholder="Contoh: Apakah produk ini masih tersedia?">{{ old('message') }}</textarea>
                        <div class="ct-composer-footer"><small>Pertanyaan dikirim langsung ke toko ini.</small><button type="submit">Kirim pertanyaan <span aria-hidden="true">↗</span></button></div>
                    </form>
                @else
                    <div class="ct-readonly">Toko sedang tidak menerima chat baru. Percakapan tetap tersedia untuk dibaca.</div>
                @endif
            @else
                <div class="ct-thread-empty"><span aria-hidden="true">✉</span><h2>Mulai percakapan dengan toko</h2><p>Pilih percakapan di samping, atau temukan toko dan tekan tombol Chat toko.</p><a class="btn" href="{{ route('customer.stores') }}">Cari toko</a></div>
            @endif
        </section>
    </div>
</div>
@if($conversation && !request('before'))
<script>
(()=>{let busy=false;setInterval(async()=>{if(document.hidden||busy)return;busy=true;try{const response=await fetch(@json(route('customer.chat.show',$conversation)),{headers:{Accept:'application/json'}});if(!response.ok)return;const data=await response.json(),panel=document.getElementById('messages');const nearBottom=panel.scrollHeight-panel.scrollTop-panel.clientHeight<80;panel.replaceChildren(...data.messages.map(m=>{const bubble=document.createElement('div');bubble.className='bubble'+(m.sender_type==='customer'?' mine':'');bubble.textContent=m.message;const time=document.createElement('small');time.textContent=new Date(m.created_at).toLocaleString('id-ID')+(m.read_at?' · Dibaca':'');bubble.append(time);return bubble;}));if(nearBottom)panel.scrollTop=panel.scrollHeight;}catch(e){}finally{busy=false;}},15000);})();
</script>
@endif
<script src="{{ asset('js/customer-store-chat.js') }}" defer></script>
@endsection
