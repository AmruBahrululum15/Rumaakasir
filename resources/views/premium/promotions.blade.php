@extends('layouts.app')
@section('title', 'Promo & Voucher')
@section('content')
<style>
.promo-page { color:#253044; padding:8px 0 24px; }
.promo-page .promo-header { display:flex; align-items:center; gap:14px; margin-bottom:22px; }
.promo-page .promo-icon { width:48px; height:48px; flex-shrink:0; display:flex; align-items:center; justify-content:center; border-radius:14px; background:#fff0e3; color:#ed7119; font-size:20px; }
.promo-page h1 { margin:0 0 5px; font-size:26px; font-weight:700; letter-spacing:-.5px; }
.promo-page .promo-subtitle { margin:0; color:#738093; font-size:13px; line-height:1.6; }
.promo-page .promo-card { margin-bottom:20px; background:#fff; border:1px solid #ebe7e2; border-radius:16px; box-shadow:0 4px 18px rgba(35,45,60,.03); overflow:hidden; }
.promo-page .promo-card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:20px 24px; border-bottom:1px solid #f0eeeb; }
.promo-page h2 { margin:0; font-size:16px; font-weight:700; }
.promo-page .promo-card-header p { margin:5px 0 0; color:#738093; font-size:12px; }
.promo-page .promo-mode { padding:6px 10px; border-radius:8px; background:#fff4e9; color:#a34d13; font-size:11px; font-weight:600; white-space:nowrap; }
.promo-page .promo-form-body { padding:24px; }
.promo-page .promo-form-section + .promo-form-section { margin-top:22px; padding-top:22px; border-top:1px solid #f0eeeb; }
.promo-page h3 { margin:0 0 16px; font-size:13px; font-weight:700; color:#536174; }
.promo-page h3 i { color:#e98137; margin-right:7px; }
.promo-page .promo-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px 24px; }
.promo-page .promo-field { min-width:0; }
.promo-page .promo-field label { display:block; margin-bottom:7px; font-size:12px; font-weight:600; }
.promo-page .promo-optional { color:#8a94a2; font-weight:400; }
.promo-page .form-control { width:100%; height:42px; border:1px solid #e3e6eb; border-radius:9px; background:#fff; color:#253044; font-size:13px; padding:9px 12px; box-shadow:none; }
.promo-page .form-control::placeholder { color:#9ca5b1; }
.promo-page .form-control:focus { border-color:#efa66f; box-shadow:0 0 0 3px rgba(255,122,24,.1); }
.promo-page .promo-help { display:block; margin-top:6px; color:#8590a0; font-size:11px; line-height:1.5; }
.promo-page .promo-note { display:flex; gap:10px; margin-top:22px; padding:12px 14px; border-radius:10px; background:#faf8f5; color:#758092; font-size:12px; line-height:1.6; }
.promo-page .promo-note i { margin-top:3px; color:#ca8d5e; }
.promo-page .promo-form-footer { display:flex; align-items:center; justify-content:flex-end; gap:12px; padding:16px 24px; background:#fdfcfb; border-top:1px solid #f0eeeb; }
.promo-page .promo-save { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:10px 20px; border:1px solid #ed7119; border-radius:9px; background:#ed7119; color:#fff; font-size:13px; font-weight:600; cursor:pointer; }
.promo-page .promo-save:hover { background:#d9600c; border-color:#d9600c; }
.promo-page .promo-cancel { padding:10px 12px; color:#68768a; font-size:13px; }
.promo-page .promo-table { width:100%; margin:0; }
.promo-page .promo-table th { padding:13px 24px; background:#faf9f7; border:0; color:#8490a0; font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; white-space:nowrap; }
.promo-page .promo-table td { padding:18px 24px; border-top:1px solid #f0eeeb; vertical-align:middle; font-size:12px; }
.promo-page .promo-name { margin-bottom:6px; font-size:13px; font-weight:600; overflow-wrap:anywhere; }
.promo-page .promo-code { display:inline-block; padding:3px 8px; border:1px solid #ece5db; border-radius:5px; background:#fbf8f4; color:#87684d; font-size:11px; overflow-wrap:anywhere; }
.promo-page .promo-period { display:flex; flex-direction:column; gap:4px; color:#637084; white-space:nowrap; }
.promo-page .promo-period span:last-child { color:#919aaa; font-size:11px; }
.promo-page .promo-status { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; }
.promo-page .promo-status::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
.promo-page .promo-status-active { background:#eef8f2; color:#338457; }
.promo-page .promo-status-inactive { background:#f0f2f5; color:#788495; }
.promo-page .promo-actions { display:flex; flex-wrap:wrap; gap:8px; }
.promo-page .promo-action { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border:1px solid #e8e6e3; border-radius:7px; color:#68768a; font-size:11px; text-decoration:none; white-space:nowrap; }
.promo-page .promo-action:hover { background:#fff5eb; border-color:#f1c7a8; color:#aa5014; }
.promo-page .promo-empty { padding:30px 20px; text-align:center; }
.promo-page .promo-empty-icon { display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; margin-bottom:12px; border-radius:14px; background:#fff4e9; color:#e5a16e; font-size:19px; }
.promo-page .promo-empty strong { display:block; margin-bottom:5px; font-size:13px; }
.promo-page .promo-empty p { margin:0; font-size:12px; color:#8590a0; }
.promo-page .promo-pagination { padding:16px 24px; border-top:1px solid #f0eeeb; }
.promo-page .promo-pagination nav,.promo-page .promo-pagination .pagination { margin-bottom:0; }
@media(max-width:767px) {
.promo-page h1 { font-size:22px; }
.promo-page .promo-header { gap:10px; margin-bottom:18px; }
.promo-page .promo-card-header { padding:18px; align-items:flex-start; }
.promo-page .promo-form-body { padding:18px; }
.promo-page .promo-fields { grid-template-columns:minmax(0,1fr); gap:16px; }
.promo-page .promo-form-footer { padding:14px 18px; }
.promo-page .promo-save { flex:1; }
.promo-page .promo-table th,.promo-page .promo-table td { padding:14px 18px; }
.promo-page .promo-pagination { padding:16px 18px; }
}
</style>
<div class="promo-page">
    <header class="promo-header">
        <div class="promo-icon" aria-hidden="true"><i class="fas fa-tags"></i></div>
        <div><h1>Promo & Voucher</h1><p class="promo-subtitle">Kelola diskon otomatis dan kode voucher untuk pelanggan toko Anda.</p></div>
    </header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ $editing->exists ? route('promotions.update', $editing) : route('promotions.store') }}" class="promo-card">
        @csrf
        @if($editing->exists) @method('PUT') @endif
        <div class="promo-card-header">
            <div><h2>{{ $editing->exists ? 'Edit promo' : 'Buat promo baru' }}</h2><p>Atur diskon, kuota, dan periode berlaku promo.</p></div>
            <span class="promo-mode">{{ $editing->exists ? 'Mode edit' : 'Promo / Voucher' }}</span>
        </div>
        <div class="promo-form-body">
            <section class="promo-form-section" aria-labelledby="promo-info-title">
                <h3 id="promo-info-title"><i class="fas fa-tag" aria-hidden="true"></i>Informasi promo</h3>
                <div class="promo-fields">
                    <div class="promo-field"><label for="promo-name">Nama promo</label><input id="promo-name" class="form-control" name="name" value="{{ old('name', $editing->name) }}" placeholder="Contoh: Promo akhir pekan" required></div>
                    <div class="promo-field">
                        <label for="promo-code">Kode voucher <span class="promo-optional">(opsional)</span></label>
                        <input id="promo-code" class="form-control" name="code" value="{{ old('code', $editing->code) }}" placeholder="Contoh: HEMAT10" aria-describedby="promo-code-help">
                        <small id="promo-code-help" class="promo-help">Kosongkan untuk promo otomatis. Gunakan huruf kapital, angka, _ atau -.</small>
                    </div>
                </div>
            </section>
            <section class="promo-form-section" aria-labelledby="promo-discount-title">
                <h3 id="promo-discount-title"><i class="fas fa-percent" aria-hidden="true"></i>Pengaturan diskon</h3>
                <div class="promo-fields">
                    <div class="promo-field">
                        <label for="promo-discount-type">Jenis diskon</label>
                        <select id="promo-discount-type" name="discount_type" class="form-control">@foreach(['percentage' => 'Persen', 'fixed' => 'Nominal rupiah'] as $key => $label)<option value="{{ $key }}" @selected(old('discount_type', $editing->discount_type) === $key)>{{ $label }}</option>@endforeach</select>
                    </div>
                    <div class="promo-field"><label for="promo-discount-value">Nilai diskon</label><input id="promo-discount-value" class="form-control" name="discount_value" value="{{ old('discount_value', $editing->discount_value) }}" placeholder="Contoh: 10 atau 10000" required><small class="promo-help">Isi persentase atau nominal rupiah sesuai jenis diskon.</small></div>
                    @foreach(['min_transaction' => 'Minimum transaksi', 'max_discount' => 'Maksimum diskon'] as $key => $label)
                        <div class="promo-field"><label for="promo-{{ $key }}">{{ $label }} @if($key === 'max_discount')<span class="promo-optional">(opsional)</span>@endif</label><input id="promo-{{ $key }}" class="form-control" name="{{ $key }}" value="{{ old($key, $editing->$key ?? ($key === 'min_transaction' ? 0 : '')) }}" placeholder="Nominal rupiah" @if($key === 'min_transaction') required @endif></div>
                    @endforeach
                </div>
            </section>
            <section class="promo-form-section" aria-labelledby="promo-period-title">
                <h3 id="promo-period-title"><i class="far fa-calendar-alt" aria-hidden="true"></i>Kuota & periode</h3>
                <div class="promo-fields">
                    @foreach(['usage_limit' => 'Kuota total', 'per_customer_limit' => 'Kuota per customer'] as $key => $label)
                        <div class="promo-field"><label for="promo-{{ $key }}">{{ $label }} <span class="promo-optional">(opsional)</span></label><input id="promo-{{ $key }}" class="form-control" name="{{ $key }}" value="{{ old($key, $editing->$key) }}" placeholder="Kosongkan jika tanpa batas"></div>
                    @endforeach
                    @foreach(['start_at' => 'Mulai', 'end_at' => 'Berakhir'] as $key => $label)
                        <div class="promo-field"><label for="promo-{{ $key }}">{{ $label }}</label><input id="promo-{{ $key }}" class="form-control" type="datetime-local" name="{{ $key }}" value="{{ old($key, $editing->$key?->format('Y-m-d\TH:i')) }}" required></div>
                    @endforeach
                    <div class="promo-field"><label for="promo-status">Status</label><select id="promo-status" name="is_active" class="form-control"><option value="1" @selected(old('is_active', $editing->is_active ?? true))>Aktif</option><option value="0" @selected(!old('is_active', $editing->is_active ?? true))>Nonaktif</option></select></div>
                </div>
            </section>
            <div class="promo-note"><i class="fas fa-info-circle" aria-hidden="true"></i><span>Satu promo terbaik diterapkan otomatis; kode voucher menggantikan promo otomatis. Kuota termasuk pembayaran yang masih pending.</span></div>
        </div>
        <div class="promo-form-footer">
            @if($editing->exists)<a class="promo-cancel" href="{{ route('promotions.index') }}">Batal</a>@endif
            <button type="submit" class="promo-save"><i class="fas fa-check" aria-hidden="true"></i>Simpan promo</button>
        </div>
    </form>
    <section class="promo-card" aria-labelledby="promo-list-title">
        <div class="promo-card-header"><div><h2 id="promo-list-title">Daftar promo & voucher</h2><p>Pantau periode, status, dan riwayat penggunaan promo.</p></div></div>
        <div class="table-responsive">
            <table class="table promo-table">
                <thead><tr><th scope="col">Promo / Kode</th><th scope="col">Periode</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
                <tbody>
                @forelse($promotions as $promo)
                    <tr>
                        <td><div class="promo-name">{{ $promo->name }}</div><span class="promo-code">{{ $promo->code ?? 'Otomatis' }}</span></td>
                        <td><div class="promo-period"><span>{{ $promo->start_at->format('d/m/Y H:i') }}</span><span>sampai {{ $promo->end_at->format('d/m/Y H:i') }}</span></div></td>
                        <td><span class="promo-status {{ $promo->is_active ? 'promo-status-active' : 'promo-status-inactive' }}">{{ $promo->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td><div class="promo-actions"><a class="promo-action" href="{{ route('promotions.edit', $promo) }}"><i class="far fa-edit" aria-hidden="true"></i>Edit / Nonaktifkan</a><a class="promo-action" href="{{ route('promotions.history', $promo) }}"><i class="fas fa-history" aria-hidden="true"></i>Riwayat</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="promo-empty"><div class="promo-empty-icon" aria-hidden="true"><i class="fas fa-ticket-alt"></i></div><strong>Belum ada promo.</strong><p>Buat promo atau voucher pertama melalui form di atas.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($promotions->hasPages())<div class="promo-pagination">{{ $promotions->links() }}</div>@endif
    </section>
</div>
@endsection
