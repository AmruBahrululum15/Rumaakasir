@extends('layouts.app')
@section('title', 'Detail Pengajuan Toko')
@section('description', 'Periksa informasi toko, pemilik, dan paket sebelum mengambil keputusan.')
@section('content')
<section class="content"><div class="container-fluid">
<a href="{{ route('super-admin.store-registrations.index') }}" class="btn btn-outline-secondary mb-3">Kembali ke Pengajuan Toko</a>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@foreach(['Informasi Toko'=>['store_name'=>'Nama Toko','legal_name'=>'Nama Pemilik','business_type'=>'Jenis Usaha','business_type_other'=>'Jenis Lainnya','store_phone'=>'Telepon','store_email'=>'Email Toko','address'=>'Alamat','province'=>'Provinsi','city'=>'Kota / Kabupaten','district'=>'Kecamatan','village'=>'Kelurahan / Desa','postal_code'=>'Kode Pos','website'=>'Website','social_media'=>'Social Media'], 'Informasi Pemilik'=>['owner_name'=>'Nama','owner_username'=>'Username','owner_email'=>'Email','owner_phone'=>'Nomor HP','owner_position'=>'Jabatan'], 'Paket & Status'=>['requested_plan'=>'Paket Diminta','billing_cycle'=>'Periode','status'=>'Status','rejection_reason'=>'Alasan Penolakan','created_at'=>'Tanggal Pengajuan','reviewed_at'=>'Tanggal Review','reviewed_by'=>'ID Reviewer']] as $heading=>$fields)
<div class="card"><div class="card-header"><h2 class="h5 mb-0">{{ $heading }}</h2></div><div class="card-body"><dl class="row mb-0">@foreach($fields as $key=>$label)<dt class="col-sm-3">{{ $label }}</dt><dd class="col-sm-9" style="overflow-wrap:anywhere">{{ $key === 'business_type' ? $registration->businessTypeLabel() : ($registration->$key ?: '-') }}</dd>@endforeach</dl></div></div>
@endforeach
<p class="alert alert-light">Alamat wilayah: {{ $registration->address_verified ? 'Sesuai referensi wilayah dan relasi API.' : 'Input manual / data lama — periksa alamat saat review.' }}</p>
@include('partials.business-module-profile', ['businessType' => $registration->business_type, 'moduleBusiness' => null, 'requestedPlan' => $registration->requested_plan])
@if($registration->status === 'pending')<div class="card"><div class="card-body">
<p>Approval membuat toko baru dan Atasan Utama. Pemohon Premium menggunakan Free sampai pembayaran berhasil.</p>
<form method="POST" action="{{ route('super-admin.store-registrations.approve', $registration) }}" class="mb-4">@csrf<button class="btn btn-success">Approve Pengajuan</button></form>
<form method="POST" action="{{ route('super-admin.store-registrations.reject', $registration) }}">@csrf<label for="rejection_reason">Alasan penolakan *</label><textarea id="rejection_reason" name="rejection_reason" class="form-control mb-3" required maxlength="2000">{{ old('rejection_reason') }}</textarea><button class="btn btn-outline-danger">Reject Pengajuan</button></form>
</div></div>@elseif($registration->status === 'approved')
<a class="btn btn-primary" href="{{ route('businesses.show', $registration->business_id) }}">Lihat Toko</a>
@if(!$registration->activated_at)<form class="d-inline" method="POST" action="{{ route('super-admin.store-registrations.resend', $registration) }}">@csrf<button class="btn btn-outline-primary">Kirim Ulang Tautan Aktivasi</button></form>@endif
@endif
</div></section>
@endsection
