@extends('portal.layout')
@section('content')
@php($title=['login'=>'Masuk sebagai pelanggan','register'=>'Buat akun pelanggan','forgot'=>'Lupa password','reset'=>'Password baru','profile'=>'Profil Saya'][$mode])
@if($mode==='register')
@include('portal.partials.customer-register')
@else
<section class="card auth" @if($mode==='register') data-registration-passwords @endif><h1>{{ $title }}</h1><p>Satu akun untuk berinteraksi dengan toko pilihan Anda.</p>
<form method="post" action="{{ $mode==='profile'?route('customer.profile'):route('customer.'.$mode.'.submit') }}">@csrf @if($mode==='profile') @method('patch') @endif
@if(in_array($mode,['register','profile']))<label for="name">Nama</label><input id="name" name="name" required maxlength="120" value="{{ old('name',auth('customer')->user()?->name) }}"><label for="phone">Nomor telepon</label><input id="phone" name="phone" required maxlength="30" value="{{ old('phone',auth('customer')->user()?->phone) }}">@endif
@if($mode==='login')<label for="identifier">Email atau Nomor HP</label><input id="identifier" name="identifier" type="text" required maxlength="190" autocomplete="username" placeholder="Email atau nomor HP Anda" value="{{ old('identifier',old('email')) }}">
@elseif($mode!=='profile')<label for="email">Email</label><input id="email" name="email" type="email" required autocomplete="email" value="{{ old('email',request('email')) }}" @if($mode==='reset') readonly @endif>@endif
@if(in_array($mode,['login','register','reset']))<label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="{{ $mode==='login'?'current-password':'new-password' }}">@endif
@if(in_array($mode,['register','reset']))<label for="confirmation">Konfirmasi password</label><input id="confirmation" name="password_confirmation" type="password" required autocomplete="new-password">@endif
@if($mode==='reset')<input type="hidden" name="token" value="{{ request('token') }}">@endif
<button>{{ ['login'=>'Masuk','register'=>'Daftar','forgot'=>'Kirim instruksi reset','reset'=>'Simpan password','profile'=>'Simpan profil'][$mode] }}</button></form>
<div class="auth-links">@if($mode!=='login')<a href="{{ route('customer.login') }}">Masuk customer</a>@endif<a href="{{ route('customer.register') }}">Daftar Sekarang</a><a href="{{ route('customer.forgot') }}">Lupa password?</a></div>
<p style="text-align:center;font-size:12px;margin-top:24px"><a href="{{ route('login') }}">Masuk ke Rumaakasir Bisnis</a></p></section>
@endif
@endsection
