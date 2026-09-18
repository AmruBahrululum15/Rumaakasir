@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<div class="container-fluid py-3"><div class="row"><div class="col-lg-7">
<div class="card shadow-sm"><div class="card-header bg-white"><h4 class="mb-0">Pengaturan Bisnis & Struk</h4></div><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
<h5 class="border-bottom pb-2">Informasi Bisnis</h5><div class="row">
<div class="col-md-6 form-group"><label>Nama Bisnis</label><input class="form-control" name="app_name" required maxlength="100" value="{{ old('app_name', $settings['app_name'] ?? 'POS Enterprise') }}"></div>
<div class="col-md-6 form-group"><label>Nama Perusahaan</label><input class="form-control" name="company_name" maxlength="150" value="{{ old('company_name', $settings['company_name'] ?? '') }}"></div>
<div class="col-12 form-group"><label>Alamat</label><textarea class="form-control" name="business_address" rows="2">{{ old('business_address', $settings['business_address'] ?? '') }}</textarea></div>
<div class="col-md-6 form-group"><label>Telepon</label><input class="form-control" name="business_phone" maxlength="50" value="{{ old('business_phone', $settings['business_phone'] ?? '') }}"></div>
<div class="col-md-6 form-group"><label>Email</label><input type="email" class="form-control" name="business_email" value="{{ old('business_email', $settings['business_email'] ?? '') }}"></div>
<div class="col-12 form-group"><label>Logo Struk</label><input type="file" class="form-control-file" name="app_logo" accept="image/png,image/jpeg,image/webp">@if(!empty($settings['app_logo']))<img class="mt-2" style="max-height:70px;max-width:150px" src="{{ asset('storage/'.$settings['app_logo']) }}" alt="Logo saat ini">@endif</div>
</div><h5 class="border-bottom pb-2 mt-3">Pengaturan Struk</h5>
<div class="form-group"><label>Header Struk</label><textarea class="form-control" name="receipt_header" rows="3" maxlength="1000">{{ old('receipt_header', $settings['receipt_header'] ?? '') }}</textarea></div>
<div class="form-group"><label>Footer Struk</label><textarea class="form-control" name="receipt_footer" rows="3" maxlength="1000">{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}</textarea></div>
<input type="hidden" name="app_description" value="{{ old('app_description', $settings['app_description'] ?? '') }}"><button class="btn btn-primary">Simpan Pengaturan</button>
</form></div></div></div>
<div class="col-lg-5"><div class="card shadow-sm"><div class="card-header bg-white"><h5 class="mb-0">Preview Struk</h5></div><div class="card-body text-center" style="white-space:pre-line">@if(!empty($settings['app_logo']))<img style="max-height:60px;max-width:120px" src="{{ asset('storage/'.$settings['app_logo']) }}" alt="Logo">@endif
<strong class="d-block">{{ $settings['app_name'] ?? 'POS Enterprise' }}</strong>{{ $settings['business_address'] ?? '' }}
{{ !empty($settings['business_phone']) ? 'Telp. '.$settings['business_phone'] : '' }}
<hr>{{ $settings['receipt_header'] ?? '' }}<hr><div class="text-left">No. Transaksi : CONTOH-001<br>Kasir : Kasir Contoh<br>Pelanggan : Pelanggan Umum<br>Pembayaran : Cash<br>Sumber : Kasir<br>Total : Rp35.000</div><hr>{{ $settings['receipt_footer'] ?? '' }}</div></div></div>
</div></div>
@endsection
