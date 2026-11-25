@extends('portal.layout')
@section('title', 'Profil Saya')
@section('content')
<section class="hero"><span class="badge">AKUN ANDA</span><h1>Profil Saya</h1><p>Informasi pribadi dan alamat, dalam kendali Anda.</p></section>
<div class="profile-grid"><section class="card"><h2>Data pribadi</h2>
<form method="post" action="{{ route('customer.profile') }}" enctype="multipart/form-data">@csrf @method('patch')
<div class="profile-photo"><span class="avatar large">@if($account->avatar_path)<img src="{{ route('customer.avatar') }}" alt="Foto profil">@else{{ $account->initials }}@endif</span><div><label for="avatar">Foto profil</label><input id="avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp"><small>JPG, PNG atau WebP, maksimal 2 MB.</small></div></div>
<img id="avatar-preview" hidden alt="Preview foto baru" class="avatar large">
<label for="name">Nama lengkap</label><input id="name" name="name" required maxlength="120" value="{{ old('name',$account->name) }}">
<label for="email">Email</label><input id="email" name="email" type="email" required value="{{ old('email',$account->email) }}">
<label for="phone">Nomor HP</label><input id="phone" name="phone" required value="{{ old('phone',$account->phone) }}">
<label for="birth_date">Tanggal lahir <small>(opsional)</small></label><input id="birth_date" name="birth_date" type="date" max="{{ date('Y-m-d') }}" value="{{ old('birth_date',$account->birth_date) }}"><button>Simpan profil</button>
</form></section><section><div class="card"><h2>Alamat Saya</h2><p>Alamat tidak ditampilkan kepada customer lain atau toko melalui profil Anda.</p>
@foreach($account->addresses as $address)<details class="address-card"><summary>{{ $address->label }} · {{ $address->city }} @if($address->is_default)<span class="badge">Utama</span>@endif</summary><p>{{ $address->address }}</p>@include('portal.partials.address-form',['address'=>$address])</details>@endforeach
<details class="address-card" @if($account->addresses->isEmpty()) open @endif><summary>Tambah alamat</summary>@include('portal.partials.address-form',['address'=>null])</details>
</div><div class="card account-links"><a href="{{ route('customer.vouchers') }}">Voucher Saya</a><a href="{{ route('customer.bookings') }}">Reservasi Saya</a><a href="{{ route('customer.chat.index') }}">Chat Saya</a><form method="post" action="{{ route('customer.logout') }}">@csrf<button class="secondary">Keluar dari akun</button></form></div></section></div>
@endsection
@push('scripts')<script>document.getElementById('avatar').addEventListener('change',e=>{const img=document.getElementById('avatar-preview');if(img.dataset.blob)URL.revokeObjectURL(img.dataset.blob);const file=e.target.files[0];img.hidden=!file;if(file){img.dataset.blob=URL.createObjectURL(file);img.src=img.dataset.blob;}});</script>@endpush
