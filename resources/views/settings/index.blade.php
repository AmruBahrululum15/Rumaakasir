@extends('layouts.app')
@section('title', 'Pengaturan')
@section('content')
<link rel="stylesheet" href="{{ asset('css/store-settings.css') }}">
<div class="store-settings">
<header class="ss-heading"><span>PREFERENSI TOKO</span><h1>Pengaturan Toko</h1><p>Kelola informasi bisnis, tampilan struk, dan lokasi toko Anda.</p></header>
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Periksa kembali pengaturan Anda.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
@php
    $canBranding = app(\App\Services\PackageAccess::class)->allows('custom_branding');
@endphp
<div class="ss-layout"><div>
<div class="card shadow-sm"><div class="card-header bg-white"><h4 class="mb-0"><i class="fas fa-store" aria-hidden="true"></i> Informasi & Branding</h4><p class="ss-muted">Informasi ini ditampilkan pada struk transaksi toko.</p></div><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(! $canBranding)<div class="alert alert-info">Custom Branding <span class="badge badge-warning">Premium</span>. Fitur ini tersedia pada paket Premium. Informasi kontak tetap dapat diperbarui.</div>@endif
<form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
<h5 class="border-bottom pb-2">Informasi Bisnis</h5><div class="row">
<div class="col-md-6 form-group"><label for="ss-app_name">Nama Bisnis</label><input class="form-control" id="ss-app_name" name="app_name" @readonly(! $canBranding) required maxlength="100" value="{{ old('app_name', $settings['app_name'] ?? 'POS Enterprise') }}"></div>
<div class="col-md-6 form-group"><label for="ss-company_name">Nama Perusahaan</label><input class="form-control" id="ss-company_name" name="company_name" @readonly(! $canBranding) maxlength="150" value="{{ old('company_name', $settings['company_name'] ?? '') }}"></div>
<div class="col-12 form-group"><label for="ss-business_address">Alamat</label><textarea class="form-control" id="ss-business_address" name="business_address" rows="2">{{ old('business_address', $settings['business_address'] ?? '') }}</textarea></div>
<div class="col-md-6 form-group"><label for="ss-business_phone">Telepon</label><input class="form-control" id="ss-business_phone" name="business_phone" maxlength="50" value="{{ old('business_phone', $settings['business_phone'] ?? '') }}"></div>
<div class="col-md-6 form-group"><label for="ss-business_email">Email</label><input type="email" class="form-control" id="ss-business_email" name="business_email" value="{{ old('business_email', $settings['business_email'] ?? '') }}"></div>
<div class="col-12 form-group"><label for="ss-app_logo">Logo Struk</label><input type="file" class="form-control-file" id="ss-app_logo" name="app_logo" @disabled(! $canBranding) accept="image/png,image/jpeg,image/webp">@if(!empty($settings['app_logo']))<img class="mt-2" style="max-height:70px;max-width:150px" src="{{ asset('storage/'.$settings['app_logo']) }}" alt="Logo saat ini">@endif</div>
</div><h5 class="border-bottom pb-2 mt-3">Pengaturan Struk</h5>
<div class="form-group"><label for="ss-receipt_header">Header Struk</label><textarea class="form-control" id="ss-receipt_header" name="receipt_header" @readonly(! $canBranding) rows="3" maxlength="1000">{{ old('receipt_header', $settings['receipt_header'] ?? '') }}</textarea></div>
<div class="form-group"><label for="ss-receipt_footer">Footer Struk</label><textarea class="form-control" id="ss-receipt_footer" name="receipt_footer" @readonly(! $canBranding) rows="3" maxlength="1000">{{ old('receipt_footer', $settings['receipt_footer'] ?? '') }}</textarea></div>
<input type="hidden" name="app_description" value="{{ old('app_description', $settings['app_description'] ?? '') }}"><button class="btn btn-primary">Simpan Pengaturan</button>
</form></div></div></div>
<aside class="ss-preview"><div class="card shadow-sm"><div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-receipt" aria-hidden="true"></i> Preview Struk</h5><p class="ss-muted">Contoh tampilan dengan pengaturan tersimpan.</p></div><div class="card-body text-center ss-receipt" style="white-space:pre-line">@if(!empty($settings['app_logo']))<img style="max-height:60px;max-width:120px" src="{{ asset('storage/'.$settings['app_logo']) }}" alt="Logo">@endif
<strong class="d-block">{{ $settings['app_name'] ?? 'POS Enterprise' }}</strong>{{ $settings['business_address'] ?? '' }}
{{ !empty($settings['business_phone']) ? 'Telp. '.$settings['business_phone'] : '' }}
<hr>{{ $settings['receipt_header'] ?? '' }}<hr><div class="text-left">No. Transaksi : CONTOH-001<br>Kasir : Kasir Contoh<br>Pelanggan : Pelanggan Umum<br>Pembayaran : Cash<br>Sumber : Kasir<br>Total : Rp 35.000</div><hr>{{ $settings['receipt_footer'] ?? '' }}</div></div></aside>
</div>
@if(!auth()->user()->hasRole('Super Admin') && auth()->user()->hasRole('Atasan') && auth()->user()->can('settings.edit'))
<a class="ss-payment-link" href="{{ route('settings.customer-payments') }}"><span class="ss-link-icon"><i class="fas fa-credit-card" aria-hidden="true"></i></span><span><strong>Pembayaran pelanggan</strong><small>Kelola metode pembayaran yang tersedia untuk toko Anda.</small></span><i class="fas fa-chevron-right" aria-hidden="true"></i></a>
<section class="card ss-location">
<div class="card-header"><h5><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Lokasi Toko</h5><p class="ss-muted">Bantu pelanggan menemukan toko dan melihat jaraknya.</p></div>
<div class="card-body ss-location-layout">
<form method="post" action="{{ route('settings.location') }}">@csrf @method('put')
@php
    $business = auth()->user()->business;
    $savedMaps = $settings['store_maps_url'] ?? '';
    if (!$savedMaps && $business?->latitude !== null && $business?->longitude !== null) {
        $savedMaps = 'https://www.google.com/maps?q='.$business->latitude.','.$business->longitude;
    }
