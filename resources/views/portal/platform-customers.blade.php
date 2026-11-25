@extends('layouts.app')
@section('title', $staff ? 'Pelanggan Portal Toko' : 'Pelanggan Platform')
@section('content')
<style>
.portal-customers-page { color:#253044; padding:8px 0 24px; }
.portal-customers-page .pc-header { display:flex; align-items:center; gap:14px; margin-bottom:22px; }
.portal-customers-page .pc-icon { display:flex; align-items:center; justify-content:center; width:48px; height:48px; flex-shrink:0; border-radius:14px; background:#fff0e3; color:#ed7119; font-size:20px; }
.portal-customers-page h1 { margin:0 0 5px; font-size:26px; font-weight:700; letter-spacing:-.5px; }
.portal-customers-page .pc-subtitle { margin:0; color:#738093; font-size:13px; line-height:1.6; }
.portal-customers-page .pc-card { margin-bottom:20px; background:#fff; border:1px solid #ebe7e2; border-radius:16px; box-shadow:0 4px 18px rgba(35,45,60,.03); overflow:hidden; }
.portal-customers-page .pc-card-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:20px 24px; border-bottom:1px solid #f0eeeb; }
.portal-customers-page h2 { margin:0; font-size:16px; font-weight:700; }
.portal-customers-page .pc-card-header p { margin:5px 0 0; color:#738093; font-size:12px; line-height:1.6; }
.portal-customers-page .pc-count { padding:6px 10px; border-radius:8px; background:#f7f5f2; color:#738093; font-size:11px; font-weight:600; white-space:nowrap; }
.portal-customers-page .pc-filter { display:grid; grid-template-columns:minmax(0,1fr) minmax(160px,240px) auto; align-items:end; gap:14px; padding:20px 24px; }
.portal-customers-page .pc-field { min-width:0; }
.portal-customers-page .pc-field label { display:block; margin-bottom:7px; color:#536174; font-size:12px; font-weight:600; }
.portal-customers-page .form-control { width:100%; height:42px; border:1px solid #e3e6eb; border-radius:9px; padding:9px 12px; background:#fff; color:#253044; font-size:13px; box-shadow:none; }
.portal-customers-page .form-control::placeholder { color:#9ca5b1; }
.portal-customers-page .form-control:focus { border-color:#efa66f; box-shadow:0 0 0 3px rgba(255,122,24,.1); }
.portal-customers-page .pc-search { position:relative; }
.portal-customers-page .pc-search i { position:absolute; top:15px; left:13px; color:#9ca5b1; font-size:12px; pointer-events:none; }
.portal-customers-page .pc-search input { padding-left:35px; }
.portal-customers-page .pc-button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; padding:10px 18px; border:1px solid #ed7119; border-radius:9px; background:#ed7119; color:#fff; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap; }
.portal-customers-page .pc-button:hover { background:#d9600c; border-color:#d9600c; }
.portal-customers-page .pc-table { width:100%; margin:0; }
.portal-customers-page .pc-table th { padding:13px 18px; background:#faf9f7; border:0; color:#8490a0; font-size:10px; font-weight:700; letter-spacing:.5px; text-transform:uppercase; white-space:nowrap; }
.portal-customers-page .pc-table td { padding:18px; border-top:1px solid #f0eeeb; vertical-align:middle; font-size:12px; }
.portal-customers-page .pc-table th:first-child,.portal-customers-page .pc-table td:first-child { padding-left:24px; }
.portal-customers-page .pc-table th:last-child,.portal-customers-page .pc-table td:last-child { padding-right:24px; }
.portal-customers-page .pc-person { display:flex; align-items:center; gap:10px; min-width:170px; }
.portal-customers-page .pc-avatar { display:flex; align-items:center; justify-content:center; width:34px; height:34px; flex-shrink:0; border-radius:10px; background:#fff4e9; color:#c88451; }
.portal-customers-page .pc-name { font-weight:600; overflow-wrap:anywhere; }
.portal-customers-page .pc-contact { color:#637084; white-space:nowrap; }
.portal-customers-page .pc-date { color:#738093; white-space:nowrap; }
.portal-customers-page .pc-status { display:inline-flex; align-items:center; gap:6px; padding:5px 9px; border-radius:7px; font-size:11px; font-weight:600; white-space:nowrap; background:#f0f2f5; color:#788495; }
.portal-customers-page .pc-status::before { content:''; width:5px; height:5px; border-radius:50%; background:currentColor; }
.portal-customers-page .pc-status-active { background:#eef8f2; color:#338457; }
.portal-customers-page .pc-status-suspended { background:#fff1ec; color:#b8583d; }
.portal-customers-page .pc-stat { text-align:center; }
.portal-customers-page .pc-stat-value { display:inline-flex; align-items:center; justify-content:center; min-width:27px; height:27px; padding:0 6px; border-radius:7px; background:#f5f6f8; color:#637084; font-size:11px; font-weight:600; }
.portal-customers-page .pc-detail { display:inline-flex; padding:7px 10px; border:1px solid #eddfd2; border-radius:7px; color:#a6521c; font-size:12px; text-decoration:none; }
.portal-customers-page .pc-detail:hover { background:#fff4e9; }
.portal-customers-page .pc-empty { padding:32px 20px; text-align:center; }
.portal-customers-page .pc-empty-icon { display:inline-flex; align-items:center; justify-content:center; width:48px; height:48px; margin-bottom:12px; border-radius:14px; background:#fff4e9; color:#e5a16e; font-size:19px; }
.portal-customers-page .pc-empty strong { display:block; margin-bottom:5px; font-size:13px; }
.portal-customers-page .pc-empty p { margin:0; color:#8590a0; font-size:12px; }
.portal-customers-page .pc-pagination { padding:16px 24px; border-top:1px solid #f0eeeb; }
.portal-customers-page .pc-pagination nav,.portal-customers-page .pc-pagination .pagination { margin-bottom:0; }
.portal-customers-page .pc-invite-body { padding:22px 24px; }
.portal-customers-page .pc-note { display:flex; gap:10px; margin-bottom:20px; padding:13px 14px; border-radius:10px; background:#faf8f5; color:#758092; font-size:12px; line-height:1.7; }
.portal-customers-page .pc-note i { margin-top:4px; color:#ca8d5e; }
.portal-customers-page .pc-invite-fields { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr); gap:20px; }
.portal-customers-page .pc-help { display:block; margin-top:6px; color:#8590a0; font-size:11px; line-height:1.6; }
.portal-customers-page .pc-footer { display:flex; justify-content:flex-end; padding:16px 24px; border-top:1px solid #f0eeeb; background:#fdfcfb; }
@media(max-width:767px) {
.portal-customers-page h1 { font-size:22px; }
.portal-customers-page .pc-header { gap:10px; margin-bottom:18px; }
.portal-customers-page .pc-card-header { padding:18px; align-items:flex-start; }
.portal-customers-page .pc-filter { grid-template-columns:minmax(0,1fr) auto; padding:18px; }
.portal-customers-page .pc-filter .pc-field:first-child { grid-column:1 / -1; }
.portal-customers-page .pc-invite-fields { grid-template-columns:minmax(0,1fr); gap:16px; }
.portal-customers-page .pc-invite-body { padding:18px; }
.portal-customers-page .pc-footer { padding:14px 18px; }
.portal-customers-page .pc-footer .pc-button { width:100%; }
.portal-customers-page .pc-pagination { padding:16px 18px; }
}
</style>
<div class="portal-customers-page">
    <header class="pc-header">
        <div class="pc-icon" aria-hidden="true"><i class="fas fa-users"></i></div>
        <div><h1>{{ $staff ? 'Pelanggan Portal Toko' : 'Pelanggan Platform' }}</h1><p class="pc-subtitle">{{ $staff ? 'Akun pelanggan yang terhubung dengan toko Anda. Statistik hanya untuk toko ini.' : 'Akun pelanggan Rumaakasir dan ringkasan interaksi di platform.' }}</p></div>
    </header>
    <section class="pc-card" aria-labelledby="pc-list-title">
        <div class="pc-card-header"><div><h2 id="pc-list-title">Daftar pelanggan</h2><p>Temukan pelanggan dan lihat ringkasan aktivitasnya.</p></div><span class="pc-count">{{ number_format($accounts->total(), 0, ',', '.') }} pelanggan</span></div>
        <form method="get" class="pc-filter">
            <div class="pc-field"><label for="pc-search">Cari pelanggan</label><div class="pc-search"><i class="fas fa-search" aria-hidden="true"></i><input id="pc-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau nomor HP"></div></div>
            <div class="pc-field"><label for="pc-status">Status pelanggan</label><select id="pc-status" class="form-control" name="status"><option value="">Semua status</option>@foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'] as $key => $label)<option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>@endforeach</select></div>
            <button type="submit" class="pc-button"><i class="fas fa-search" aria-hidden="true"></i>Cari</button>
        </form>
        <div class="table-responsive">
            <table class="table table-hover pc-table">
                <thead><tr><th scope="col">Nama</th><th scope="col">Email</th><th scope="col">Nomor HP</th><th scope="col">Status</th><th scope="col">Daftar</th>@unless($staff)<th scope="col">Login terakhir</th><th scope="col" class="pc-stat">Toko</th>@endunless<th scope="col" class="pc-stat">Order</th><th scope="col" class="pc-stat">Booking</th><th scope="col" class="pc-stat">Percakapan</th>@unless($staff)<th scope="col">Detail</th>@endunless</tr></thead>
                <tbody>
                @forelse($accounts as $account)
                    <tr>
                        <td><div class="pc-person"><span class="pc-avatar" aria-hidden="true"><i class="far fa-user"></i></span><span class="pc-name">{{ $account->name }}</span></div></td>
                        <td class="pc-contact">{{ $account->email }}</td><td class="pc-contact">{{ $account->phone }}</td>
                        <td><span class="pc-status pc-status-{{ $account->status }}">{{ ['active' => 'Aktif', 'inactive' => 'Nonaktif', 'suspended' => 'Ditangguhkan'][$account->status] ?? $account->status }}</span></td>
                        <td class="pc-date">{{ $account->created_at?->format('d M Y') }}</td>
                        @unless($staff)<td class="pc-date">{{ $account->last_login_at?->format('d M Y H:i') ?? 'Belum tercatat' }}</td><td class="pc-stat"><span class="pc-stat-value">{{ $account->memberships_count }}</span></td>@endunless
                        <td class="pc-stat"><span class="pc-stat-value">{{ $account->orders_count }}</span></td><td class="pc-stat"><span class="pc-stat-value">{{ $account->bookings_count }}</span></td><td class="pc-stat"><span class="pc-stat-value">{{ $account->conversations_count }}</span></td>
                        @unless($staff)<td><a href="{{ route('super-admin.platform-customers.show', $account) }}" class="pc-detail">Detail</a></td>@endunless
                    </tr>
                @empty
                    <tr><td colspan="{{ $staff ? 8 : 11 }}"><div class="pc-empty"><div class="pc-empty-icon" aria-hidden="true"><i class="fas fa-users"></i></div><strong>Belum ada pelanggan yang sesuai.</strong><p>Coba kata pencarian atau status lain untuk menemukan pelanggan.</p></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($accounts->hasPages())<div class="pc-pagination">{{ $accounts->links() }}</div>@endif
    </section>
    @if($staff && auth()->user()->can('customers.edit'))
        <section class="pc-card" aria-labelledby="pc-invite-title">
            <div class="pc-card-header"><div><h2 id="pc-invite-title">Hubungkan pelanggan QR lama</h2><p>Kirim permintaan konfirmasi untuk menghubungkan pelanggan toko dengan akun portal.</p></div></div>
            <form method="post" action="{{ route('portal-customers.invite') }}">
                @csrf
                <div class="pc-invite-body">
                    <div class="pc-note"><i class="fas fa-info-circle" aria-hidden="true"></i><span>Periksa pelanggan terlebih dahulu. Email atau nomor HP harus cocok; pelanggan wajib mengonfirmasi melalui tautan email sambil login. Akun dan data pelanggan tidak digabung otomatis.</span></div>
                    <div class="pc-invite-fields">
                        <div class="pc-field"><label for="pc-customer-id">ID pelanggan toko</label><input id="pc-customer-id" class="form-control" type="number" min="1" name="customer_id" placeholder="Masukkan ID pelanggan" aria-describedby="pc-customer-help" required><small id="pc-customer-help" class="pc-help">Gunakan ID pelanggan QR yang sudah terdaftar di toko.</small></div>
                        <div class="pc-field"><label for="pc-customer-email">Email akun customer</label><input id="pc-customer-email" class="form-control" type="email" name="email" placeholder="Contoh: pelanggan@email.com" aria-describedby="pc-email-help" required><small id="pc-email-help" class="pc-help">Konfirmasi dikirim ke email akun pelanggan.</small></div>
                    </div>
                </div>
                <div class="pc-footer"><button type="submit" class="pc-button"><i class="far fa-envelope" aria-hidden="true"></i>Kirim konfirmasi</button></div>
            </form>
        </section>
    @endif
</div>
@endsection
