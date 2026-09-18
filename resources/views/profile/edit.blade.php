@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content_header')
<div>
    <h1 class="mb-1 font-weight-bold">Profil Saya</h1>
    <p class="mb-0 text-muted">Kelola data pribadi, foto profil, dan keamanan akun Anda.</p>
</div>
@endsection

@section('content')
<style>
    .profile-card { border: 0; border-radius: 16px; box-shadow: 0 8px 26px rgba(23, 32, 51, .07); overflow: hidden; }
    .profile-card .card-header { padding: 20px 24px; border-bottom: 1px solid #eee8e1; background: #fff; }
    .profile-card .card-body { padding: 24px; }
    .profile-card-title { margin: 0; color: #172033; font-size: 17px; font-weight: 700; }
    .profile-card-description { margin: 4px 0 0; color: #8a8f9c; font-size: 13px; }
    .profile-photo { width: 130px; height: 130px; border: 4px solid #fff; border-radius: 18px; object-fit: cover; box-shadow: 0 7px 22px rgba(23, 32, 51, .14); }
    .profile-photo-placeholder { display: inline-flex; align-items: center; justify-content: center; width: 130px; height: 130px; border-radius: 18px; background: #fff1e7; color: #ff7a18; font-size: 42px; }
    .profile-photo-box { padding: 22px; border: 1px dashed #f4b784; border-radius: 14px; background: #fffaf4; text-align: center; }
    .profile-form-label { color: #374151; font-size: 13px; font-weight: 700; }
    .profile-card .form-control { min-height: 43px; border-color: #e6e1da; border-radius: 9px; box-shadow: none; }
    .profile-card textarea.form-control { min-height: 105px; }
    .profile-card .form-control:focus { border-color: #ff963f; box-shadow: 0 0 0 .15rem rgba(255, 122, 24, .13); }
    .btn-profile-save { padding: 10px 20px; border: 0; border-radius: 9px; background: #ff7a18; color: #fff; font-weight: 700; }
    .btn-profile-save:hover { background: #f36b0a; color: #fff; }
</style>

@if (session('status') === 'profile-updated')
    <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle mr-2"></i>Data profil berhasil diperbarui.</div>
@endif
@if (session('status') === 'password-updated')
    <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle mr-2"></i>Password berhasil diubah.</div>
@endif

<div class="row">
    <div class="col-lg-8 mb-4"><div class="card profile-card h-100"><div class="card-header"><h2 class="profile-card-title"><i class="fas fa-user-circle mr-2 text-warning"></i>Informasi Profil</h2><p class="profile-card-description">Lengkapi data diri yang digunakan pada akun ini.</p></div><div class="card-body">@include('profile.partials.update-profile-information-form')</div></div></div>
    <div class="col-lg-4 mb-4"><div class="card profile-card h-100"><div class="card-header"><h2 class="profile-card-title"><i class="fas fa-lock mr-2 text-warning"></i>Ganti Password</h2><p class="profile-card-description">Gunakan password yang kuat dan tidak mudah ditebak.</p></div><div class="card-body">@include('profile.partials.update-password-form')</div></div></div>
</div>
@endsection
