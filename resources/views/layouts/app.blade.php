<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- =========================================================
        META
    ========================================================== --}}

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">


    {{-- =========================================================
        TITLE
    ========================================================== --}}

    <title>
        @hasSection('title')
            @yield('title') - {{ $branding['name'] }}
        @else
            {{ $branding['name'] }}
        @endif
    </title>


    {{-- =========================================================
        GOOGLE FONT
    ========================================================== --}}

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">


    {{-- =========================================================
        FONT AWESOME
    ========================================================== --}}

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    {{-- =========================================================
        ADMINLTE CSS
    ========================================================== --}}

    <link rel="stylesheet"
          href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">


    {{-- =========================================================
        PAGE CSS
    ========================================================== --}}

    @yield('css')


    {{-- =========================================================
        POS ENTERPRISE GLOBAL CSS
        DITARUH SETELAH @yield('css')
        AGAR TEMA POS MENIMPA TEMA DEFAULT HALAMAN
    ========================================================== --}}

    <style>

        /* =====================================================
           POS ENTERPRISE VARIABLES
        ====================================================== */

        :root {

            --pos-orange: #ff7a18;
            --pos-orange-dark: #f36b0a;
            --pos-orange-light: #ff963f;

            --pos-orange-soft: #fff1e7;
            --pos-orange-softer: #fff8f2;

            --pos-cream: #fffaf4;

            --pos-background: #f6f3ef;

            --pos-white: #ffffff;

            --pos-border: #eee8e1;

            --pos-text: #172033;
            --pos-text-light: #374151;

            --pos-muted: #8a8f9c;

            --pos-green: #20b66f;
            --pos-green-soft: #eafaf2;

            --pos-purple: #7c4dff;
            --pos-purple-soft: #f1ecff;

            --pos-red: #e85d4a;
            --pos-red-soft: #fff0ec;

        }


        /* =====================================================
           RESET
        ====================================================== */

        html,
        body {

            min-height: 100%;

        }


        body {

            margin: 0 !important;

            padding: 0 !important;

            font-family:
                "Inter",
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Roboto,
                Arial,
                sans-serif !important;

            font-size: 14px;

            color:
                var(--pos-text) !important;

            background:
                var(--pos-background) !important;

        }


        .wrapper {

            background:
                var(--pos-background) !important;

        }


        /* =====================================================
           SIDEBAR
        ====================================================== */

        .main-sidebar {

            position: fixed !important;

            top: 0 !important;

            left: 0 !important;

            bottom: 0 !important;

            width: 232px !important;

            min-height: 100vh !important;

            background:
                var(--pos-cream) !important;

            border-right:
                1px solid var(--pos-border) !important;

            box-shadow:
                none !important;

            z-index: 1038 !important;

        }


        .main-sidebar::before {

            width:
                232px !important;

        }


        /* =====================================================
           SIDEBAR INNER
        ====================================================== */

        .main-sidebar .sidebar {

            width:
                232px !important;

            height:
                calc(100vh - 62px) !important;

            overflow-y:
                auto !important;

            overflow-x:
                hidden !important;

            padding:
                8px 8px 90px !important;

            background:
                var(--pos-cream) !important;

        }


        /* =====================================================
           BRAND
        ====================================================== */

        .main-sidebar .brand-link {

            width:
                232px !important;

            height:
                62px !important;

            margin:
                0 !important;

            padding:
                8px 14px !important;

            display:
                flex !important;

            align-items:
                center !important;

            background:
                linear-gradient(
                    135deg,
                    #ff7414 0%,
                    #ff963f 100%
                ) !important;

            color:
                #ffffff !important;

            border:
                none !important;

            text-decoration:
                none !important;

            box-shadow:
                0 4px 16px
                rgba(255,122,24,.12) !important;

        }


        .main-sidebar .brand-link:hover {

            color:
                #ffffff !important;

            background:
                linear-gradient(
                    135deg,
                    #ff7414 0%,
                    #ff963f 100%
                ) !important;

        }


        .main-sidebar .brand-image {
            display: block !important;
            width: 42px !important;
            height: 42px !important;
            margin: 0 10px 0 0 !important;
            object-fit: contain !important;
            border-radius: 11px !important;
            padding: 3px;

        }


        /* =====================================================
           BRAND TEXT
        ====================================================== */

        .main-sidebar .brand-text {

            display:
                flex !important;

            align-items:
                center !important;

            margin:
                0 !important;

            padding:
                0 !important;

            color: #ffffff !important;
            font-size: 14px !important;
            font-weight: 700 !important;
            line-height: 1.25 !important;
            white-space: normal !important;
            overflow: hidden;
            text-overflow: ellipsis;

            opacity:
                1 !important;

        }


        .main-sidebar .brand-text::before {
            content: none;

        }


        .main-sidebar .brand-text::after {
            content: none;

        }


        /* =====================================================
           USER PANEL
        ====================================================== */

        .main-sidebar .user-panel {

            margin:
                7px 4px 5px !important;

            padding:
                7px 5px 10px !important;

            border-bottom:
                1px solid #f0e9e1 !important;

        }


        .main-sidebar .user-panel .image {

            display:
                flex !important;

            align-items:
                center !important;

            justify-content:
                center !important;

        }


        .main-sidebar .user-panel .image img {

            width:
                32px !important;

            height:
                32px !important;

            object-fit:
                cover !important;

            border:
                2px solid #ffffff !important;

        }


        .main-sidebar .user-panel .info {

            padding:
                0 0 0 8px !important;

        }


        .main-sidebar .user-panel .info a {

            padding:
                0 !important;

            color:
                #273142 !important;

            font-size:
                11px !important;

            font-weight:
                700 !important;

        }


        .main-sidebar .user-panel .info small {

            display:
                block;

            margin-top:
                2px;

            color:
                #a08e7e;

            font-size:
                9px;

        }


        /* =====================================================
           SIDEBAR NAV
        ====================================================== */

        .main-sidebar .nav-sidebar {

            width:
                100% !important;

            padding:
                0 !important;

        }


        .main-sidebar .nav-sidebar .nav-item {

            width:
                100% !important;

            margin:
                0 !important;

        }


        /* =====================================================
           SECTION TITLE
        ====================================================== */

        .main-sidebar .nav-sidebar .nav-header {

            padding:
                13px 13px 7px !important;

            margin:
                3px 0 2px !important;

            color:
                #b19b87 !important;

            background:
                transparent !important;

            font-size:
                10px !important;

            font-weight:
                700 !important;

            letter-spacing:
                .55px !important;

            text-transform:
                uppercase !important;

            border-top:
                1px solid #f0e9e1 !important;

        }


        .main-sidebar .nav-sidebar .nav-header:first-child {

            border-top:
                none !important;

        }


        /* =====================================================
           NAVIGATION LINK
        ====================================================== */

        .main-sidebar .nav-sidebar > .nav-item > .nav-link {

            width:
                calc(100% - 8px) !important;

            min-height:
                38px !important;

            margin:
                2px 4px !important;

            padding:
                8px 10px !important;

            display:
                flex !important;

            align-items:
                center !important;

            border-radius:
                9px !important;

            background:
                transparent !important;

            color:
                #303946 !important;

            font-size:
                12px !important;

            font-weight:
                500 !important;

            box-shadow:
                none !important;

            transition:
                all .18s ease !important;

        }


        /* =====================================================
           ICON
        ====================================================== */

        .main-sidebar .nav-sidebar .nav-icon {

            width:
                20px !important;

            min-width:
                20px !important;

            margin:
                0 8px 0 0 !important;

            color:
                #303946 !important;

            font-size:
                14px !important;

            text-align:
                center !important;

        }


        /* =====================================================
           TEXT
        ====================================================== */

        .main-sidebar .nav-sidebar .nav-link p {

            margin:
                0 !important;

            padding:
                0 !important;

            color:
                inherit !important;

            font-size:
                12px !important;

            font-weight:
                500 !important;

        }


        /* =====================================================
           HOVER
        ====================================================== */

        .main-sidebar .nav-sidebar > .nav-item > .nav-link:hover {

            background:
                var(--pos-orange-soft) !important;

            color:
                var(--pos-orange-dark) !important;

            box-shadow:
                none !important;

            transform:
                translateX(1px);

        }


        .main-sidebar .nav-sidebar > .nav-item > .nav-link:hover .nav-icon {

            color:
                var(--pos-orange) !important;

        }


        /* =====================================================
           ACTIVE MENU
        ====================================================== */

        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active {

            background:
                linear-gradient(
                    135deg,
                    #ff8a32 0%,
                    #ff7414 100%
                ) !important;

            color:
                #ffffff !important;

            border-radius:
                9px !important;

            box-shadow:
                0 5px 13px
                rgba(255,122,24,.18) !important;

        }


        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active .nav-icon {

            color:
                #ffffff !important;

        }


        .main-sidebar .nav-sidebar > .nav-item > .nav-link.active p {

            color:
                #ffffff !important;

            font-weight:
                600 !important;

        }


        /* =====================================================
           BADGE
        ====================================================== */

        .main-sidebar .nav-sidebar .right {

            margin-left:
                auto !important;

        }


        .main-sidebar .badge-danger {

            background:
                #ff6b57 !important;

            color:
                #ffffff !important;

        }


        /* =====================================================
           LOGOUT
        ====================================================== */

        .pos-sidebar-logout-item {

            margin:
                7px 0 0 !important;

            padding-top:
                7px !important;

            border-top:
                1px solid #f0e9e1 !important;

        }


        .pos-logout-button {

            width:
                calc(100% - 8px) !important;

            min-height:
                38px !important;

            margin:
                2px 4px !important;

            padding:
                8px 10px !important;

            display:
                flex !important;

            align-items:
                center !important;

            border:
                none !important;

            border-radius:
                9px !important;

            background:
                #fff0ec !important;

            color:
                #e85d4a !important;

            font-family:
                inherit !important;

            font-size:
                12px !important;

            font-weight:
                600 !important;

            cursor:
                pointer !important;

            text-align:
                left !important;

            transition:
                all .18s ease !important;

        }


        .pos-logout-button:hover {

            background:
                #ffe1da !important;

            color:
                #d94835 !important;

        }


        .pos-logout-button .nav-icon {

            width:
                20px !important;

            min-width:
                20px !important;

            margin:
                0 8px 0 0 !important;

            color:
                inherit !important;

        }


        /* =====================================================
           SIDEBAR DECORATION
        ====================================================== */

        .main-sidebar::after {

            content:
                "";

            position:
                fixed;

            left:
                0;

            bottom:
                0;

            width:
                232px;

            height:
                78px;

            pointer-events:
                none;

            background:
                radial-gradient(
                    circle at 25px 80px,
                    rgba(255,122,24,.14) 0 35px,
                    transparent 36px
                ),
                radial-gradient(
                    circle at 105px 85px,
                    rgba(255,122,24,.09) 0 52px,
                    transparent 53px
                ),
                linear-gradient(
                    to top,
                    rgba(255,122,24,.07),
                    transparent
                );

            z-index:
                10;

        }


        /* =====================================================
           TOP NAVBAR
        ====================================================== */

        .main-header {

            height:
                62px !important;

            margin-left:
                232px !important;

            background:
                #ffffff !important;

            border-bottom:
                1px solid var(--pos-border) !important;

            box-shadow:
                0 2px 10px
                rgba(30,20,10,.025) !important;

        }


        .main-header.navbar {

            min-height:
                62px !important;

            background:
                #ffffff !important;

        }


        .main-header .nav-link {

            color:
                #3c4655 !important;

        }


        .main-header .nav-link:hover {

            color:
                var(--pos-orange) !important;

        }


        .main-header .nav-link[data-widget="pushmenu"] i {

            color:
                var(--pos-orange-dark) !important;

        }


        /* =====================================================
           NOTIFICATION
        ====================================================== */

        .navbar-badge {

            top:
                6px !important;

            right:
                3px !important;

            min-width:
                7px !important;

            height:
                7px !important;

            padding:
                0 !important;

            border:
                2px solid #ffffff !important;

            border-radius:
                50% !important;

            font-size:
                0 !important;

        }


        /* =====================================================
           USER AVATAR
        ====================================================== */

        .pos-navbar-avatar {

            width:
                31px;

            height:
                31px;

            margin-right:
                7px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                50%;

            background:
                #fff0e4;

            color:
                var(--pos-orange);

            font-size:
                13px;

        }


        .user-menu > .nav-link {

            display:
                flex !important;

            align-items:
                center !important;

            gap:
                5px !important;

            color:
                #343b48 !important;

        }


        .user-menu > .nav-link:hover {

            color:
                var(--pos-orange) !important;

        }


        .user-menu .user-image {

            width:
                31px !important;

            height:
                31px !important;

            object-fit:
                cover !important;

            border:
                2px solid #fff0e4 !important;

        }


        /* =====================================================
           USER DROPDOWN
        ====================================================== */

        .dropdown-menu {

            border:
                1px solid var(--pos-border) !important;

            border-radius:
                12px !important;

            box-shadow:
                0 12px 35px
                rgba(30,20,10,.10) !important;

            overflow:
                hidden;

        }


        .dropdown-item {

            margin:
                2px 5px !important;

            width:
                calc(100% - 10px) !important;

            border-radius:
                7px !important;

            color:
                #3d4654 !important;

            font-size:
                12px !important;

        }


        .dropdown-item:hover {

            background:
                var(--pos-orange-soft) !important;

            color:
                var(--pos-orange-dark) !important;

        }


        .pos-dropdown-logout {

            border:
                none !important;

            background:
                transparent !important;

            color:
                #e85d4a !important;

            text-align:
                left !important;

            cursor:
                pointer !important;

        }


        .pos-dropdown-logout:hover {

            background:
                #fff0ec !important;

            color:
                #d94835 !important;

        }


        /* =====================================================
           USER DROPDOWN HEADER
        ====================================================== */

        .pos-user-dropdown-header {

            display:
                flex;

            align-items:
                center;

            gap:
                10px;

            padding:
                14px;

            background:
                #fffaf5;

        }


        .pos-user-dropdown-avatar {

            width:
                40px;

            height:
                40px;

            flex:
                0 0 40px;

            display:
                flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                50%;

            overflow:
                hidden;

            background:
                #fff0e4;

            color:
                var(--pos-orange);

        }


        .pos-user-dropdown-avatar img {

            width:
                100%;

            height:
                100%;

            object-fit:
                cover;

        }


        .pos-user-dropdown-name {

            color:
                var(--pos-text);

            font-size:
                13px;

            font-weight:
                700;

        }


        .pos-user-dropdown-role {

            margin-top:
                2px;

            color:
                #9a9a9a;

            font-size:
                10px;

        }


        /* =====================================================
           CONTENT WRAPPER
        ====================================================== */

        .content-wrapper {

            min-height:
                calc(100vh - 62px) !important;

            margin-left:
                232px !important;

            background:
                var(--pos-background) !important;

        }


        /* =====================================================
           CONTENT
        ====================================================== */

        .content {

            padding:
                0 !important;

            background:
                transparent !important;

        }


        .content > .container-fluid {

            padding-left:
                18px !important;

            padding-right:
                18px !important;

        }


        /* =====================================================
           CONTENT HEADER
        ====================================================== */

        .content-header {

            padding:
                20px 18px 10px !important;

            background:
                transparent !important;

        }


        .content-header h1 {

            color:
                var(--pos-text-dark) !important;

        }


        /* =====================================================
           GENERAL CARD
        ====================================================== */

        .card {

            border:
                1px solid var(--pos-border) !important;

            border-radius:
                16px !important;

            background:
                #ffffff !important;

            box-shadow:
                0 8px 25px
                rgba(40,30,20,.045) !important;

        }


        .card-header {

            background:
                #ffffff !important;

            border-bottom:
                1px solid #f0ebe6 !important;

        }


        .card-title {

            color:
                var(--pos-text) !important;

            font-weight:
                700 !important;

        }


        /* =====================================================
           PRIMARY BUTTON
        ====================================================== */

        .btn-primary {

            background:
                linear-gradient(
                    135deg,
                    #ff8a32,
                    #ff7414
                ) !important;

            border:
                none !important;

            border-color:
                transparent !important;

            color:
                #ffffff !important;

            border-radius:
                9px !important;

            font-weight:
                600 !important;

            box-shadow:
                0 5px 13px
                rgba(255,122,24,.16) !important;

        }


        .btn-primary:hover,
        .btn-primary:focus,
        .btn-primary:active {

            background:
                linear-gradient(
                    135deg,
                    #ff7414,
                    #f36609
                ) !important;

            border-color:
                transparent !important;

            color:
                #ffffff !important;

        }


        /* =====================================================
           WARNING BUTTON
        ====================================================== */

        .btn-warning {

            background:
                var(--pos-orange) !important;

            border-color:
                var(--pos-orange) !important;

            color:
                #ffffff !important;

        }


        /* =====================================================
           SUCCESS
        ====================================================== */

        .btn-success {

            background:
                #20b66f !important;

            border-color:
                #20b66f !important;

        }


        /* =====================================================
           DANGER
        ====================================================== */

        .btn-danger {

            background:
                #e85d4a !important;

            border-color:
                #e85d4a !important;

        }


        /* =====================================================
           OUTLINE PRIMARY
        ====================================================== */

        .btn-outline-primary {

            color:
                var(--pos-orange-dark) !important;

            border-color:
                var(--pos-orange) !important;

            border-radius:
                9px !important;

        }


        .btn-outline-primary:hover {

            background:
                var(--pos-orange) !important;

            border-color:
                var(--pos-orange) !important;

            color:
                #ffffff !important;

        }


        /* =====================================================
           FORM CONTROL
        ====================================================== */

        .form-control,
        .custom-select,
        select,
        input,
        textarea {

            border:
                1px solid #e4ded7 !important;

            border-radius:
                9px !important;

            color:
                var(--pos-text) !important;

            font-size:
                13px !important;

        }


        .form-control:focus,
        .custom-select:focus,
        select:focus,
        input:focus,
        textarea:focus {

            border-color:
                var(--pos-orange) !important;

            box-shadow:
                0 0 0 3px
                rgba(255,122,24,.10) !important;

        }


        /* =====================================================
           TABLE
        ====================================================== */

        .table {

            color:
                var(--pos-text) !important;

            font-size:
                13px !important;

        }


        .table thead th {

            background:
                #fffaf5 !important;

            color:
                #8c7968 !important;

            border-top:
                none !important;

            border-bottom:
                1px solid #eee5dc !important;

            font-size:
                10px !important;

            font-weight:
                700 !important;

            text-transform:
                uppercase !important;

        }


        .table tbody td {

            border-top:
                1px solid #f3eee9 !important;

            vertical-align:
                middle !important;

        }


        .table tbody tr:hover {

            background:
                #fffaf5 !important;

        }


        /* =====================================================
           BADGES
        ====================================================== */

        .badge-primary {

            background:
                var(--pos-orange-soft) !important;

            color:
                var(--pos-orange-dark) !important;

        }


        .badge-success {

            background:
                var(--pos-green-soft) !important;

            color:
                #13945a !important;

        }


        .badge-warning {

            background:
                #fff4e5 !important;

            color:
                #d97706 !important;

        }


        .badge-danger {

            background:
                #fff0f0 !important;

            color:
                #e54848 !important;

        }


        /* =====================================================
           PAGINATION
        ====================================================== */

        .page-link {

            color:
                var(--pos-orange-dark) !important;

            border-color:
                #eee5dc !important;

        }


        .page-item.active .page-link {

            background:
                var(--pos-orange) !important;

            border-color:
                var(--pos-orange) !important;

            color:
                #ffffff !important;

        }


        /* =====================================================
           LINKS
        ====================================================== */

        a {

            color:
                var(--pos-orange-dark);

        }


        a:hover {

            color:
                var(--pos-orange);

        }


        /* =====================================================
           MAIN FOOTER
        ====================================================== */

        .main-footer {

            margin-left:
                232px !important;

            background:
                #ffffff !important;

            border-top:
                1px solid var(--pos-border) !important;

            color:
                #8a8f9c !important;

            font-size:
                11px !important;

        }


        /* =====================================================
           SCROLLBAR
        ====================================================== */

        .main-sidebar .sidebar::-webkit-scrollbar {

            width:
                5px;

        }


        .main-sidebar .sidebar::-webkit-scrollbar-track {

            background:
                transparent;

        }


        .main-sidebar .sidebar::-webkit-scrollbar-thumb {

            background:
                #e3d9ce;

            border-radius:
                10px;

        }


        .main-sidebar .sidebar::-webkit-scrollbar-thumb:hover {

            background:
                #d3c4b5;

        }


        /* =====================================================
           MOBILE
        ====================================================== */

        @media (max-width: 991.98px) {

            .main-sidebar {

                transform:
                    translateX(-232px) !important;

                transition:
                    transform .25s ease;

            }


            body.sidebar-open .main-sidebar {

                transform:
                    translateX(0) !important;

            }


            .main-header {

                margin-left:
                    0 !important;

            }


            .content-wrapper {

                margin-left:
                    0 !important;

            }


            .main-footer {

                margin-left:
                    0 !important;

            }


            .main-sidebar::after {

                width:
                    232px;

            }

        }


        /* =====================================================
           SMALL MOBILE
        ====================================================== */

        @media (max-width: 575.98px) {

            .content > .container-fluid {

                padding-left:
                    10px !important;

                padding-right:
                    10px !important;

            }


            .content-header {

                padding:
                    15px 10px 8px !important;

            }


            .card {

                border-radius:
                    12px !important;

            }

        }

    </style>

