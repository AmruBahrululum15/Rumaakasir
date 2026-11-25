@extends('layouts.app')
@section('title', 'Profil')
@section('description', 'Kelola informasi pribadi akun Super Admin.')
@section('content')
<section class="content"><div class="container-fluid">
    @if(session('status') === 'profile-updated')<div class="alert alert-success">Profil berhasil diperbarui.</div>@endif
    <div class="card"><div class="card-body">
        <p class="text-muted">Username: <strong>{{ $user->username ?: '-' }}</strong></p>
        @include('profile.partials.update-profile-information-form', ['profileUpdateRoute' => 'super-admin.profile.update'])
    </div></div>
</div></section>
@endsection
