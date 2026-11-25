@extends('layouts.guest')
@section('title', 'Lupa Password')
@section('content')
<h2>Lupa password?</h2><p class="muted">Masukkan username Anda. Kami akan mengirimkan tautan pengaturan ulang password.</p>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}" data-loading>@csrf<div class="form-group"><label for="username">Username</label><div class="input-wrap"><i class="fas fa-user"></i><input class="form-control @error('username') invalid @enderror" id="username" type="text" name="username" autocomplete="username" value="{{ old('username') }}" required autofocus></div>@error('username')<div class="field-error">{{ $message }}</div>@enderror</div><button class="btn">Kirim tautan reset</button></form><div class="auth-links"><a href="{{ route('login') }}">Kembali ke Login</a></div>
@endsection
