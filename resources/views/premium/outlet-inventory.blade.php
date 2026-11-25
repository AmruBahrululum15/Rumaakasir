@extends('layouts.app')
@section('title','Stok & Transfer Outlet')
@section('content')
<link rel="stylesheet" href="{{ asset('css/multi-outlet.css') }}">
<link rel="stylesheet" href="{{ asset('css/outlet-inventory.css') }}">
<div class="multi-outlet-page outlet-inventory-page">
    <header class="mo-heading"><div><span class="mo-eyebrow">Persediaan cabang</span><h1>Stok & Transfer Outlet</h1><p>Atur alokasi produk dan pantau perpindahan stok antaroutlet.</p></div><a class="mo-btn mo-secondary" href="{{ route('outlets.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke Multi Outlet</a></header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <div class="oi-top-grid">
        <section class="mo-card oi-transfer-card">
            <div class="mo-section-heading"><span class="mo-icon"><i class="fas fa-exchange-alt" aria-hidden="true"></i></span><div><h2>Transfer Stok</h2><p>Pilih produk, lokasi asal, dan tujuan transfer.</p></div></div>
            <form method="POST" action="{{ route('outlets.inventory.store') }}">@csrf<input type="hidden" name="request_key" value="{{ (string)\Illuminate\Support\Str::uuid() }}">
                <label for="oi-product">Produk</label><select id="oi-product" name="product_id" required>@forelse($products as $p)<option value="{{ $p->id }}" @selected(old('product_id')==$p->id)>{{ $p->name }} (Total {{ $p->stock }})</option>@empty<option value="">Belum ada produk</option>@endforelse</select>
                <div class="oi-locations">
                    <div><label for="oi-source">Dari lokasi</label><select id="oi-source" name="from_outlet_id"><option value="">Pusat / tanpa outlet</option>@foreach($outlets as $o)<option value="{{ $o->id }}" @selected(old('from_outlet_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
                    <span class="oi-direction" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                    <div><label for="oi-destination">Ke lokasi</label><select id="oi-destination" name="to_outlet_id"><option value="">Pusat / tanpa outlet</option>@foreach($outlets as $o)<option value="{{ $o->id }}" @selected(old('to_outlet_id')==$o->id)>{{ $o->name }}</option>@endforeach</select></div>
                </div>
                <div class="oi-quantity"><div><label for="oi-quantity">Jumlah transfer</label><div class="oi-unit-input"><input id="oi-quantity" name="quantity" type="number" min="1" max="1000000" value="{{ old('quantity') }}" placeholder="0" required><span>unit</span></div></div><p>Pastikan lokasi asal dan tujuan berbeda. Jumlah yang tersedia diperiksa saat transfer diproses.</p></div>
                <div class="mo-form-actions"><button type="submit" @disabled($products->isEmpty())><i class="fas fa-exchange-alt" aria-hidden="true"></i> Transfer stok</button></div>
            </form>
        </section>
        <aside class="oi-guide">
            <div class="oi-metrics"><article><span class="mo-icon mo-neutral"><i class="fas fa-store" aria-hidden="true"></i></span><div><strong>{{ $outlets->count() }}</strong><span>Outlet aktif</span></div></article><article><span class="mo-icon mo-neutral"><i class="fas fa-box" aria-hidden="true"></i></span><div><strong>{{ $products->count() }}</strong><span>Produk toko</span></div></article></div>
            <section class="mo-card oi-explanation"><h2>Alokasi stok yang terkontrol</h2><p>Transfer memindahkan alokasi produk, sehingga stok total toko tetap sama.</p><div class="oi-guide-item"><span>01</span><div><h3>Stok pusat</h3><p>Stok total dikurangi jumlah yang sudah dialokasikan ke outlet.</p></div></div><div class="oi-guide-item"><span>02</span><div><h3>Stok outlet</h3><p>Persediaan yang dialokasikan untuk transaksi di cabang tersebut.</p></div></div><div class="oi-guide-note"><i class="fas fa-info-circle" aria-hidden="true"></i><span>Transfer tidak memindahkan transaksi lama.</span></div></section>
        </aside>
    </div>
    <section class="mo-card oi-table-card"><div class="mo-section-title"><div><h2>Stok Outlet</h2><p>Alokasi persediaan per produk dan cabang.</p></div><span class="mo-count">{{ number_format($stocks->total(),0,',','.') }} alokasi</span></div>
        <div class="mo-table-wrap"><table><thead><tr><th>Outlet</th><th>Produk</th><th class="oi-number">Stok tersedia</th></tr></thead><tbody>@forelse($stocks as $s)<tr><td><div class="oi-cell"><span class="oi-cell-icon"><i class="fas fa-store" aria-hidden="true"></i></span><strong>{{ $s->outlet?->name ?? '—' }}</strong></div></td><td>{{ $s->product?->name ?? '—' }}</td><td class="oi-number"><span class="oi-stock-badge {{ $s->quantity<=0?'oi-zero':'' }}">{{ number_format($s->quantity,0,',','.') }} <small>unit</small></span></td></tr>@empty<tr><td colspan="3"><div class="oi-empty"><span class="mo-icon mo-neutral"><i class="fas fa-box-open" aria-hidden="true"></i></span><strong>Belum ada alokasi stok.</strong><p>Transfer produk dari pusat ke outlet untuk mulai mengisi persediaan cabang.</p></div></td></tr>@endforelse</tbody></table></div><div class="oi-pagination">{{ $stocks->links() }}</div>
    </section>
    <section class="mo-card oi-table-card"><div class="mo-section-title"><div><h2>Riwayat Transfer</h2><p>30 transfer terbaru yang tercatat di toko Anda.</p></div><span class="mo-count">Terbaru</span></div>
        <div class="mo-table-wrap"><table><thead><tr><th>Waktu</th><th>Produk</th><th>Asal & tujuan</th><th class="oi-number">Jumlah</th></tr></thead><tbody>@forelse($transfers as $t)<tr><td><strong class="oi-date">{{ \Carbon\Carbon::parse($t->created_at)->locale('id')->translatedFormat('d M Y') }}</strong><span class="oi-time">{{ \Carbon\Carbon::parse($t->created_at)->format('H:i') }}</span></td><td>{{ $products->firstWhere('id',$t->product_id)?->name ?? '—' }}</td><td><div class="oi-route"><span>{{ $outlets->firstWhere('id',$t->from_outlet_id)?->name ?? 'Pusat / nonaktif' }}</span><i class="fas fa-arrow-right" aria-hidden="true"></i><span>{{ $outlets->firstWhere('id',$t->to_outlet_id)?->name ?? 'Pusat / nonaktif' }}</span></div></td><td class="oi-number"><strong class="oi-transfer-quantity">{{ number_format($t->quantity,0,',','.') }} <small>unit</small></strong></td></tr>@empty<tr><td colspan="4"><div class="oi-empty"><span class="mo-icon mo-neutral"><i class="fas fa-exchange-alt" aria-hidden="true"></i></span><strong>Belum ada transfer.</strong><p>Riwayat perpindahan stok akan muncul setelah transfer pertama berhasil.</p></div></td></tr>@endforelse</tbody></table></div>
    </section>
</div>
@endsection
