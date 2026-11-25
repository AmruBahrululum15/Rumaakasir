@extends('layouts.app')
@section('title', 'Notifikasi')
@section('description', 'Notifikasi untuk akun Super Admin Anda.')
@section('content')
<section class="content"><div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-header d-flex align-items-center justify-content-between">
        <h2 class="h6 mb-0">{{ $unreadCount }} belum dibaca</h2>
        @if($unreadCount)<form method="POST" action="{{ route('super-admin.notifications.read-all') }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary">Tandai semua dibaca</button></form>@endif
    </div><div class="card-body">
        @forelse($notifications as $notification)
            <article class="border-bottom py-3">
                <h3 class="h6">{{ $notification->data['title'] ?? 'Notifikasi' }} @if(!$notification->read_at)<span class="badge badge-warning">Baru</span>@endif</h3>
                <p>{{ $notification->data['message'] ?? $notification->data['body'] ?? 'Anda memiliki notifikasi baru.' }}</p>
                <small class="text-muted">{{ $notification->created_at->format('d M Y H:i') }}</small>
                @if(isset($notification->data['registration_id']))<a class="btn btn-sm btn-outline-primary" href="{{ route('super-admin.store-registrations.show', $notification->data['registration_id']) }}">Review Pengajuan</a>@endif
                @if(!$notification->read_at)<form method="POST" action="{{ route('super-admin.notifications.read', $notification->id) }}" class="mt-2">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">Tandai dibaca</button></form>@endif
            </article>
        @empty<p class="text-center text-muted py-4 mb-0">Belum ada notifikasi.</p>@endforelse
    </div><div class="card-footer">{{ $notifications->links() }}</div></div>
</div></section>
@endsection
