@extends('layouts.app')
@section('title', 'Fitur & Paket')
@section('content')
<div class="container-fluid py-3">
    <h1 class="h3 mb-3 sr-only">Fitur & Paket</h1>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <p class="text-muted">Batas kosong berarti unlimited. Perubahan berlaku untuk seluruh toko pada paket ini tanpa menghapus data existing.</p>
    <details class="card"><summary class="card-header">Paket global / Legacy</summary><div class="row p-3">
        @foreach($packages as $package)
        @php($failedForm = (string) old('_package_id') === (string) $package->id)
        <div class="col-lg-6"><div class="card card-primary">
            <div class="card-header"><h2 class="card-title">{{ $package->name }}</h2></div>
            <form method="POST" action="{{ route('packages.update', $package) }}">
                @csrf @method('PUT')
                <input type="hidden" name="_package_id" value="{{ $package->id }}">
                <div class="card-body">
                    <div class="form-group"><label for="name-{{ $package->id }}">Nama paket</label><input id="name-{{ $package->id }}" class="form-control" name="name" required maxlength="100" value="{{ $failedForm ? old('name') : $package->name }}"></div>
                    @if($package->code === 'premium')
                        @foreach(['price_monthly' => 'Harga bulanan (Rp)', 'price_yearly' => 'Harga tahunan (Rp)'] as $key => $label)
                            <div class="form-group"><label for="{{ $key }}-{{ $package->id }}">{{ $label }}</label><input id="{{ $key }}-{{ $package->id }}" class="form-control" type="number" min="1" max="999999999999" required name="{{ $key }}" value="{{ $failedForm ? old($key) : $package->$key }}"></div>
                        @endforeach
                        <div class="form-check mb-3"><input type="hidden" name="is_active" value="0"><input id="is-active-{{ $package->id }}" class="form-check-input" type="checkbox" name="is_active" value="1" @checked($failedForm ? old('is_active') : $package->is_active)><label class="form-check-label" for="is-active-{{ $package->id }}">Tersedia untuk pembelian baru</label></div>
                    @endif
                    @foreach(['max_products' => 'Batas produk', 'max_users' => 'Batas pengguna (termasuk Atasan)', 'max_tables' => 'Batas meja'] as $key => $label)
                        <div class="form-group"><label for="{{ $key }}-{{ $package->id }}">{{ $label }}</label><input id="{{ $key }}-{{ $package->id }}" class="form-control" type="number" min="1" max="1000000" name="{{ $key }}" value="{{ $failedForm ? old($key) : $package->$key }}" placeholder="Unlimited"></div>
                    @endforeach
                    @foreach(['export_excel' => 'Export Excel', 'export_pdf' => 'Export PDF', 'custom_branding' => 'Custom Branding', 'ai_agent' => 'AI Agent', 'qr_order' => 'Meja & QR Order'] as $key => $label)
                        <div class="form-check mb-2"><input type="hidden" name="{{ $key }}" value="0"><input id="{{ $key }}-{{ $package->id }}" class="form-check-input" type="checkbox" name="{{ $key }}" value="1" @checked($failedForm ? old($key) : $package->$key)><label class="form-check-label" for="{{ $key }}-{{ $package->id }}">{{ $label }}</label></div>
                    @endforeach
                </div>
                <div class="card-footer"><button class="btn btn-primary">Simpan {{ $package->name }}</button></div>
            </form>
        </div></div>
        @endforeach
    </div></details>
    @include('packages.variants')
</div>
@endsection