@endphp
<label for="store-maps-url">Link Google Maps</label>
<input id="store-maps-url" class="form-control" type="url" name="maps_url" maxlength="4096" value="{{ old('maps_url', $savedMaps) }}" placeholder="https://maps.app.goo.gl/..." aria-describedby="maps-help" @error('maps_url') aria-invalid="true" @enderror>
<small id="maps-help" class="ss-help">Tempel link lokasi toko. Koordinat akan dibaca otomatis.</small>
@error('maps_url')<small class="text-danger d-block">{{ $message }}</small>@enderror
<div class="ss-location-actions"><button class="btn btn-primary"><i class="fas fa-check mr-1" aria-hidden="true"></i> Simpan Lokasi</button>
@if($business?->latitude !== null && $business?->longitude !== null)<a class="btn btn-outline-primary" href="{{ 'https://www.google.com/maps?q='.$business->latitude.','.$business->longitude }}" target="_blank" rel="noopener noreferrer">Lihat di Maps <i class="fas fa-external-link-alt ml-1" aria-hidden="true"></i></a>@endif
</div><small class="ss-help">Kosongkan link dan simpan untuk menghapus titik lokasi toko.</small>
</form>
<aside class="ss-map-guide"><strong><i class="far fa-map" aria-hidden="true"></i> Cara mendapatkan link</strong><ol><li>Buka lokasi toko di Google Maps.</li><li>Pilih <b>Bagikan</b>, lalu <b>Salin link</b>.</li><li>Tempel link di kolom sebelah dan simpan.</li></ol></aside>
</div></section>
@endif
</div>
@endsection
