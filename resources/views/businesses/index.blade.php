@extends('layouts.app')

@section('title', 'Manajemen Toko')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Manajemen Toko</h1>
        <a href="{{ route('businesses.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Toko</a>
    </div>
</div>
<section class="content"><div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="sa-stats">
        <a class="sa-stat text-reset" href="{{ route('super-admin.store-registrations.index', ['status' => 'pending']) }}"><p class="sa-stat-label">Pengajuan Toko Pending</p><div class="sa-stat-value">{{ \App\Models\StoreRegistration::where('status', 'pending')->count() }}</div><p class="sa-stat-caption">Review pengajuan toko baru</p></a>
        @foreach([
            ['Total Toko', $totalBusinesses, 'fas fa-store', 'Seluruh tenant platform'],
            ['Toko Aktif', $activeBusinesses, 'far fa-check-circle', 'Tenant berstatus aktif'],
            ['Toko Nonaktif', $inactiveBusinesses, 'far fa-pause-circle', 'Tenant berstatus nonaktif'],
            ['Total Pengguna', $totalUsers, 'fas fa-users', 'Pengguna yang terdaftar'],
        ] as [$label, $value, $icon, $caption])
            <div class="sa-stat"><div class="sa-stat-top"><p class="sa-stat-label">{{ $label }}</p><span class="sa-stat-icon"><i class="{{ $icon }}" aria-hidden="true"></i></span></div><div class="sa-stat-value">{{ $value }}</div><p class="sa-stat-caption">{{ $caption }}</p></div>
        @endforeach
    </div>
    <div class="card"><div class="sa-table-heading"><div><h2>{{ request()->query('section') === 'subscriptions' ? 'Paket & Langganan Toko' : 'Daftar Toko' }}</h2><p>Informasi tenant yang terhubung dengan platform.</p></div><span class="sa-table-label">{{ $totalBusinesses }} tenant</span></div><div class="card-body p-0"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>ID</th><th>Toko</th><th>Atasan</th><th>Paket</th><th>Dibuat</th><th>Telepon</th><th>User</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
            <tbody>@forelse($businesses as $business)
                <tr>
                    <td>{{ $business->id }}</td><td>{{ $business->name }}</td><td>{{ $business->owner?->name ?? '-' }}</td><td>{{ $packageNames[$business->subscription_plan] ?? ucfirst($business->subscription_plan) }}</td><td>{{ $business->created_at?->format('d M Y') ?? '-' }}</td><td>{{ $business->phone ?: '-' }}</td><td>{{ $business->users_count }}</td>
                    <td><span class="badge badge-{{ $business->is_active ? 'success' : 'secondary' }}">{{ $business->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="text-right">
                        @if($business->allowsOperations())<form method="POST" action="{{ route('businesses.activate', $business) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-primary">Kelola POS</button></form>@endif
                        <a href="{{ route('businesses.show', $business) }}" class="btn btn-sm btn-outline-secondary">Detail</a>
                        <form method="POST" action="{{ route('businesses.status', $business) }}" class="d-inline">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $business->is_active ? 0 : 1 }}"><button class="btn btn-sm btn-outline-secondary">{{ $business->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button></form>
                        <a href="{{ route('businesses.edit', $business) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                    </td>
                </tr>
            @empty<tr><td colspan="9" class="text-center text-muted py-4">Belum ada toko.</td></tr>@endforelse</tbody>
        </table>
    </div></div><div class="card-footer">{{ $businesses->links() }}</div></div>
</div></section>
@endsection
