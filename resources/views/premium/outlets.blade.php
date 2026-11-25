@extends('layouts.app')
@section('title','Multi Outlet')
@section('content')
<link rel="stylesheet" href="{{ asset('css/multi-outlet.css') }}">
<div class="multi-outlet-page">
    <header class="mo-heading"><div><span class="mo-eyebrow">Manajemen cabang</span><h1>Multi Outlet</h1><p>Kelola cabang, lingkup kerja, dan penugasan tim dalam satu toko.</p></div>
        @if(app(\App\Services\PackageAccess::class)->allows('inter_outlet_stock_transfer'))<a class="mo-btn mo-secondary" href="{{ route('outlets.inventory.index') }}"><i class="fas fa-boxes" aria-hidden="true"></i> Stok & Transfer Outlet</a>@endif
    </header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <div class="mo-stats">
        <article class="mo-stat"><span class="mo-stat-icon"><i class="fas fa-store" aria-hidden="true"></i></span><div><span>Total outlet</span><strong>{{ $outlets->count() }}</strong><small>{{ $outlets->where('is_active',true)->count() }} outlet aktif</small></div></article>
        <article class="mo-stat"><span class="mo-stat-icon mo-neutral"><i class="fas fa-receipt" aria-hidden="true"></i></span><div><span>Transaksi dibayar</span><strong>{{ number_format($summary->sum('transactions'),0,',','.') }}</strong><small>Seluruh riwayat toko</small></div></article>
        <article class="mo-stat"><span class="mo-stat-icon mo-neutral"><i class="fas fa-wallet" aria-hidden="true"></i></span><div><span>Total omzet</span><strong class="mo-revenue">Rp {{ number_format($summary->sum('revenue'),0,',','.') }}</strong><small>Dari transaksi berstatus lunas</small></div></article>
    </div>
    <div class="mo-top-grid">
        <section class="mo-card">
            <div class="mo-section-heading"><span class="mo-icon"><i class="fas fa-map-marker-alt" aria-hidden="true"></i></span><div><h2>Lingkup Kerja Pemilik</h2><p>Pilih outlet untuk transaksi yang Anda kelola.</p></div></div>
            <form method="POST" action="{{ route('outlets.select') }}">@csrf
                <label for="mo-selected">Outlet kerja</label><div class="mo-inline-form"><select id="mo-selected" name="outlet_id"><option value="">Semua outlet / transaksi baru tanpa outlet</option>@foreach($outlets->where('is_active',true) as $o)<option value="{{ $o->id }}" @selected($selected===$o->id)>{{ $o->name }}</option>@endforeach</select><button type="submit">Pilih outlet</button></div>
                <div class="mo-context"><span class="mo-dot"></span>Lingkup saat ini: <strong>{{ $outlets->firstWhere('id',$selected)?->name ?? 'Semua outlet' }}</strong></div>
            </form>
        </section>
        <details class="mo-card mo-add"><summary><span class="mo-icon"><i class="fas fa-plus" aria-hidden="true"></i></span><span><strong>Tambah Outlet</strong><small>Daftarkan cabang baru untuk toko Anda.</small></span><span class="mo-chevron" aria-hidden="true"></span></summary>
            <form method="POST" action="{{ route('outlets.store') }}">@csrf<input type="hidden" name="is_active" value="1">
                <div class="mo-fields"><div><label for="mo-new-name">Nama outlet <span>*</span></label><input id="mo-new-name" name="name" maxlength="150" placeholder="Contoh: Cabang Cileungsi" required></div><div><label for="mo-new-phone">Telepon</label><input id="mo-new-phone" type="tel" name="phone" maxlength="30" placeholder="Nomor telepon outlet"></div><div class="mo-wide"><label for="mo-new-address">Alamat</label><textarea id="mo-new-address" name="address" maxlength="1000" rows="2" placeholder="Alamat lengkap cabang"></textarea></div></div>
                <div class="mo-form-actions"><button type="submit">Simpan outlet</button></div>
            </form>
        </details>
    </div>
    <section class="mo-outlets-section"><div class="mo-section-title"><div><h2>Daftar Outlet</h2><p>Informasi cabang dan ringkasan transaksi lunas.</p></div><span class="mo-count">{{ $outlets->count() }} outlet</span></div>
        <div class="mo-outlets-grid">
            @forelse($outlets as $o)
            <article class="mo-card mo-outlet"><div class="mo-outlet-head"><span class="mo-icon"><i class="fas fa-store" aria-hidden="true"></i></span><h3>{{ $o->name }}</h3><span class="mo-badge {{ $o->is_active?'':'mo-inactive' }}">{{ $o->is_active?'Aktif':'Nonaktif' }}</span></div>
                <div class="mo-outlet-contact"><p><i class="fas fa-map-marker-alt" aria-hidden="true"></i>{{ $o->address ?: 'Alamat belum dicantumkan' }}</p><p><i class="fas fa-phone-alt" aria-hidden="true"></i>{{ $o->phone ?: 'Telepon belum dicantumkan' }}</p></div>
                <div class="mo-outlet-numbers"><div><span>Transaksi dibayar</span><strong>{{ number_format($summary->get($o->id)?->transactions ?? 0,0,',','.') }}</strong></div><div><span>Omzet</span><strong>Rp {{ number_format($summary->get($o->id)?->revenue ?? 0,0,',','.') }}</strong></div></div>
                <details class="mo-edit"><summary><i class="far fa-edit" aria-hidden="true"></i> Edit outlet <span class="mo-chevron" aria-hidden="true"></span></summary><form method="POST" action="{{ route('outlets.update',$o) }}">@csrf @method('PUT')
                    <div class="mo-fields"><div><label for="mo-name-{{ $o->id }}">Nama</label><input id="mo-name-{{ $o->id }}" name="name" maxlength="150" value="{{ $o->name }}" required></div><div><label for="mo-phone-{{ $o->id }}">Telepon</label><input id="mo-phone-{{ $o->id }}" type="tel" name="phone" maxlength="30" value="{{ $o->phone }}"></div><div class="mo-wide"><label for="mo-address-{{ $o->id }}">Alamat</label><textarea id="mo-address-{{ $o->id }}" name="address" maxlength="1000" rows="2">{{ $o->address }}</textarea></div><div><label for="mo-active-{{ $o->id }}">Status</label><select id="mo-active-{{ $o->id }}" name="is_active"><option value="1" @selected($o->is_active)>Aktif</option><option value="0" @selected(!$o->is_active)>Nonaktif</option></select></div></div><div class="mo-form-actions"><button type="submit">Simpan perubahan</button></div>
                </form></details>
            </article>
            @empty
            <div class="mo-empty"><span class="mo-icon"><i class="fas fa-store" aria-hidden="true"></i></span><h3>Belum ada outlet</h3><p>Tambahkan cabang melalui form Tambah Outlet untuk mulai mengelola tim dan transaksi per outlet.</p></div>
            @endforelse
        </div>
    </section>
    <section class="mo-legacy"><span class="mo-icon mo-neutral"><i class="fas fa-archive" aria-hidden="true"></i></span><div><h2>Tanpa outlet <span>Termasuk data lama</span></h2><p>Transaksi tanpa penugasan outlet tetap tersimpan dalam toko ini.</p></div><div class="mo-legacy-total"><strong>Rp {{ number_format($summary->get('')?->revenue ?? 0,0,',','.') }}</strong><span>{{ $summary->get('')?->transactions ?? 0 }} transaksi dibayar</span></div></section>
    <section class="mo-card mo-team"><div class="mo-section-title"><div><h2>Penugasan Pengguna</h2><p>Staff yang ditugaskan hanya mengakses transaksi outletnya.</p></div><span class="mo-count">{{ $users->count() }} pengguna</span></div>
        <div class="mo-table-wrap"><table><thead><tr><th>Pengguna</th><th>Penugasan outlet</th></tr></thead><tbody>@forelse($users as $u)<tr><td><div class="mo-user"><span class="mo-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($u->name,0,1)) }}</span><div><strong>{{ $u->name }}</strong><span>{{ $u->username }}</span></div></div></td><td>@if($u->hasRole('Atasan'))<span class="mo-owner-label"><i class="far fa-user" aria-hidden="true"></i> Pemilik</span><span class="mo-owner-help">Pemilik memilih lingkup kerja sendiri.</span>@else<form class="mo-assignment" method="POST" action="{{ route('outlets.assign',$u) }}">@csrf @method('PATCH')<label class="mo-sr-only" for="mo-assignment-{{ $u->id }}">Outlet untuk {{ $u->name }}</label><select id="mo-assignment-{{ $u->id }}" name="outlet_id"><option value="">Tanpa penugasan</option>@foreach($outlets->where('is_active',true) as $o)<option value="{{ $o->id }}" @selected($u->outlet_id==$o->id)>{{ $o->name }}</option>@endforeach</select><button type="submit" class="mo-secondary">Simpan</button></form>@endif</td></tr>@empty<tr><td colspan="2" class="mo-table-empty">Belum ada pengguna yang dapat ditugaskan.</td></tr>@endforelse</tbody></table></div>
    </section>
</div>
@endsection
