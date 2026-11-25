@extends('layouts.app')
@section('title', 'Pengaturan Akun')
@section('description', 'Jaga keamanan akun Super Admin dengan memperbarui password.')
@section('content')
<section class="content"><div class="container-fluid">
    @if(session('status') === 'password-updated')<div class="alert alert-success">Password berhasil diperbarui.</div>@endif
    <div class="card"><div class="card-body"><div class="row"><div class="col-lg-7">
        <h2 class="h5 mb-4">Ubah Password</h2>
        @include('profile.partials.update-password-form')
    </div></div></div></div>
</div></section>
@endsection
