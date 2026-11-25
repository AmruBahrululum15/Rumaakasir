@extends('layouts.login')
@section('content')
<header class="card-heading"><p class="eyebrow">SELAMAT DATANG KEMBALI</p><h2 id="login-heading">Selamat Datang</h2><p>Masuk untuk melanjutkan ke akun Anda.</p></header>
@if(session('status') || session('success'))<div class="login-alert success" role="status"><svg class="icon" aria-hidden="true"><use href="#icon-check"/></svg><span>{{ session('status') ?: session('success') }}</span></div>@endif
@if($errors->any())<div class="login-alert error" id="login-errors" role="alert"><svg class="icon" aria-hidden="true"><use href="#icon-alert"/></svg><div>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div></div>@endif
<form method="POST" action="{{ route('login') }}" id="login-form" data-loading>@csrf
<div class="login-field"><label for="login">Username</label><div class="input-wrap"><svg class="icon" aria-hidden="true"><use href="#icon-user"/></svg><input id="login" name="login" value="{{ old('login') }}" required autocomplete="username" autocapitalize="none" spellcheck="false" placeholder="Masukkan username" @if($errors->has('login')) aria-invalid="true" aria-describedby="login-errors" @endif></div></div>
<div class="login-field"><label for="password">Password</label><div class="input-wrap"><svg class="icon" aria-hidden="true"><use href="#icon-lock"/></svg><input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password" @if($errors->has('password')) aria-invalid="true" aria-describedby="login-errors" @endif><button class="password-toggle" type="button" data-password-toggle="password" aria-label="Tampilkan password" aria-controls="password" aria-pressed="false"><svg class="icon" aria-hidden="true"><use href="#icon-eye"/><path class="eye-slash" d="m3 3 18 18"/></svg></button></div></div>
<div class="login-options"><label class="remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat Saya</label><a href="{{ route('password.request') }}">Lupa Password?</a></div>
<button class="login-button primary" type="submit"><span data-login-label>Masuk</span><svg class="icon" aria-hidden="true"><use href="#icon-arrow"/></svg></button>
</form>
@if(config('demo.enabled'))
<aside class="demo-access" aria-label="Akses akun demo">
    <span class="demo-access-icon" aria-hidden="true">D</span>
    <div><strong>Coba akun demo</strong><p>Username: <code>{{ config('demo.username') }}</code>@if(config('demo.password_hint')) · Password: <code>{{ config('demo.password_hint') }}</code>@endif</p></div>
</aside>
@endif
<div class="divider"><span>Belum punya akun?</span></div>
<section class="registration-options" aria-label="Pilihan pendaftaran">
    <article class="registration-option customer-option">
        <h3>Daftar sebagai Pelanggan</h3>
        <p>Belanja, booking, kumpulkan poin, dan chat dengan toko.</p>
        <a class="login-button secondary" href="{{ route('customer.register') }}">Daftar Pelanggan</a>
    </article>
    <article class="registration-option store-option">
        <h3>Daftarkan Toko</h3>
        <p>Kelola bisnis Anda menggunakan {{ (\App\Models\PlatformSetting::find(1) ?? new \App\Models\PlatformSetting())->brandingName() }}.</p>
        <a class="login-button secondary" href="{{ route('store-registration.create') }}">Daftarkan Toko</a>
    </article>
</section>
<p class="customer-login-link">Sudah punya akun pelanggan? <a href="{{ route('customer.login') }}">Masuk sebagai Pelanggan</a></p>
@endsection
