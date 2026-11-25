@php($navigationBusiness = app(\App\Tenancy\TenantContext::class)->business())
@if($navigationBusiness?->business_type !== null)
@if(auth()->user()->hasAnyRole(['Atasan','Admin']) && auth()->user()->can('customers.view') && app(\App\Services\CustomerPortal::class)->active($navigationBusiness))
<li class="nav-item"><a href="{{ route('portal-customers.index') }}" class="nav-link {{ request()->routeIs('portal-customers.*')?'active':'' }}"><i class="nav-icon fas fa-user-friends"></i><p>Pelanggan Portal Toko</p></a></li>
@endif
@foreach(app(\App\Services\PremiumFeatures::class)->catalog($navigationBusiness->business_type) as $featureKey=>$definition)
@if(isset($definition['navigation']) && app(\App\Services\PackageAccess::class)->allows($featureKey,$navigationBusiness))
@php($nav = $definition['navigation'])
@if(($featureKey==='customer_chat'?app(\App\Services\CustomerChat::class)->canRead(auth()->user()):auth()->user()->can($nav['permission'])) && (!$nav['roles'] || auth()->user()->hasAnyRole($nav['roles'])))
<li class="nav-item"><a href="{{ route($nav['route']) }}" class="nav-link {{ request()->routeIs($nav['active']) ? 'active' : '' }}"><i class="nav-icon fas {{ $nav['icon'] }}"></i><p>{{ $nav['label'] }}@if($featureKey==='booking')@php($reservationUnread=app(\App\Services\StaffNotifications::class)->query(auth()->user())->whereNull('read_at')->whereNotNull('data->booking_id')->count())<span class="badge badge-danger right" data-reservation-unread @if(!$reservationUnread) hidden @endif>{{ $reservationUnread }}</span>@endif @if($featureKey==='customer_chat')@php($chatUnread=app(\App\Services\CustomerChat::class)->unreadCount(auth()->user()))<span class="badge badge-danger right" data-chat-unread @if(!$chatUnread) hidden @endif>{{ $chatUnread }}</span>@endif</p></a></li>
@endif
@endif
@endforeach
@endif


