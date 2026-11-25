@php
    $saUser = auth()->user();
    $saUnreadCount = $saUser->unreadNotifications()->count();
    $pendingStoreCount = \App\Models\StoreRegistration::where('status', 'pending')->count();
    $saGroups = [
        'DASHBOARD' => [
            ['Dashboard', 'fas fa-chart-pie', route('super-admin.dashboard'), request()->routeIs('super-admin.dashboard')],
        ],
        'MANAJEMEN TENANT' => [
            ['Kelola Toko', 'fas fa-store', route('businesses.index'), request()->routeIs('businesses.*') && ! $saSubscriptions],
            ['Pengajuan Toko', 'fas fa-clipboard-list', route('super-admin.store-registrations.index'), request()->routeIs('super-admin.store-registrations.*')],
            ['Paket & Langganan', 'far fa-credit-card', route('super-admin.subscriptions.index'), $saSubscriptions],
        ],
        'PENGGUNA' => [
            ['Pelanggan Platform', 'fas fa-user-friends', route('super-admin.platform-customers.index'), request()->routeIs('super-admin.platform-customers.*')],
            ['Semua Pengguna', 'fas fa-users', route('super-admin.users.index'), request()->routeIs('super-admin.users.*')],
            ['Atasan / Pemilik Toko', 'fas fa-user-tie', route('super-admin.owners.index'), request()->routeIs('super-admin.owners.*')],
        ],
        'PLATFORM' => [
            ['Fitur & Paket', 'fas fa-layer-group', route('packages.index'), request()->routeIs('packages.*')],
            ['Pengaturan Platform', 'fas fa-sliders-h', route('super-admin.platform-settings.index'), request()->routeIs('super-admin.platform-settings.*')],
        ],
        'MONITORING' => [
            ['Customer Portal', 'fas fa-store', route('super-admin.customer-portal'), request()->routeIs('super-admin.customer-portal')],
            ['Aktivitas', 'fas fa-history', route('super-admin.activities.index'), request()->routeIs('super-admin.activities.*')],
            ['Notifikasi', 'far fa-bell', route('super-admin.notifications.index'), request()->routeIs('super-admin.notifications.*')],
        ],
        'AKUN' => [
            ['Profil', 'far fa-user-circle', route('super-admin.profile.edit'), request()->routeIs('super-admin.profile.*')],
            ['Pengaturan', 'fas fa-cog', route('super-admin.settings.index'), request()->routeIs('super-admin.settings.*')],
        ],
    ];
@endphp

<nav class="main-header navbar sa-topbar" aria-label="Navigasi atas Super Admin">
    <div class="sa-topbar-left">
        <button type="button" class="sa-icon-button" data-sa-toggle aria-label="Tutup sidebar" aria-controls="sa-sidebar" aria-expanded="true"><i class="fas fa-bars" aria-hidden="true"></i></button>
        <div class="sa-topbar-context"><span class="sa-topbar-title">{{ $saTitle }}</span><span class="sa-topbar-breadcrumb">Super Admin <span aria-hidden="true">/</span> {{ $saTitle }}</span></div>
    </div>
    <div class="sa-topbar-right">
        <div class="dropdown">
            <button type="button" class="sa-icon-button sa-notification-toggle" data-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi{{ $saUnreadCount ? ', '.$saUnreadCount.' belum dibaca' : '' }}">
                <i class="far fa-bell" aria-hidden="true"></i>
                @if($saUnreadCount > 0)<span class="sa-notification-dot" aria-hidden="true"></span>@endif
            </button>
            <div class="dropdown-menu dropdown-menu-right sa-dropdown">
                <div class="sa-dropdown-heading">Notifikasi @if($saUnreadCount > 0)<span class="sa-count">{{ $saUnreadCount }}</span>@endif</div>
                <p class="sa-dropdown-hint">{{ $saUnreadCount > 0 ? $saUnreadCount.' notifikasi belum dibaca.' : 'Tidak ada notifikasi baru.' }}</p>
                <a class="dropdown-item" href="{{ route('super-admin.notifications.index') }}"><i class="far fa-bell" aria-hidden="true"></i>Lihat notifikasi</a>
            </div>
        </div>
        <span class="sa-topbar-divider" aria-hidden="true"></span>
        <div class="dropdown">
            <button type="button" class="sa-account-toggle" data-toggle="dropdown" aria-expanded="false" aria-label="Menu akun Super Admin">
                @include('partials.super-admin.avatar')
                <span class="sa-account-copy"><strong>Super Admin</strong><small>Platform Administrator</small></span>
                <i class="fas fa-chevron-down sa-chevron" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right sa-dropdown">
                <div class="sa-dropdown-heading sa-account-name">{{ $saUser->name }}</div>
                <a class="dropdown-item" href="{{ route('super-admin.profile.edit') }}"><i class="far fa-user-circle" aria-hidden="true"></i>Profil</a>
                <a class="dropdown-item" href="{{ route('super-admin.settings.index') }}"><i class="fas fa-cog" aria-hidden="true"></i>Pengaturan</a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item sa-logout" type="submit"><i class="fas fa-sign-out-alt" aria-hidden="true"></i>Logout</button></form>
            </div>
        </div>
    </div>
</nav>

<button type="button" class="sa-sidebar-backdrop" data-sa-close aria-label="Tutup navigasi"></button>
<aside id="sa-sidebar" class="main-sidebar sa-sidebar" aria-label="Sidebar Super Admin">
    <a href="{{ route('super-admin.dashboard') }}" class="sa-brand" title="{{ $platformName }}">
        <span class="sa-brand-mark">@if($platformLogo)<img src="{{ asset($platformLogo) }}" alt="">@else<i class="fas fa-layer-group" aria-hidden="true"></i>@endif</span>
        <span class="sa-brand-copy"><strong>{{ $platformName }}</strong><small>SaaS Management</small></span>
    </a>
    <div class="sa-sidebar-scroll">
        <nav aria-label="Menu platform">
            @foreach($saGroups as $heading => $items)
                <section class="sa-nav-group" aria-label="{{ $heading }}">
                    <h2 class="sa-nav-heading">{{ $heading }}</h2>
                    <ul class="sa-nav-list">
                        @foreach($items as [$label, $icon, $href, $active])
                            <li>
                                    <a class="sa-nav-link{{ $active ? ' is-active' : '' }}" href="{{ $href }}" title="{{ $label }}" @if($active) aria-current="page" @endif>
                                        <i class="{{ $icon }}" aria-hidden="true"></i><span class="sa-nav-label">{{ $label }}</span>
                                        @if($label === 'Notifikasi' && $saUnreadCount > 0)<span class="sa-count">{{ $saUnreadCount }}</span>@endif
                                        @if($label === 'Pengajuan Toko' && $pendingStoreCount > 0)<span class="sa-count">{{ $pendingStoreCount }}</span>@endif
                                    </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </nav>
    </div>
    <div class="sa-sidebar-profile">
        @include('partials.super-admin.avatar')
        <span class="sa-profile-copy"><strong>Super Admin</strong><small>Platform Administrator</small></span>
        <span class="sa-profile-indicator" title="Sesi aktif" aria-label="Sesi aktif"></span>
    </div>
</aside>
