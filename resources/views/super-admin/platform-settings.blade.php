@extends('layouts.app')
@section('title', 'Pengaturan Platform')
@section('description', 'Atur identitas aplikasi SaaS dan kontak dukungan platform.')
@section('content')
<section class="content"><div class="container-fluid">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('super-admin.platform-settings.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <h2 class="h5">Branding Aplikasi</h2><p class="text-muted">Nama dan logo ini tampil pada halaman login dan copyright platform.</p>
            @foreach(['app_name' => ['Nama Platform', 'text']] as $field => [$label, $type])
                <div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $settings->brandingName()) }}" maxlength="{{ $field === 'support_phone' ? 50 : 255 }}" @if($field === 'app_name') required @endif>@error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            @endforeach
            <div class="form-group"><label for="logo">Logo Platform</label>
                <div class="mb-3 p-3 border rounded d-inline-flex align-items-center" style="gap:12px"><img id="platform-logo-preview" src="{{ asset($settings->brandingLogo()) }}" alt="Preview logo platform" style="width:64px;height:64px;object-fit:contain"><span>{{ $settings->brandingName() }}</span></div>
                <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp" class="form-control-file">
                <small class="text-muted">JPG, PNG, atau WEBP. Maksimal 2 MB.</small>@error('logo')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            <hr><h2 class="h5">Kontak Dukungan</h2>
            @foreach(['support_email' => ['Email support', 'email'], 'support_phone' => ['Nomor support', 'tel']] as $field => [$label, $type])
                <div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" class="form-control" value="{{ old($field, $settings->$field) }}" maxlength="{{ $field === 'support_phone' ? 50 : 255 }}">@error($field)<div class="text-danger">{{ $message }}</div>@enderror</div>
            @endforeach
            <button class="btn btn-primary">Simpan Pengaturan Platform</button>
        </form>
    </div></div>
</div></section>
<script>
(() => {
    const input = document.getElementById('logo');
    const preview = document.getElementById('platform-logo-preview');
    const original = preview.src;
    let objectUrl;
    input.addEventListener('change', () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        const file = input.files[0];
        if (file && ['image/png','image/jpeg','image/webp'].includes(file.type) && file.size <= 2097152) {
            objectUrl = URL.createObjectURL(file); preview.src = objectUrl;
        } else { preview.src = original; }
    });
})();
</script>
@endsection
