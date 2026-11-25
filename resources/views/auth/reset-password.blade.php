@extends('layouts.guest')
@section('title', 'Atur Ulang Password')
@section('content')
<h2>Atur ulang password</h2><p class="muted">Pilih password baru yang aman untuk akun Anda.</p>
<form method="POST" action="{{ route('password.store') }}" data-loading>@csrf<input type="hidden" name="token" value="{{ $request->route('token') }}"><div class="form-group"><label>Username</label><div class="input-wrap"><i class="fas fa-user"></i><input class="form-control" type="text" name="username" readonly value="{{ $request->username }}" required></div>@error('username')<div class="field-error">{{ $message }}</div>@enderror</div>@foreach(['password'=>'Password baru','password_confirmation'=>'Konfirmasi password'] as $field=>$label)<div class="form-group"><label>{{ $label }}</label><div class="input-wrap"><i class="fas fa-lock"></i><input id="{{ $field }}" class="form-control" type="password" name="{{ $field }}" required><button class="toggle-password" type="button" data-password-toggle="{{ $field }}"><i class="fas fa-eye"></i></button></div>@error($field)<div class="field-error">{{ $message }}</div>@enderror</div>@endforeach<button class="btn">Simpan password baru</button></form>
@endsection
