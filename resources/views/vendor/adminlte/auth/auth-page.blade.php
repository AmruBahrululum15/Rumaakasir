@extends('adminlte::master')

@php
    $authType = $authType ?? 'login';
    $dashboardUrl = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home');

    if (config('adminlte.use_route_url', false)) {
        $dashboardUrl = $dashboardUrl ? route($dashboardUrl) : '';
    } else {
        $dashboardUrl = $dashboardUrl ? url($dashboardUrl) : '';
    }

    $bodyClasses = "{$authType}-page";

    if (! empty(config('adminlte.layout_dark_mode', null))) {
        $bodyClasses .= ' dark-mode';
    }
@endphp

@section('adminlte_css')
    <style>
        :root { --pos-orange: #ff7a18; --pos-background: #f6f3ef; --pos-border: #eee8e1; --pos-text: #172033; }
        body.login-page, body.register-page, body.confirm-page { min-height: 100vh; background: var(--pos-background) !important; font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .login-box, .register-box, .confirm-box { width: min(100% - 32px, 430px); }
        .login-logo a, .register-logo a, .confirm-logo a { color: var(--pos-text) !important; font-size: 25px; font-weight: 800; }
        .login-logo a::before, .register-logo a::before, .confirm-logo a::before { content: "\\f54e"; display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; margin-right: 10px; border-radius: 13px; color: #fff; background: linear-gradient(135deg, #ff7414, #ff963f); font: 900 18px/1 "Font Awesome 5 Free"; vertical-align: middle; }
        .login-logo img, .register-logo img, .confirm-logo img { display: none; }
        .login-box .card, .register-box .card, .confirm-box .card { border: 1px solid var(--pos-border) !important; border-radius: 16px !important; box-shadow: 0 8px 25px rgba(40,30,20,.045) !important; }
        .login-box .card-header, .register-box .card-header, .confirm-box .card-header { background: #fff !important; border-bottom: 1px solid var(--pos-border); }
        .login-box .form-control, .register-box .form-control, .confirm-box .form-control { height: 42px; border: 1px solid #e4ded7; border-radius: 9px; }
        .login-box .form-control:focus, .register-box .form-control:focus, .confirm-box .form-control:focus { border-color: var(--pos-orange); box-shadow: 0 0 0 3px rgba(255,122,24,.10); }
        .login-box .btn-primary, .register-box .btn-primary, .confirm-box .btn-primary { border: 0; border-radius: 9px; background: linear-gradient(135deg, #ff8a32, #ff7414); box-shadow: 0 5px 13px rgba(255,122,24,.16); }
    </style>
    @stack('css')
    @yield('css')
@stop

@section('classes_body'){{ $bodyClasses }}@stop

@section('body')
    <div class="{{ $authType }}-box">

        {{-- Logo --}}
        <div class="{{ $authType }}-logo">
            <a href="{{ $dashboardUrl }}">

                {{-- Logo Image --}}
                @if (config('adminlte.auth_logo.enabled', false))
                    <img src="{{ asset(config('adminlte.auth_logo.img.path')) }}"
                         alt="{{ config('adminlte.auth_logo.img.alt') }}"
                         @if (config('adminlte.auth_logo.img.class', null))
                            class="{{ config('adminlte.auth_logo.img.class') }}"
                         @endif
                         @if (config('adminlte.auth_logo.img.width', null))
                            width="{{ config('adminlte.auth_logo.img.width') }}"
                         @endif
                         @if (config('adminlte.auth_logo.img.height', null))
                            height="{{ config('adminlte.auth_logo.img.height') }}"
                         @endif>
                @else
                    <img src="{{ asset(config('adminlte.logo_img')) }}"
                         alt="{{ config('adminlte.logo_img_alt') }}" height="50">
                @endif

                {{-- Logo Label --}}
                {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}

            </a>
        </div>

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}">

            {{-- Card Header --}}
            @hasSection('auth_header')
                <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                    <h3 class="card-title float-none text-center">
                        @yield('auth_header')
                    </h3>
                </div>
            @endif

            {{-- Card Body --}}
            <div class="card-body {{ $authType }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>
@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
