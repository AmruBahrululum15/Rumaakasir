@php
    $currentRoute = Route::currentRouteName();

    $isActive = function ($routes) use ($currentRoute) {
        foreach ((array) $routes as $route) {
            if ($currentRoute === $route) {
                return true;
            }

            if (str_ends_with($route, '.*') && str_starts_with($currentRoute ?? '', rtrim($route, '.*'))) {
                return true;
            }
        }

        return false;
    };
@endphp

<aside class="pos-sidebar">

    {{-- BRAND --}}
    <div class="pos-brand">

        <div class="pos-brand-icon">
            @if(!empty($branding['logo']))
                <img src="{{ asset('storage/'.$branding['logo']) }}" alt="Logo {{ $branding['name'] }}" style="width:100%;height:100%;object-fit:contain">
            @else
                <i class="fas fa-cash-register"></i>
            @endif
        </div>

        <div class="pos-brand-text">
            <div class="pos-brand-title">
                {{ $branding['name'] }}
            </div>

            <div class="pos-brand-subtitle">
                {{ $branding['description'] ?: 'Point of Sale' }}
            </div>
        </div>

        {{-- Decorative circles --}}
        <span class="brand-circle brand-circle-1"></span>
        <span class="brand-circle brand-circle-2"></span>

    </div>


    {{-- NAVIGATION --}}
    <div class="pos-sidebar-content">

        {{-- DASHBOARD --}}
        <a href="{{ route('dashboard') }}"
           class="pos-menu-item pos-menu-dashboard {{ $isActive(['dashboard']) ? 'active' : '' }}">

            <span class="pos-menu-icon">
                <i class="fas fa-home"></i>
            </span>

            <span class="pos-menu-text">
                Dashboard
            </span>

        </a>


        {{-- TRANSAKSI --}}
        <div class="pos-menu-section">

            <div class="pos-section-title">
                <span>TRANSAKSI</span>
                <span class="pos-section-line"></span>
            </div>


            {{-- Penjualan --}}
            @if(Route::has('sales.index'))
                <a href="{{ route('sales.index') }}"
                   class="pos-menu-item {{ $isActive(['sales.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </span>

                    <span class="pos-menu-text">
                        Penjualan
                    </span>

                </a>
            @endif


            {{-- Pembelian --}}
            @if(Route::has('purchases.index'))
                <a href="{{ route('purchases.index') }}"
                   class="pos-menu-item {{ $isActive(['purchases.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </span>

                    <span class="pos-menu-text">
                        Pembelian
                    </span>

                </a>
            @endif


            {{-- Riwayat Transaksi --}}
            @if(Route::has('sales.index'))
                <a href="{{ route('sales.index') }}"
                   class="pos-menu-item">

                    <span class="pos-menu-icon">
                        <i class="fas fa-history"></i>
                    </span>

                    <span class="pos-menu-text">
                        Riwayat Transaksi
                    </span>

                </a>
            @endif

        </div>


        {{-- MASTER DATA --}}
        <div class="pos-menu-section">

            <div class="pos-section-title">
                <span>MASTER DATA</span>
                <span class="pos-section-line"></span>
            </div>


            {{-- Produk --}}
            @if(Route::has('products.index'))
                <a href="{{ route('products.index') }}"
                   class="pos-menu-item {{ $isActive(['products.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-cube"></i>
                    </span>

                    <span class="pos-menu-text">
                        Produk
                    </span>

                </a>
            @endif


            {{-- Kategori --}}
            @if(Route::has('categories.index'))
                <a href="{{ route('categories.index') }}"
                   class="pos-menu-item {{ $isActive(['categories.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-tags"></i>
                    </span>

                    <span class="pos-menu-text">
                        Kategori
                    </span>

                </a>
            @endif


            {{-- Pelanggan --}}
            @if(Route::has('customers.index'))
                <a href="{{ route('customers.index') }}"
                   class="pos-menu-item {{ $isActive(['customers.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-users"></i>
                    </span>

                    <span class="pos-menu-text">
                        Pelanggan
                    </span>

                </a>
            @endif


            {{-- Supplier --}}
            @if(Route::has('suppliers.index'))
                <a href="{{ route('suppliers.index') }}"
                   class="pos-menu-item {{ $isActive(['suppliers.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-briefcase"></i>
                    </span>

                    <span class="pos-menu-text">
                        Supplier
                    </span>

                </a>
            @endif


            {{-- Meja & QR --}}
            @if(Route::has('tables.index'))
                <a href="{{ route('tables.index') }}"
                   class="pos-menu-item {{ $isActive(['tables.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-qrcode"></i>
                    </span>

                    <span class="pos-menu-text">
                        Meja & QR
                    </span>

                </a>
            @endif

        </div>


        {{-- LAPORAN --}}
        <div class="pos-menu-section">

            <div class="pos-section-title">
                <span>LAPORAN</span>
                <span class="pos-section-line"></span>
            </div>


            @if(Route::has('reports.index'))
                <a href="{{ route('reports.index') }}"
                   class="pos-menu-item {{ $isActive(['reports.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-chart-column"></i>
                    </span>

                    <span class="pos-menu-text">
                        Laporan Penjualan
                    </span>

                </a>
            @endif


            @if(Route::has('reports.index'))
                <a href="{{ route('reports.index') }}"
                   class="pos-menu-item">

                    <span class="pos-menu-icon">
                        <i class="fas fa-boxes-stacked"></i>
                    </span>

                    <span class="pos-menu-text">
                        Laporan Stok
                    </span>

                </a>
            @endif


            @if(Route::has('reports.index'))
                <a href="{{ route('reports.index') }}"
                   class="pos-menu-item">

                    <span class="pos-menu-icon">
                        <i class="fas fa-chart-pie"></i>
                    </span>

                    <span class="pos-menu-text">
                        Laporan Keuangan
                    </span>

                </a>
            @endif

        </div>


        {{-- PENGATURAN --}}
        <div class="pos-menu-section">

            <div class="pos-section-title">
                <span>PENGATURAN</span>
                <span class="pos-section-line"></span>
            </div>


            @if(Route::has('settings.index'))
                <a href="{{ route('settings.index') }}"
                   class="pos-menu-item {{ $isActive(['settings.*']) ? 'active' : '' }}">

                    <span class="pos-menu-icon">
                        <i class="fas fa-cog"></i>
                    </span>

                    <span class="pos-menu-text">
                        Pengaturan
                    </span>

                </a>
            @endif


            {{-- Logout --}}
            <form method="POST"
                  action="{{ route('logout') }}"
                  class="pos-logout-form">

                @csrf

                <button type="submit"
                        class="pos-menu-item pos-menu-logout">

                    <span class="pos-menu-icon">
                        <i class="fas fa-right-from-bracket"></i>
                    </span>

                    <span class="pos-menu-text">
                        Logout
                    </span>

                </button>

            </form>

        </div>

    </div>


    {{-- FOOTER --}}
    <div class="pos-sidebar-footer">

        <div class="pos-footer-decoration decoration-1"></div>
        <div class="pos-footer-decoration decoration-2"></div>

        <div class="pos-footer-text">
            Mudah · Cepat · Akurat
        </div>

    </div>

</aside>
