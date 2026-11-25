@php
    $platform = \App\Models\PlatformSetting::find(1) ?? new \App\Models\PlatformSetting();
    $platformName = $platform->brandingName();
    $platformLogo = $platform->brandingLogo();
@endphp
<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><title>Masuk &middot; {{ $platformName }}</title><link rel="stylesheet" href="{{ asset('css/login.css') }}"><script src="{{ asset('js/login.js') }}" defer></script></head>
<body class="login-page">
<svg class="icon-definitions" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs>
<symbol id="icon-store" viewBox="0 0 24 24"><path d="M3 9l2-5h14l2 5M3 9v2a3 3 0 0 0 5 2 3 3 0 0 0 4 0 3 3 0 0 0 4 0 3 3 0 0 0 5-2V9H3zM5 14v6h14v-6M10 20v-5h4v5"/></symbol>
<symbol id="icon-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></symbol>
<symbol id="icon-lock" viewBox="0 0 24 24"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3"/></symbol>
<symbol id="icon-eye" viewBox="0 0 24 24"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></symbol>
<symbol id="icon-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></symbol>
<symbol id="icon-arrow" viewBox="0 0 24 24"><path d="M5 12h14m-5-5 5 5-5 5"/></symbol>
<symbol id="icon-alert" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 3v1"/></symbol>
</defs></svg>
<main class="login-shell"><section class="login-intro" aria-label="Tentang {{ $platformName }}">
@include('partials.auth.platform-brand')
<div class="intro-content"><p class="eyebrow">SATU PLATFORM UNTUK BISNIS ANDA</p><h1>Kelola bisnis<br>lebih mudah.<br><span>Tumbuh bersama.</span></h1><p class="intro-description">Penjualan, stok, pelanggan, dan laporan.<br>Semua terhubung dalam satu sistem.</p>
<div class="illustration" aria-hidden="true"><div class="preview"><div class="preview-top"><span class="dots"><i></i><i></i><i></i></span><span>Ringkasan bisnis</span><span class="pill"></span></div><div class="preview-body"><div class="preview-nav"><b></b><i></i><i></i><i></i></div><div class="preview-main"><div class="preview-cards"><span><i></i><b></b></span><span><i></i><b></b></span><span><i></i><b></b></span></div><div class="preview-chart"><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div></div></div></div><div class="preview-note"><svg class="icon"><use href="#icon-check"/></svg>Operasional lebih teratur</div></div>
<ul class="benefits">@foreach(['Kelola penjualan & stok', 'Monitoring bisnis secara real-time', 'Cocok untuk banyak jenis usaha'] as $benefit)<li><svg class="icon" aria-hidden="true"><use href="#icon-check"/></svg>{{ $benefit }}</li>@endforeach</ul></div>
<p class="intro-footer">Fokus pada bisnis Anda. Kami bantu operasionalnya.</p></section>
<section class="login-panel" aria-labelledby="login-heading"><div class="mobile-brand">@include('partials.auth.platform-brand')</div><div class="login-card">@yield('content')</div><footer class="login-footer">&copy; {{ now()->year }} {{ $platformName }}. All rights reserved.</footer></section></main>
</body></html>
