@extends('layouts.app')
@section('title', 'Detail Toko')
@section('description', 'Informasi tenant dan status langganannya.')
@section('content')
<section class="content"><div class="container-fluid"><div class="card"><div class="card-body">
    <h2 class="h5 mb-4">{{ $business->name }}</h2>
    <dl class="row">
        @foreach(['Jenis Usaha' => app(\App\Services\BusinessModules::class)->label($business->business_type), 'Atasan' => $business->owner?->name, 'Telepon' => $business->phone, 'Alamat' => $business->address, 'Paket' => ucfirst($business->subscription_plan), 'Status toko' => $business->is_active ? 'Aktif' : 'Nonaktif', 'Status langganan' => ucfirst($business->subscription_status), 'Berakhir' => $business->subscription_ends_at?->format('d M Y'), 'Dibuat' => $business->created_at?->format('d M Y')] as $label => $value)
            <dt class="col-sm-4">{{ $label }}</dt><dd class="col-sm-8">{{ $value ?: '-' }}</dd>
        @endforeach
    </dl>
    @include('partials.business-module-profile', ['businessType' => $business->business_type, 'moduleBusiness' => $business])
    <a href="{{ route('businesses.edit', $business) }}" class="btn btn-primary">Edit Toko</a>
    <a href="{{ route('businesses.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div></div></div></section>
@endsection
