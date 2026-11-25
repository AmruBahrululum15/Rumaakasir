@extends('layouts.guest')
@section('title', 'Aktifkan Akun Toko')
@section('content')
<h2>Aktifkan akun Anda</h2><p class="muted">Pengajuan {{ $registration->store_name }} disetujui. Lanjutkan untuk membuat password sebelum menggunakan toko.</p>
<form method="POST" action="{{ request()->fullUrl() }}" data-loading style="margin-top:24px">@csrf<button type="submit" class="auth-button">Lanjutkan ke Pengaturan Akun</button></form>
@endsection
