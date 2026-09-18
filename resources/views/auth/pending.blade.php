@extends('layouts.guest')
@section('title', 'Menunggu Persetujuan')
@section('content')
<h2>Registrasi sedang diproses</h2><p class="muted">Silakan menunggu persetujuan dari Atasan.</p>@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif<div class="alert alert-success"><strong>Status: Pending</strong><br>Akun belum dapat digunakan sebelum disetujui.</div><a class="btn" style="display:block;text-align:center;text-decoration:none" href="{{ route('login') }}">Kembali ke Login</a>
@endsection