</head>


<body class="hold-transition layout-fixed">


<div class="wrapper">


    {{-- =========================================================
        TOP NAVBAR
    ========================================================== --}}

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">


        {{-- LEFT --}}
        <ul class="navbar-nav">

            <li class="nav-item">

                <a class="nav-link"
                   data-widget="pushmenu"
                   href="#"
                   role="button">

                    <i class="fas fa-bars"></i>

                </a>

            </li>

        </ul>


        {{-- RIGHT --}}
        <ul class="navbar-nav ml-auto">


            {{-- =================================================
                NOTIFICATION
            ================================================== --}}

            @auth

                @php

                    $unreadNotifications =
                        auth()->user()->unreadNotifications->count();

                @endphp


                <li class="nav-item dropdown">

                    <a class="nav-link"
                       href="#"
                       data-toggle="dropdown">

                        <i class="fas fa-bell"></i>


                        @if($unreadNotifications > 0)

                            <span class="badge badge-danger navbar-badge">

                                {{ $unreadNotifications }}

                            </span>

                        @endif

                    </a>


                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">


                        <span class="dropdown-header">

                            {{ $unreadNotifications }}

                            Notifikasi Belum Dibaca

                        </span>


                        <div class="dropdown-divider"></div>


                        <a href="{{ route('notifications.index') }}"
                           class="dropdown-item">

                            <i class="fas fa-bell mr-2"></i>

                            Lihat Semua Notifikasi

                        </a>


                    </div>

                </li>

            @endauth


            {{-- =================================================
                USER
            ================================================== --}}

            @auth

                <li class="nav-item dropdown user-menu">


                    <a href="#"
                       class="nav-link dropdown-toggle"
                       data-toggle="dropdown">


                        {{-- AVATAR --}}

                        @if(auth()->user()->foto)

                            <img src="{{ asset('storage/' . auth()->user()->foto) }}"
                                 class="user-image"
                                 alt="User">

                        @else

                            <div class="pos-navbar-avatar">

                                <i class="fas fa-user"></i>

                            </div>

                        @endif


                        <span class="d-none d-md-inline">

                            {{ auth()->user()->name }}

                        </span>


                    </a>


                    {{-- USER DROPDOWN --}}

                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">


                        <div class="pos-user-dropdown-header">


                            <div class="pos-user-dropdown-avatar">

                                @if(auth()->user()->foto)

                                    <img src="{{ asset('storage/' . auth()->user()->foto) }}"
                                         alt="User">

                                @else

                                    <i class="fas fa-user"></i>

                                @endif

                            </div>


                            <div>

                                <div class="pos-user-dropdown-name">

                                    {{ auth()->user()->name }}

                                </div>


                                <div class="pos-user-dropdown-role">

                                    @if(auth()->user()->roles->count())

                                        {{ auth()->user()->roles->first()->name }}

                                    @else

                                        Administrator

                                    @endif

                                </div>

                            </div>


                        </div>


                        <div class="dropdown-divider"></div>


                        {{-- USER --}}

                        <a href="{{ route('profile.edit') }}"
                           class="dropdown-item">

                            <i class="fas fa-user mr-2"></i>

                            Profil

                        </a>


                        {{-- SETTINGS --}}

                        @can('settings.view')
                        @if(Route::has('settings.index'))

                            <a href="{{ route('settings.index') }}"
                               class="dropdown-item">

                                <i class="fas fa-cog mr-2"></i>

                                Pengaturan

                            </a>

                        @endif
                        @endcan


                        <div class="dropdown-divider"></div>


                        {{-- LOGOUT --}}

                        <form method="POST"
                              action="{{ route('logout') }}"
                              class="m-0">

                            @csrf

                            <button type="submit"
                                    class="dropdown-item pos-dropdown-logout">

                                <i class="fas fa-sign-out-alt mr-2"></i>

                                Keluar

                            </button>

                        </form>


                    </div>


                </li>

            @endauth


        </ul>

    </nav>


    {{-- =========================================================
        SIDEBAR
    ========================================================== --}}

    <aside class="main-sidebar sidebar-light-primary elevation-0">


        {{-- =====================================================
            BRAND
        ====================================================== --}}

        <a href="{{ route('dashboard') }}"
           class="brand-link">

            @if($branding['logo'])
                <img src="{{ asset('storage/'.$branding['logo']) }}" alt="Logo {{ $branding['name'] }}" class="brand-image img-circle elevation-1" style="object-fit:contain;background:#fff">
            @endif

            <span class="brand-text text-truncate" title="{{ $branding['name'] }}">

                {{ $branding['name'] }}

            </span>

        </a>


        {{-- =====================================================
            SIDEBAR
        ====================================================== --}}

        <div class="sidebar">


            {{-- =================================================
                USER PANEL
            ================================================== --}}

            @auth

                <div class="user-panel mt-2 mb-2 d-flex">


                    <div class="image">


                        @if(auth()->user()->foto)

                            <img src="{{ asset('storage/' . auth()->user()->foto) }}"
                                 class="img-circle"
                                 alt="User">

                        @else

                            <div style="
                                width:32px;
                                height:32px;
                                border-radius:50%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                background:#fff0e4;
                                color:#ff7a18;
                            ">

                                <i class="fas fa-user"></i>

                            </div>

                        @endif


                    </div>


                    <div class="info">


                        <a href="{{ route('profile.edit') }}"
                           class="d-block">

                            {{ auth()->user()->name }}

                        </a>


                        <small>

                            @if(auth()->user()->roles->count())

                                {{ auth()->user()->roles->first()->name }}

                            @else

                                Administrator

                            @endif

                        </small>


                    </div>


                </div>

            @endauth


            {{-- =================================================
                MENU
            ================================================== --}}

            <nav>

                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false">


                    <li class="nav-item"><a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="nav-icon fas fa-home"></i><p>Dashboard</p></a></li>

                    @can('users.approve')
                    <li class="nav-item"><a href="{{ route('users.approvals.index') }}" class="nav-link {{ request()->routeIs('users.approvals.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-check"></i><p>Approval Registrasi</p></a></li>
                    @endcan

                    <li class="nav-header">TRANSAKSI</li>
                    @can('sales.create')
                    <li class="nav-item"><a href="{{ route('sales.create') }}" class="nav-link {{ request()->routeIs('sales.create') ? 'active' : '' }}"><i class="nav-icon fas fa-shopping-cart"></i><p>Penjualan</p></a></li>
                    @endcan
                    @can('purchases.create')
                    <li class="nav-item"><a href="{{ route('purchases.create') }}" class="nav-link {{ request()->routeIs('purchases.create') ? 'active' : '' }}"><i class="nav-icon fas fa-shopping-bag"></i><p>Pembelian</p></a></li>
                    @endcan
                    @can('sales.view')
                    <li class="nav-item"><a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index', 'sales.show', 'sales.edit') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Riwayat Transaksi</p></a></li>
                    @endcan
                    @can('purchases.view')
                    <li class="nav-item"><a href="{{ route('purchases.index') }}" class="nav-link {{ request()->routeIs('purchases.index', 'purchases.show', 'purchases.edit') ? 'active' : '' }}"><i class="nav-icon fas fa-history"></i><p>Riwayat Pembelian</p></a></li>
                    @endcan

                    <li class="nav-header">MASTER DATA</li>
                    @can('products.view')
                    <li class="nav-item"><a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="nav-icon fas fa-box"></i><p>Produk</p></a></li>
                    @endcan
                    @can('categories.view')
                    <li class="nav-item"><a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="nav-icon fas fa-tags"></i><p>Kategori</p></a></li>
                    @endcan
                    @can('customers.view')
                    <li class="nav-item"><a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users"></i><p>Pelanggan</p></a></li>
                    @endcan
                    @can('suppliers.view')
                    <li class="nav-item"><a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}"><i class="nav-icon fas fa-briefcase"></i><p>Supplier</p></a></li>
                    @endcan
                    @can('tables.view')
                    <li class="nav-item"><a href="{{ route('tables.index') }}" class="nav-link {{ request()->routeIs('tables.*') ? 'active' : '' }}"><i class="nav-icon fas fa-qrcode"></i><p>Meja & QR</p></a></li>
                    @endcan

                    @can('reports.view')
                    <li class="nav-header">LAPORAN</li>
                    <li class="nav-item"><a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}"><i class="nav-icon fas fa-chart-bar"></i><p>Laporan</p></a></li>
                    <li class="nav-item"><a href="{{ route('reports.export.excel') }}" class="nav-link {{ request()->routeIs('reports.export.excel') ? 'active' : '' }}"><i class="nav-icon fas fa-file-excel"></i><p>Export Excel</p></a></li>
                    <li class="nav-item"><a href="{{ route('reports.pdf') }}" class="nav-link {{ request()->routeIs('reports.pdf') ? 'active' : '' }}"><i class="nav-icon fas fa-file-pdf"></i><p>Export PDF</p></a></li>
                    @endcan

                    @canany(['settings.view', 'users.view'])
                    <li class="nav-header">PENGATURAN</li>
                    @endcanany
                    @can('settings.view')
                    <li class="nav-item"><a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="nav-icon fas fa-cog"></i><p>Pengaturan</p></a></li>
                    @endcan
                    @can('users.view')
                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-cog"></i><p>Manajemen Pengguna</p></a></li>
                    @endcan


                    {{-- =================================================
                        NOTIFIKASI
                    ================================================== --}}

                    <li class="nav-item">

                        <a href="{{ route('notifications.index') }}"
                           class="nav-link
                           {{ request()->routeIs('notifications.*') ? 'active' : '' }}">

                            <i class="nav-icon fas fa-bell"></i>

                            <p>

                                Notifikasi

                                @auth

                                    @if(isset($unreadNotifications) && $unreadNotifications > 0)

                                        <span class="right badge badge-danger">

                                            {{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}

                                        </span>

                                    @endif

                                @endauth

                            </p>

                        </a>

                    </li>


                    {{-- =================================================
                        LOGOUT
                    ================================================== --}}

                    <li class="nav-item pos-sidebar-logout-item">

                        <form method="POST"
                              action="{{ route('logout') }}">

                            @csrf

                            <button type="submit"
                                    class="pos-logout-button">

                                <i class="nav-icon fas fa-sign-out-alt"></i>

                                <span>
                                    Keluar
                                </span>

                            </button>

                        </form>

                    </li>


                </ul>

            </nav>


        </div>

    </aside>


    {{-- =========================================================
        CONTENT WRAPPER
    ========================================================== --}}

    <div class="content-wrapper">


        {{-- =====================================================
            CONTENT HEADER
        ====================================================== --}}

        @hasSection('content_header')

            <section class="content-header">

                <div class="container-fluid">

                    @yield('content_header')

                </div>

            </section>

        @endif


        {{-- =====================================================
            MAIN CONTENT
        ====================================================== --}}

        <section class="content">

            <div class="container-fluid">

                @yield('content')

            </div>

        </section>


    </div>


    {{-- =========================================================
        FOOTER
    ========================================================== --}}

    <footer class="main-footer">


        <div class="float-right d-none d-sm-inline">

            Mudah

            <span class="mx-1">
                ·
            </span>

            Cepat

            <span class="mx-1">
                ·
            </span>

            Akurat

        </div>


        <strong>

            {{ $branding['name'] }}

        </strong>

        &copy;

        {{ date('Y') }}


    </footer>


</div>


{{-- =========================================================
    JQUERY
========================================================== --}}

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


{{-- =========================================================
    BOOTSTRAP
========================================================== --}}

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>


{{-- =========================================================
    ADMINLTE JS
========================================================== --}}

<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>


{{-- =========================================================
    GLOBAL SCRIPT
========================================================== --}}

<script>

    document.addEventListener('DOMContentLoaded', function () {


        /* =====================================================
           TOOLTIP
        ====================================================== */

        if (typeof $ !== 'undefined') {

            $('[data-toggle="tooltip"]').tooltip();

        }


        /* =====================================================
           LOGOUT CONFIRMATION
        ====================================================== */

        document
            .querySelectorAll('.pos-logout-button')
            .forEach(function (button) {

                button.addEventListener('click', function (event) {

                    const confirmed =
                        confirm(
                            'Apakah Anda yakin ingin keluar dari sistem?'
                        );

                    if (!confirmed) {

                        event.preventDefault();

                    }

                });

            });


    });

</script>


{{-- =========================================================
    PAGE JAVASCRIPT
========================================================== --}}

@yield('js')

@stack('scripts')


</body>

</html>
