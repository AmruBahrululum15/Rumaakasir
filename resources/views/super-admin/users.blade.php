@extends('layouts.app')
@section('title', $ownersOnly ? 'Atasan / Pemilik Toko' : 'Semua Pengguna')
@section('description', $ownersOnly ? 'Daftar pemilik dan Atasan di seluruh tenant.' : 'Lihat pengguna, toko, dan role di seluruh platform.')
@section('content')
<section class="content"><div class="container-fluid"><div class="card">
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Toko</th><th>Role</th><th>Status</th><th>Dibuat</th></tr></thead>
        <tbody>@forelse($users as $user)<tr>
            <td>{{ $user->name }}</td><td>{{ $user->username ?: '-' }}</td><td>{{ $user->email }}</td>
            <td>{{ $user->business?->name ?? '-' }}</td><td>{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
            <td>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }} / {{ $user->status }}</td><td>{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
        </tr>@empty<tr><td colspan="7" class="text-center text-muted py-5">Belum ada pengguna.</td></tr>@endforelse</tbody>
    </table></div></div><div class="card-footer">{{ $users->links() }}</div>
</div></div></section>
@endsection
