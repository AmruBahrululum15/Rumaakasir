<div class="sc-grid">
    <aside class="sc-card sc-inbox" aria-label="Daftar percakapan">
        <div class="sc-inbox-header"><h2>Percakapan</h2><span class="sc-count">{{ $conversations->total() }}</span></div>
        <div class="sc-conversations">
            @forelse($conversations as $item)
                <a class="sc-conversation {{ $conversation?->id === $item->id ? 'is-active' : '' }}" href="{{ route('customer-chat.show', $item) }}" @if($conversation?->id === $item->id) aria-current="page" @endif>
                    <span class="sc-avatar" aria-hidden="true"><i class="far fa-user"></i></span>
                    <span class="sc-conversation-content"><span class="sc-conversation-top"><strong>{{ $item->account->name }}</strong>@if($item->latestMessage)<time datetime="{{ $item->latestMessage->created_at->toIso8601String() }}">{{ $item->latestMessage->created_at->format('d M') }}</time>@endif</span><span class="sc-preview">{{ \Illuminate\Support\Str::limit($item->latestMessage?->message, 60) ?: 'Belum ada pesan.' }}</span></span>
                    @if($item->unread_count)<span class="sc-unread" aria-label="{{ $item->unread_count }} pesan belum dibaca">{{ $item->unread_count }}</span>@endif
                </a>
            @empty
                <div class="sc-list-empty"><i class="far fa-comment-dots" aria-hidden="true"></i><strong>Belum ada percakapan.</strong><p>Pesan pelanggan akan tampil di sini.</p></div>
            @endforelse
        </div>
        @if($conversations->hasPages())<div class="sc-pagination">{{ $conversations->links() }}</div>@endif
    </aside>
    <section class="sc-card sc-thread" aria-label="Pesan pelanggan">
        @if($conversation)
            <div class="sc-thread-header"><span class="sc-avatar" aria-hidden="true"><i class="far fa-user"></i></span><div><h2>{{ $conversation->account->name }}</h2><p>Percakapan pelanggan</p></div></div>
            @if($messages->count() === 100 || request('before'))
                <div class="sc-history-nav">@if($messages->count() === 100)<a href="{{ request()->url().'?before='.$messages->first()->id }}">Pesan sebelumnya</a>@endif @if(request('before'))<a href="{{ request()->url() }}">Pesan terbaru</a>@endif</div>
            @endif
            <div class="messages" id="messages" aria-live="polite" aria-label="Riwayat pesan">
                @foreach($messages as $message)
                    <div class="bubble {{ $message->sender_type === 'staff' ? 'mine' : '' }}">{{ $message->message }}<small>{{ $message->created_at->format('d M H:i') }}{{ $message->read_at ? ' · Dibaca' : '' }}</small></div>
                @endforeach
            </div>
            @if(app(\App\Services\CustomerPortal::class)->active($conversation->business, 'customer_chat') && auth()->user()->can('customer_chat.reply'))
                <form class="sc-composer" method="post" action="{{ route('customer-chat.send', $conversation) }}">
                    @csrf
                    <label for="message">Balas pesan</label>
                    <textarea id="message" name="message" required maxlength="2000" rows="3" placeholder="Tulis balasan untuk pelanggan..."></textarea>
                    <div class="sc-composer-footer"><small>Maksimal 2.000 karakter</small><button type="submit"><i class="fas fa-paper-plane" aria-hidden="true"></i>Kirim pesan</button></div>
                </form>
            @else
                <div class="sc-readonly"><i class="fas fa-info-circle" aria-hidden="true"></i>Toko sedang tidak menerima layanan online. Percakapan tersedia untuk dibaca.</div>
            @endif
        @else
            <div class="sc-thread-empty"><div class="sc-empty-icon" aria-hidden="true"><i class="far fa-comments"></i></div><h2>Pilih percakapan</h2><p>Pilih percakapan untuk membaca pesan.</p><span>Pesan dan balasan pelanggan akan tampil di sini.</span></div>
        @endif
    </section>
</div>
@if($conversation && !request('before'))<script>
(()=>{let busy=false;setInterval(async()=>{if(document.hidden||busy)return;busy=true;try{const response=await fetch(@json(route($customer?'customer.chat.show':'customer-chat.show',$conversation)),{headers:{Accept:'application/json'}});if(!response.ok)return;const data=await response.json(),panel=document.getElementById('messages');panel.replaceChildren(...data.messages.map(m=>{const bubble=document.createElement('div');bubble.className='bubble'+(m.sender_type===@json($customer?'customer':'staff')?' mine':'');bubble.textContent=m.message;const time=document.createElement('small');time.textContent=new Date(m.created_at).toLocaleString('id-ID')+(m.read_at?' · Dibaca':'');bubble.append(time);return bubble;}));}catch(e){}finally{busy=false;}},15000);})();
</script>@endif
