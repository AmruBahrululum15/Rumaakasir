@extends('layouts.guest')
@section('title', 'Lupa Password')
@section('content')
<h2>Lupa password?</h2><p class="muted">Masukkan email Anda. Kami akan mengirimkan tautan pengaturan ulang password.</p>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('password.email') }}" data-loading>@csrf<div class="form-group"><label for="email">Email</label><div class="input-wrap"><i class="fas fa-envelope"></i><input class="form-control @error('email') invalid @enderror" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus></div>@error('email')<div class="field-error">{{ $message }}</div>@enderror</div><button class="btn">Kirim tautan reset</button></form><div class="auth-links"><a href="{{ route('login') }}">Kembali ke Login</a></div>
@endsection
