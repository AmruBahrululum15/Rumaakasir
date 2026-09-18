@extends('layouts.guest')
@section('title', 'Masuk')
@section('content')
<h2>Selamat datang</h2><p class="muted">Masuk untuk melanjutkan ke sistem.</p>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('login') }}" data-loading>@csrf
<div class="form-group"><label for="login">Username atau email</label><div class="input-wrap"><i class="fas fa-user"></i><input id="login" class="form-control @error('login') invalid @enderror" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="Masukkan username atau email"></div>@error('login')<div class="field-error">{{ $message }}</div>@enderror</div>
<div class="form-group"><label for="password">Password</label><div class="input-wrap"><i class="fas fa-lock"></i><input id="password" class="form-control @error('password') invalid @enderror" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password"><button class="toggle-password" type="button" data-password-toggle="password" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button></div>@error('password')<div class="field-error">{{ $message }}</div>@enderror</div>
<label class="checkbox"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya</label><button class="auth-button" type="submit">Masuk <span aria-hidden="true">→</span></button>
</form><div class="auth-links"><a href="{{ route('password.request') }}">Lupa Password?</a><a href="{{ route('register') }}">Daftar Akun</a></div>
@endsection
