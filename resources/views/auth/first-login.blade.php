@extends('layouts.guest')
@section('title', 'Pengaturan Akun Pertama')
@section('content')
<h2>Pengaturan Akun Pertama</h2><p class="muted">Untuk keamanan, silakan ubah username dan password sebelum melanjutkan.</p>
<form method="POST" action="{{ route('first-login.update') }}" data-loading>@csrf @method('PUT')<div class="form-group"><label>Username baru</label><div class="input-wrap"><i class="fas fa-user"></i><input class="form-control" name="username" value="{{ old('username') }}" required autofocus></div>@error('username')<div class="field-error">{{ $message }}</div>@enderror</div>@foreach(['password'=>'Password baru','password_confirmation'=>'Konfirmasi password baru'] as $field=>$label)<div class="form-group"><label>{{ $label }}</label><div class="input-wrap"><i class="fas fa-lock"></i><input id="{{ $field }}" class="form-control" type="password" name="{{ $field }}" required><button class="toggle-password" type="button" data-password-toggle="{{ $field }}"><i class="fas fa-eye"></i></button></div>@error($field)<div class="field-error">{{ $message }}</div>@enderror</div>@endforeach<button class="btn">Simpan dan lanjutkan</button></form>
@endsection
