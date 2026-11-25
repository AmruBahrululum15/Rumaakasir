<a class="nav-link" href="#" data-toggle="dropdown" aria-label="Notifikasi toko"><i class="fas fa-bell"></i><span class="badge badge-danger navbar-badge" @if(!$unreadNotifications) hidden @endif>{{ $unreadNotifications }}</span></a>
<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="max-height:70vh;overflow:auto;min-width:300px">
<span class="dropdown-header">{{ $unreadNotifications }} Notifikasi Belum Dibaca</span>
@forelse($navbarNotifications as $notification)
<form method="post" action="{{ route('notifications.open',$notification->id) }}" @if(isset($notification->data['booking_id'])) data-reservation-note="{{ $notification->id }}" @endif>@csrf
@if(isset($notification->data['booking_id']))
<button class="dropdown-item text-wrap {{ $notification->read_at?'':'font-weight-bold' }}" type="submit"><span class="d-block"><i class="fas fa-calendar mr-1" aria-hidden="true"></i>{{ $notification->data['title']??'Reservasi baru' }}</span><small class="d-block">{{ $notification->data['booking_code']??'' }} · {{ $notification->data['message']??'' }}</small><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small><span class="d-block text-warning">Lihat Reservasi</span></button>
@elseif(isset($notification->data['conversation_id']))
<button class="dropdown-item text-wrap {{ $notification->read_at?'':'font-weight-bold' }}" type="submit"><span class="d-block"><i class="fas fa-comment mr-1" aria-hidden="true"></i>{{ $notification->data['title']??$notification->data['message']??'Pesan baru' }}</span><small class="d-block">{{ $notification->data['message_preview']??'' }}</small><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small></button>
@else
<button class="dropdown-item text-wrap {{ $notification->read_at?'':'font-weight-bold' }}" type="submit"><span class="d-block">{{ $notification->data['title']??'Notifikasi' }}</span><small class="d-block">{{ $notification->data['order_number']??$notification->data['sale_code']??'' }} @if(isset($notification->data['total'])) • Rp{{ number_format($notification->data['total'],0,',','.') }}@endif</small><small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small></button>
@endif</form>
@empty<div class="dropdown-item text-muted">Belum ada notifikasi.</div>@endforelse
<div class="dropdown-divider"></div><form method="post" action="{{ route('notifications.read-all') }}">@csrf @method('patch')<button class="dropdown-item" type="submit">Tandai semua dibaca</button></form><a class="dropdown-item" href="{{ route('notifications.index') }}">Lihat semua notifikasi</a>
</div>
