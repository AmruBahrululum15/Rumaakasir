@extends('layouts.guest')
@section('title', 'Pengajuan Diterima')
@section('content')
<h2>Pendaftaran berhasil dikirim.</h2><p class="muted">Pengajuan toko Anda sedang ditinjau oleh Super Admin. Kami akan mengirimkan pemberitahuan melalui email setelah proses review.</p><div class="auth-links"><a href="{{ route('login') }}">Kembali ke Login</a></div>
@endsection
