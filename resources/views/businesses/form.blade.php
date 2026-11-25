@extends('layouts.app')

@section('title', $business->exists ? 'Edit Toko' : 'Tambah Toko')

@section('content')
<div class="content-header"><div class="container-fluid"><h1 class="m-0">{{ $business->exists ? 'Edit Toko' : 'Tambah Toko' }}</h1></div></div>
<section class="content"><div class="container-fluid"><div class="card card-primary"><form method="POST" action="{{ $business->exists ? route('businesses.update', $business) : route('businesses.store') }}">@csrf @if($business->exists) @method('PUT') @endif
    <div class="card-body">
        <div class="form-group"><label>Nama Toko</label><input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $business->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Slug <small class="text-muted">(opsional)</small></label><input name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $business->slug) }}">@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Telepon</label><input name="phone" class="form-control" value="{{ old('phone', $business->phone) }}"></div>
        <div class="form-group"><label>Alamat</label><textarea name="address" class="form-control" rows="3">{{ old('address', $business->address) }}</textarea></div>
        <div class="row"><div class="col-md-6"><div class="form-group"><label>Paket</label><select name="subscription_plan" class="form-control"><option value="free" @selected(old('subscription_plan', $business->subscription_plan ?: 'free') === 'free')>{{ \App\Models\Package::where('code', 'free')->value('name') }}</option><option value="premium" @selected(old('subscription_plan', $business->subscription_plan) === 'premium')>{{ \App\Models\Package::where('code', 'premium')->value('name') }}</option></select></div></div><div class="col-md-6"><div class="form-group"><label>Status Subscription</label><select name="subscription_status" class="form-control"><option value="active" @selected(old('subscription_status', $business->subscription_status ?: 'active') === 'active')>Aktif</option><option value="inactive" @selected(old('subscription_status', $business->subscription_status) === 'inactive')>Nonaktif</option><option value="expired" @selected(old('subscription_status', $business->subscription_status) === 'expired')>Kedaluwarsa</option><option value="suspended" @selected(old('subscription_status', $business->subscription_status) === 'suspended')>Ditangguhkan</option></select></div></div></div>
        <div class="form-group"><label for="subscription_ends_at">Akhir langganan (kosong = tanpa kedaluwarsa)</label><input id="subscription_ends_at" type="datetime-local" name="subscription_ends_at" class="form-control" value="{{ old('subscription_ends_at', $business->subscription_ends_at?->format('Y-m-d\TH:i')) }}">@error('subscription_ends_at')<div class="text-danger">{{ $message }}</div>@enderror</div>
        <div class="row">
            <div class="col-md-6 form-group"><label for="user_limit">Batas pengguna (termasuk Atasan)</label><input id="user_limit" type="number" min="1" name="user_limit" class="form-control" value="{{ old('user_limit', $business->user_limit) }}"><small class="text-muted">Kosong = mengikuti paket. Batas toko hanya dapat memperketat batas paket. Akun existing tetap dipertahankan.</small>@error('user_limit')<div class="text-danger">{{ $message }}</div>@enderror</div>
            <div class="col-md-6 form-group"><label for="product_limit">Batas produk</label><input id="product_limit" type="number" min="1" name="product_limit" class="form-control" value="{{ old('product_limit', $business->product_limit) }}"><small class="text-muted">Kosong = mengikuti paket. Batas toko hanya dapat memperketat batas paket. Produk existing tetap dipertahankan.</small>@error('product_limit')<div class="text-danger">{{ $message }}</div>@enderror</div>
        </div>
        @if(! $business->exists)
        <hr><h5>Atasan Utama</h5>
        <div class="form-group"><label>Nama Atasan</label><input name="supervisor_name" class="form-control @error('supervisor_name') is-invalid @enderror" value="{{ old('supervisor_name') }}" required>@error('supervisor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Username Atasan</label><input name="supervisor_username" class="form-control @error('supervisor_username') is-invalid @enderror" value="{{ old('supervisor_username') }}" required>@error('supervisor_username')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Email Atasan</label><input type="email" name="supervisor_email" class="form-control @error('supervisor_email') is-invalid @enderror" value="{{ old('supervisor_email') }}" required>@error('supervisor_email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label>Password Awal Atasan</label><input type="password" name="supervisor_password" class="form-control" required></div>
        <div class="form-group"><label>Konfirmasi Password Awal</label><input type="password" name="supervisor_password_confirmation" class="form-control" required></div>
        @endif
        <div class="form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" @checked(old('is_active', $business->exists ? $business->is_active : true))><label class="form-check-label" for="is_active">Toko aktif</label></div>
    </div>
    <div class="card-footer"><button class="btn btn-primary">Simpan</button><a href="{{ route('businesses.index') }}" class="btn btn-default">Batal</a></div>
</form></div></div></section>
@endsection
