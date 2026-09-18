@extends('layouts.app')

@section('content')
<style>
    .table-setup { max-width: 960px; margin: 0 auto; padding: 32px 20px; }
    .setup-header { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:24px; }
    .setup-title { margin:0 0 6px; color:#1f2937; font-size:28px; font-weight:700; }
    .setup-subtitle { margin:0; color:#6b7280; }
    .setup-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; margin-bottom:24px; }
    .stat-card, .setup-card { background:#fff; border:1px solid #e5e7eb; border-radius:16px; box-shadow:0 4px 16px rgba(15,23,42,.05); }
    .stat-card { padding:18px; }
    .stat-label { display:block; color:#6b7280; font-size:13px; margin-bottom:5px; }
    .stat-value { color:#111827; font-size:28px; font-weight:700; }
    .setup-card { padding:24px; margin-bottom:18px; }
    .setup-card h2 { font-size:19px; margin:0 0 7px; color:#1f2937; }
    .setup-card p { color:#6b7280; margin:0 0 20px; line-height:1.55; }
    .setup-form { display:flex; align-items:end; gap:12px; flex-wrap:wrap; }
    .field { flex:1; min-width:220px; }
    .field label { display:block; margin-bottom:7px; font-weight:600; color:#374151; }
    .field input { width:100%; height:43px; border:1px solid #d1d5db; border-radius:9px; padding:8px 11px; }
    .setup-btn { height:43px; border:0; border-radius:9px; padding:0 18px; background:#4f46e5; color:#fff; font-weight:600; cursor:pointer; }
    .setup-btn:hover { background:#4338ca; }
    .setup-btn-secondary { background:#fff; color:#374151; border:1px solid #d1d5db; text-decoration:none; display:inline-flex; align-items:center; }
    .setup-note { margin-top:16px; padding:12px 14px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; color:#92400e; font-size:13px; }
    @media (max-width:650px) { .setup-grid { grid-template-columns:1fr; } .setup-header { flex-direction:column; } .setup-form { align-items:stretch; } .setup-btn { width:100%; } }
</style>

<div class="table-setup">
    <div class="setup-header">
        <div>
            <h1 class="setup-title">Atur Meja & QR</h1>
            <p class="setup-subtitle">Setiap meja aktif otomatis mempunyai QR Code untuk pemesanan pelanggan.</p>
        </div>
        <a href="{{ route('tables.index') }}" class="setup-btn setup-btn-secondary"><i class="fas fa-arrow-left mr-2"></i>Kembali</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="setup-grid">
        <div class="stat-card"><span class="stat-label">Total meja</span><span class="stat-value">{{ $totalTables }}</span></div>
        <div class="stat-card"><span class="stat-label">Meja aktif</span><span class="stat-value text-success">{{ $activeTables }}</span></div>
        <div class="stat-card"><span class="stat-label">Meja nonaktif</span><span class="stat-value text-muted">{{ $inactiveTables }}</span></div>
    </div>

    <div class="setup-card">
        <h2>Atur jumlah meja aktif</h2>
        <p>Masukkan jumlah meja yang ingin tersedia. Jika jumlah dinaikkan, meja dan QR baru dibuat otomatis.</p>
        <form action="{{ route('tables.generate') }}" method="POST" class="setup-form">
            @csrf
            <div class="field">
                <label for="jumlah">Jumlah meja aktif</label>
                <input id="jumlah" type="number" name="jumlah" min="1" max="500" value="{{ old('jumlah', $activeTables ?: $totalTables) }}" required autofocus>
                @error('jumlah')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <button class="setup-btn" type="submit"><i class="fas fa-layer-group mr-2"></i>Simpan jumlah meja</button>
        </form>
        <div class="setup-note"><i class="fas fa-info-circle mr-1"></i>Jika jumlah dikurangi, meja tidak dihapus; meja terakhir akan dinonaktifkan agar riwayat pesanan tetap tersimpan.</div>
    </div>

    <div class="setup-card">
        <h2>Tambah satu meja khusus</h2>
        <p>Gunakan opsi ini bila Anda ingin memakai nama meja sendiri, misalnya “VIP 01” atau “Teras”.</p>
        <form action="{{ route('tables.store') }}" method="POST" class="setup-form">
            @csrf
            <div class="field">
                <label for="name">Nama meja</label>
                <input id="name" type="text" name="name" maxlength="100" value="{{ old('name') }}" placeholder="Contoh: Meja VIP 01" required>
                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
            <button class="setup-btn" type="submit"><i class="fas fa-plus mr-2"></i>Tambah meja</button>
        </form>
    </div>
</div>
@endsection
