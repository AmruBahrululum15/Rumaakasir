<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    'title' => 'POS Enterprise',

    'title_prefix' => '',

    'title_postfix' => '',


    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */

    'use_ico_only' => false,

    'use_full_favicon' => false,

    'favicon' => [
        'ico' => 'favicon.ico',
        'png' => 'favicon.png',
        'apple' => 'apple-touch-icon.png',
        'svg' => 'favicon.svg',
    ],


    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    */

    'auth_logo' => [
        'enabled' => false,

        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'POS Enterprise',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Preloader
    |--------------------------------------------------------------------------
    */

    'preloader' => [
        'enabled' => false,

        'mode' => 'fullscreen',

        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'POS Enterprise',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    */

    'usermenu_enabled' => true,

    'usermenu_header' => false,

    'usermenu_header_class' => 'bg-warning',

    'usermenu_image' => false,

    'usermenu_desc' => false,

    'usermenu_profile_url' => false,


    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    */

    'layout_topnav' => false,

    'layout_boxed' => false,

    'layout_fixed_sidebar' => false,

    'layout_fixed_navbar' => false,

    'layout_fixed_footer' => false,

    'layout_dark_mode' => false,


    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    */

    'classes_auth_card' => 'card-outline card-warning',

    'classes_auth_header' => '',

    'classes_auth_body' => '',

    'classes_auth_footer' => '',

    'classes_auth_icon' => '',

    'classes_auth_btn' => 'btn-flat btn-warning',


    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    */

    'classes_body' => 'pos-enterprise-body',

    'classes_brand' => 'pos-brand',

    'classes_brand_text' => 'pos-brand-text',

    'classes_content_wrapper' => 'pos-content-wrapper',

    'classes_content_header' => 'pos-content-header',

    'classes_content' => 'pos-content',

    'classes_sidebar' => 'sidebar-light elevation-0 pos-sidebar',

    'classes_sidebar_nav' => 'nav-compact pos-sidebar-nav',

    'classes_topnav' => 'navbar-white navbar-light pos-topnav',

    'classes_topnav_nav' => 'navbar-expand',

    'classes_topnav_container' => 'container-fluid',


    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    // Sidebar selalu dalam keadaan penuh.
    'sidebar_mini' => false,

    // Jangan collapse saat halaman pertama kali dibuka.
    'sidebar_collapse' => false,

    'sidebar_collapse_auto_size' => false,

    'sidebar_collapse_remember' => false,

    'sidebar_collapse_remember_no_transition' => true,

    'sidebar_scrollbar_theme' => 'os-theme-light',

    'sidebar_scrollbar_auto_hide' => 'l',

    'sidebar_nav_accordion' => true,

    'sidebar_nav_animation_speed' => 300,


    /*
    |--------------------------------------------------------------------------
    | Control Sidebar
    |--------------------------------------------------------------------------
    */

    'right_sidebar' => false,

    'right_sidebar_icon' => 'fas fa-cogs',

    'right_sidebar_theme' => 'dark',

    'right_sidebar_slide' => true,

    'right_sidebar_push' => false,

    'right_sidebar_scrollbar_theme' => 'os-theme-light',

    'right_sidebar_scrollbar_auto_hide' => 'l',


    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'use_route_url' => false,

    'dashboard_url' => 'dashboard',

    'logout_url' => 'logout',

    'login_url' => 'login',

    'register_url' => 'register',

    'password_reset_url' => 'password/reset',

    'password_email_url' => 'password/email',

    'profile_url' => 'profile',

    'disable_darkmode_routes' => false,


    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    */

    'laravel_asset_bundling' => false,

    'laravel_css_path' => 'css/app.css',

    'laravel_js_path' => 'js/app.js',


    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    */

    'menu' => [

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD
        |--------------------------------------------------------------------------
        */

        [
            'text' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'fas fa-fw fa-home',
            'classes' => 'pos-menu-dashboard',
        ],


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'TRANSAKSI',
            'classes' => 'pos-menu-header',
        ],

        [
            'text' => 'Penjualan',
            'route' => 'sales.create',
            'icon' => 'fas fa-fw fa-cash-register',
        ],

        [
            'text' => 'Riwayat Penjualan',
            'route' => 'sales.index',
            'icon' => 'fas fa-fw fa-history',
        ],

        [
            'text' => 'Pembelian',
            'route' => 'purchases.create',
            'icon' => 'fas fa-fw fa-shopping-cart',
        ],

        [
    'text' => 'Riwayat Pembelian',
    'route' => 'purchases.index',
    'icon' => 'fas fa-clipboard-list',
],

        /*
        |--------------------------------------------------------------------------
        | MASTER DATA
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'MASTER DATA',
            'classes' => 'pos-menu-header',
        ],

        [
            'text' => 'Produk',
            'route' => 'products.index',
            'icon' => 'fas fa-fw fa-box',
        ],

        [
            'text' => 'Kategori',
            'route' => 'categories.index',
            'icon' => 'fas fa-fw fa-tags',
        ],

        [
            'text' => 'Pelanggan',
            'route' => 'customers.index',
            'icon' => 'fas fa-fw fa-users',
        ],

        [
            'text' => 'Supplier',
            'route' => 'suppliers.index',
            'icon' => 'fas fa-fw fa-truck',
        ],

        [
            'text' => 'Meja & QR',
            'route' => 'tables.index',
            'icon' => 'fas fa-fw fa-qrcode',
        ],


        /*
        |--------------------------------------------------------------------------
        | LAPORAN
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'LAPORAN',
            'classes' => 'pos-menu-header',
        ],

        [
            'text' => 'Laporan',
            'route' => 'reports.index',
            'icon' => 'fas fa-fw fa-chart-line',
        ],

        [
            'text' => 'Export Excel',
            'route' => 'reports.export.excel',
            'icon' => 'fas fa-fw fa-file-excel',
        ],

        [
            'text' => 'Export PDF',
            'route' => 'reports.pdf',
            'icon' => 'fas fa-fw fa-file-pdf',
        ],


        /*
        |--------------------------------------------------------------------------
        | PENGATURAN
        |--------------------------------------------------------------------------
        */

        [
            'header' => 'PENGATURAN',
            'classes' => 'pos-menu-header',
        ],

        [
            'text' => 'Pengaturan',
            'route' => 'settings.index',
            'icon' => 'fas fa-fw fa-cog',
        ],

        [
            'text' => 'Manajemen Pengguna',
            'route' => 'users.index',
            'icon' => 'fas fa-fw fa-user-cog',
        ],


        /*
        |--------------------------------------------------------------------------
        | NOTIFIKASI
        |--------------------------------------------------------------------------
        */

        [
            'text' => 'Notifikasi',
            'route' => 'notifications.index',
            'icon' => 'fas fa-fw fa-bell',
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    */

    'filters' => [

        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,

        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,

        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,

        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,

        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,

        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,

        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,

    ],


    /*
    |--------------------------------------------------------------------------
    | Plugins
    |--------------------------------------------------------------------------
    |
    | Plugin tambahan dibuat tidak aktif secara global.
    | Jika suatu halaman membutuhkan plugin tertentu,
    | plugin bisa diaktifkan dari Blade halaman tersebut.
    |
    */

    'plugins' => [

        /*
        |--------------------------------------------------------------------------
        | DataTables
        |--------------------------------------------------------------------------
        */

        'Datatables' => [

            'active' => false,

            'files' => [

                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
                ],

                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js',
                ],

                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css',
                ],

            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Select2
        |--------------------------------------------------------------------------
        */

        'Select2' => [

            'active' => false,

            'files' => [

                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                ],

                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                ],

            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Chart.js
        |--------------------------------------------------------------------------
        */

        'Chartjs' => [

            'active' => false,

            'files' => [

                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                ],

            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | SweetAlert2
        |--------------------------------------------------------------------------
        */

        'Sweetalert2' => [

            'active' => false,

            'files' => [

                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],

            ],
        ],


        /*
        |--------------------------------------------------------------------------
        | Pace
        |--------------------------------------------------------------------------
        */

        'Pace' => [

            'active' => false,

            'files' => [

                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/themes/blue/pace-theme-center-radar.min.css',
                ],

                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/pace.min.js',
                ],

            ],
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    */

    'iframe' => [

        'default_tab' => [
            'url' => null,
            'title' => null,
        ],

        'buttons' => [

            'close' => true,

            'close_all' => true,

            'close_all_other' => true,

            'scroll_left' => true,

            'scroll_right' => true,

            'fullscreen' => true,

        ],

        'options' => [

            'loading_screen' => 1000,

            'auto_show_new_tab' => true,

            'use_navbar_items' => true,

        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    */

    'livewire' => false,

];