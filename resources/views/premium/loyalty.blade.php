@extends('layouts.app')
@section('title', 'Loyalty Customer')
@section('content')
<style>
.loyalty-page { color:#253044; padding:8px 0 24px; }
.loyalty-page .loyalty-header { display:flex; align-items:center; gap:14px; margin-bottom:22px; }
.loyalty-page .loyalty-icon { display:flex; align-items:center; justify-content:center; width:48px; height:48px; flex-shrink:0; border-radius:14px; background:#fff0e3; color:#ed7119; font-size:20px; }
.loyalty-page h1 { margin:0 0 5px; font-size:26px; font-weight:700; letter-spacing:-.5px; }
.loyalty-page .loyalty-subtitle { margin:0; color:#738093; font-size:13px; line-height:1.6; }
.loyalty-page .loyalty-layout { display:grid; grid-template-columns:minmax(0,2fr) minmax(250px,1fr); gap:20px; align-items:start; margin-bottom:20px; }
.loyalty-page .loyalty-card { background:#fff; border:1px solid #ebe7e2; border-radius:16px; box-shadow:0 4px 18px rgba(35,45,60,.03); overflow:hidden; }
.loyalty-page .loyalty-card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:20px 24px; border-bottom:1px solid #f0eeeb; }
.loyalty-page h2 { margin:0; font-size:16px; font-weight:700; }
.loyalty-page .loyalty-card-header p { margin:5px 0 0; color:#738093; font-size:12px; line-height:1.5; }
.loyalty-page .loyalty-status { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; }
.loyalty-page .loyalty-status::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
.loyalty-page .loyalty-active { background:#eef8f2; color:#338457; }
.loyalty-page .loyalty-inactive { background:#f0f2f5; color:#788495; }
.loyalty-page .loyalty-form-body { padding:24px; }
.loyalty-page .loyalty-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:22px 24px; }
.loyalty-page .loyalty-field { min-width:0; }
.loyalty-page .loyalty-field label { display:block; margin-bottom:7px; font-size:12px; font-weight:600; }
.loyalty-page .form-control { width:100%; height:42px; padding:9px 12px; border:1px solid #e3e6eb; border-radius:9px; background:#fff; color:#253044; font-size:13px; box-shadow:none; }
.loyalty-page .form-control:focus { border-color:#efa66f; box-shadow:0 0 0 3px rgba(255,122,24,.1); }
.loyalty-page .loyalty-help { display:block; margin-top:7px; color:#8590a0; font-size:11px; line-height:1.6; }
.loyalty-page .loyalty-form-footer { display:flex; justify-content:flex-end; padding:16px 24px; border-top:1px solid #f0eeeb; background:#fdfcfb; }
.loyalty-page .loyalty-save { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:10px 20px; border:1px solid #ed7119; border-radius:9px; background:#ed7119; color:#fff; font-size:13px; font-weight:600; cursor:pointer; }
.loyalty-page .loyalty-save:hover { background:#d9600c; border-color:#d9600c; }
.loyalty-page .loyalty-guide { padding:24px; }
.loyalty-page .loyalty-guide h2 { margin-bottom:20px; }
.loyalty-page .loyalty-step { display:flex; align-items:flex-start; gap:12px; }
.loyalty-page .loyalty-step + .loyalty-step { margin-top:20px; }
.loyalty-page .loyalty-step-icon { display:flex; align-items:center; justify-content:center; width:32px; height:32px; flex-shrink:0; border-radius:9px; background:#fff4e9; color:#c96a25; font-size:13px; }
.loyalty-page .loyalty-step strong { display:block; margin-bottom:4px; font-size:12px; font-weight:600; }
.loyalty-page .loyalty-step p { margin:0; color:#7d899a; font-size:12px; line-height:1.7; }
.loyalty-page .loyalty-guide-note { margin-top:22px; padding:12px; border-radius:9px; background:#faf8f5; color:#8590a0; font-size:11px; line-height:1.7; }
.loyalty-page .loyalty-table { width:100%; margin:0; }
.loyalty-page .loyalty-table th { padding:13px 24px; border:0; background:#faf9f7; color:#8490a0; font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; }
.loyalty-page .loyalty-table td { padding:16px 24px; border-top:1px solid #f0eeeb; vertical-align:middle; font-size:13px; }
.loyalty-page .loyalty-table th:last-child,.loyalty-page .loyalty-table td:last-child { text-align:right; }
.loyalty-page .loyalty-customer { display:flex; align-items:center; gap:11px; }
.loyalty-page .loyalty-avatar { display:flex; align-items:center; justify-content:center; width:34px; height:34px; flex-shrink:0; border-radius:10px; background:#f7f5f2; color:#ad947e; font-size:13px; }
.loyalty-page .loyalty-name { font-weight:600; overflow-wrap:anywhere; }
.loyalty-page .loyalty-history-link { display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:8px 12px; border:1px solid #eddfd2; border-radius:8px; background:#fffbf7; color:#a6521c; font-size:12px; text-decoration:none; white-space:nowrap; }
.loyalty-page .loyalty-history-link:hover { background:#fff1e4; border-color:#ecc5a8; color:#964711; }
.loyalty-page .loyalty-empty { padding:30px 20px; text-align:center; }
.loyalty-page .loyalty-empty-icon { display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; margin-bottom:12px; border-radius:14px; background:#fff4e9; color:#e5a16e; font-size:19px; }
.loyalty-page .loyalty-empty strong { display:block; margin-bottom:5px; font-size:13px; }
.loyalty-page .loyalty-empty p { margin:0; color:#8590a0; font-size:12px; }
.loyalty-page .loyalty-pagination { padding:16px 24px; border-top:1px solid #f0eeeb; }
.loyalty-page .loyalty-pagination nav,.loyalty-page .loyalty-pagination .pagination { margin-bottom:0; }
@media(max-width:991px) { .loyalty-page .loyalty-layout { grid-template-columns:minmax(0,1fr); } }
@media(max-width:575px) {
.loyalty-page h1 { font-size:22px; }
.loyalty-page .loyalty-header { gap:10px; margin-bottom:18px; }
.loyalty-page .loyalty-card-header { padding:18px; align-items:flex-start; }
.loyalty-page .loyalty-form-body,.loyalty-page .loyalty-guide { padding:18px; }
.loyalty-page .loyalty-fields { grid-template-columns:minmax(0,1fr); gap:18px; }
.loyalty-page .loyalty-form-footer { padding:14px 18px; }
.loyalty-page .loyalty-save { width:100%; }
.loyalty-page .loyalty-table th,.loyalty-page .loyalty-table td { padding:14px 12px; }
.loyalty-page .loyalty-customer { gap:8px; }
.loyalty-page .loyalty-avatar { display:none; }
.loyalty-page .loyalty-history-link { font-size:11px; padding:8px; white-space:normal; }
.loyalty-page .loyalty-pagination { padding:16px 18px; }
}
</style>
<div class="loyalty-page">
    <header class="loyalty-header">
        <div class="loyalty-icon" aria-hidden="true"><i class="fas fa-star"></i></div>
        <div><h1>Loyalty Customer</h1><p class="loyalty-subtitle">Kelola program poin dan penghargaan untuk pelanggan setia toko Anda.</p></div>
    </header>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="loyalty-layout">
        @can('settings.edit')
        <form action="{{ route('loyalty.update') }}" method="POST" class="loyalty-card">
            @csrf @method('PUT')
            <div class="loyalty-card-header">
                <div><h2>Pengaturan loyalty</h2><p>Atur perolehan dan penukaran poin pelanggan.</p></div>
                <span class="loyalty-status {{ $settings['loyalty_enabled'] ? 'loyalty-active' : 'loyalty-inactive' }}">{{ $settings['loyalty_enabled'] ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
            <div class="loyalty-form-body">
                <div class="loyalty-fields">
                    <div class="loyalty-field">
                        <label for="loyalty-enabled">Status loyalty</label>
                        <select id="loyalty-enabled" name="loyalty_enabled" class="form-control" aria-describedby="loyalty-status-help"><option value="0">Nonaktif</option><option value="1" @selected($settings['loyalty_enabled'])>Aktif</option></select>
                        <small id="loyalty-status-help" class="loyalty-help">Aktifkan atau nonaktifkan program loyalty toko.</small>
                    </div>
                    @foreach([
                        'spend_per_point' => ['Belanja rupiah per 1 poin', 'Nominal belanja untuk memperoleh 1 poin.'],
                        'point_value' => ['Nilai rupiah per poin saat redeem', 'Nilai potongan rupiah untuk setiap poin yang ditukar.'],
                        'minimum_redeem_points' => ['Minimum poin redeem', 'Jumlah poin minimum untuk melakukan penukaran.'],
                    ] as $key => [$label, $help])
                        <div class="loyalty-field">
                            <label for="loyalty-{{ $key }}">{{ $label }}</label>
                            <input id="loyalty-{{ $key }}" type="number" min="1" name="{{ $key }}" value="{{ old($key, $settings[$key]) }}" class="form-control" aria-describedby="loyalty-{{ $key }}-help" required>
                            <small id="loyalty-{{ $key }}-help" class="loyalty-help">{{ $help }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="loyalty-form-footer"><button type="submit" class="loyalty-save"><i class="fas fa-check" aria-hidden="true"></i>Simpan pengaturan</button></div>
        </form>
        @endcan
        <aside class="loyalty-card loyalty-guide" aria-labelledby="loyalty-guide-title">
            <h2 id="loyalty-guide-title">Cara kerja loyalty</h2>
            <div class="loyalty-step"><span class="loyalty-step-icon" aria-hidden="true"><i class="fas fa-shopping-bag"></i></span><div><strong>Pelanggan berbelanja</strong><p>Poin diperoleh setelah pembayaran berhasil.</p></div></div>
            <div class="loyalty-step"><span class="loyalty-step-icon" aria-hidden="true"><i class="fas fa-star"></i></span><div><strong>Kumpulkan poin</strong><p>Perolehan poin mengikuti nominal belanja yang diatur oleh toko.</p></div></div>
            <div class="loyalty-step"><span class="loyalty-step-icon" aria-hidden="true"><i class="fas fa-gift"></i></span><div><strong>Tukar poin di kasir</strong><p>Redeem tersedia pada transaksi tunai kasir sesuai minimum poin.</p></div></div>
            <div class="loyalty-guide-note">Buka saldo & riwayat poin pelanggan untuk melihat perolehan dan penukaran poin.</div>
        </aside>
    </div>
    <section class="loyalty-card" aria-labelledby="loyalty-customers-title">
        <div class="loyalty-card-header"><div><h2 id="loyalty-customers-title">Pelanggan loyalty</h2><p>Lihat saldo dan riwayat poin masing-masing pelanggan.</p></div></div>
        <div class="table-responsive">
            <table class="table loyalty-table">
                <thead><tr><th scope="col">Customer</th><th scope="col">Riwayat</th></tr></thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr><td><div class="loyalty-customer"><span class="loyalty-avatar" aria-hidden="true"><i class="far fa-user"></i></span><span class="loyalty-name">{{ $customer->name }}</span></div></td><td><a class="loyalty-history-link" href="{{ route('loyalty.show', $customer) }}"><i class="fas fa-history" aria-hidden="true"></i>Saldo & riwayat poin</a></td></tr>
                    @empty
                        <tr><td colspan="2"><div class="loyalty-empty"><div class="loyalty-empty-icon" aria-hidden="true"><i class="fas fa-users"></i></div><strong>Belum ada customer.</strong><p>Pelanggan akan tampil di sini setelah terdaftar di toko.</p></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())<div class="loyalty-pagination">{{ $customers->links() }}</div>@endif
    </section>
</div>
@endsection
