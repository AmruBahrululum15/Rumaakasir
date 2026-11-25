@extends('layouts.app')
@section('title','Pembayaran Pelanggan')
@section('content')
<link rel="stylesheet" href="{{ asset('css/customer-payment-settings.css') }}">
<div class="payment-settings">
<header class="ps-heading"><div><span class="ps-eyebrow">PENGATURAN TOKO</span><h1>Pembayaran Pelanggan</h1><p>Kelola metode pembayaran yang tersedia untuk pelanggan {{ $business->name }}.</p></div><a class="ps-back" href="{{ route('settings.index') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i> Pengaturan toko</a></header>
@if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Periksa kembali pengaturan pembayaran.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" enctype="multipart/form-data" action="{{ route('settings.customer-payments.update') }}">@csrf @method('put')
<section class="ps-card"><div class="ps-card-heading"><span class="ps-icon"><i class="fas fa-wallet" aria-hidden="true"></i></span><div><h2>Metode pembayaran</h2><p>Pilih metode yang ingin Anda sediakan di toko.</p></div></div>
<div class="ps-methods">
@foreach(['cash_enabled'=>['Cash','Pembayaran tunai, dikonfirmasi oleh kasir.','money-bill-wave'],'qris_enabled'=>['QRIS','Pembayaran dengan scan QRIS toko.','qrcode'],'va_enabled'=>['Virtual Account','Pembayaran melalui rekening virtual Midtrans toko.','university']] as $field=>$method)
<label class="ps-method" for="ps-{{ $field }}"><span class="ps-method-icon"><i class="fas fa-{{ $method[2] }}" aria-hidden="true"></i></span><span class="ps-method-text"><strong>{{ $method[0] }}</strong><small>{{ $method[1] }}</small></span><input type="hidden" name="{{ $field }}" value="0"><input id="ps-{{ $field }}" type="checkbox" name="{{ $field }}" value="1" @checked(old($field,$settings->$field))><span class="ps-switch" aria-hidden="true"></span></label>
@endforeach
</div></section>
<div class="ps-columns">
<section class="ps-card"><div class="ps-card-heading"><span class="ps-icon"><i class="fas fa-qrcode" aria-hidden="true"></i></span><div><h2>Pengaturan QRIS</h2><p>Gunakan QRIS manual atau gateway toko Anda.</p></div></div><div class="ps-card-body">
<div class="ps-field"><label for="qris-mode">Jenis QRIS</label><select class="form-control" id="qris-mode" name="qris_mode"><option value="manual" @selected(old('qris_mode',$settings->qris_mode)==='manual')>QRIS manual — dikonfirmasi kasir</option><option value="midtrans" @selected(old('qris_mode',$settings->qris_mode)==='midtrans')>Midtrans toko — konfirmasi otomatis</option></select></div>
<div class="ps-field"><label for="qris-image">Gambar QRIS milik toko</label><div class="ps-upload"><span class="ps-upload-icon"><i class="far fa-image" aria-hidden="true"></i></span><strong>Upload gambar QRIS</strong><p>PNG, JPG, atau WEBP · Maksimal 4 MB</p><input id="qris-image" type="file" name="qris_image" accept="image/png,image/jpeg,image/webp" aria-describedby="qris-image-help"></div><small id="qris-image-help" class="ps-help">QRIS lama tetap digunakan untuk order yang sudah dibuat.</small></div>
<div class="ps-note"><i class="fas fa-info-circle" aria-hidden="true"></i><span>Untuk QRIS manual, kasir mengonfirmasi pembayaran setelah dana diterima.</span></div>
</div></section>
<section class="ps-card"><div class="ps-card-heading"><span class="ps-icon ps-icon-neutral"><i class="fas fa-link" aria-hidden="true"></i></span><div><h2>Gateway Midtrans</h2><p>Konfigurasi merchant untuk pembayaran otomatis.</p></div></div><div class="ps-card-body">
<div class="ps-field"><label for="server-key">Server Key toko</label><input class="form-control" id="server-key" type="password" name="midtrans_server_key" autocomplete="new-password" placeholder="Masukkan Server Key" aria-describedby="server-key-help"><small id="server-key-help" class="ps-help">Kosongkan untuk mempertahankan key tersimpan. Gunakan merchant toko sendiri.</small></div>
<label class="ps-production" for="ps-production"><span><strong>Mode produksi</strong><small>Aktifkan untuk transaksi Midtrans production.</small></span><input type="hidden" name="midtrans_production" value="0"><input id="ps-production" type="checkbox" name="midtrans_production" value="1" @checked(old('midtrans_production',$settings->midtrans_production))><span class="ps-switch" aria-hidden="true"></span></label>
<fieldset class="ps-banks"><legend>Bank Virtual Account</legend><p>Pilih bank yang sudah aktif di akun merchant Midtrans toko.</p><div class="ps-bank-grid">@foreach(\App\Services\CustomerPaymentMethods::BANKS as $key=>$label)<label for="bank-{{ $key }}"><input id="bank-{{ $key }}" type="checkbox" name="va_banks[]" value="{{ $key }}" @checked(in_array($key,old('va_banks',$settings->va_banks??[])))><span>{{ $label }}</span></label>@endforeach</div></fieldset>
</div></section>
</div>
<section class="ps-card ps-webhook"><span class="ps-icon ps-icon-neutral"><i class="fas fa-satellite-dish" aria-hidden="true"></i></span><div><h2>URL notifikasi pembayaran</h2><p>Gunakan URL ini pada dashboard merchant Midtrans toko.</p><code>{{ route('customer-payment.webhook',$business->id) }}</code></div></section>
<div class="ps-footer"><span>Pengaturan berlaku untuk {{ $business->name }}.</span><button type="submit" class="ps-save"><i class="fas fa-check" aria-hidden="true"></i> Simpan Pengaturan Pembayaran</button></div>
</form></div>
@endsection
