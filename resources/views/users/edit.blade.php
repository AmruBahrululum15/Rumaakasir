@extends('layouts.app')

@section('title', 'Edit User')

@section('content_header')

<div class="d-flex justify-content-between align-items-center">

    <div>

        <h1 class="page-title">
            Edit User
        </h1>

        <p class="page-subtitle">
            Perbarui informasi akun dan data karyawan.
        </p>

    </div>

    <a
        href="{{ route('users.show', $user->id) }}"
        class="btn btn-light btn-back"
    >
        <i class="fas fa-arrow-left mr-2"></i>
        Kembali
    </a>

</div>

@stop


@section('content')

<style>

    body {
        background: #f4f7fb;
    }

    .page-title {
        font-size: 28px;
        font-weight: 700;
        color: #10233f;
        margin-bottom: 4px;
    }

    .page-subtitle {
        color: #718096;
        margin-bottom: 0;
    }

    .btn-back {
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 600;
        background: #fff;
    }

    .form-card {
        background: #fff;
        border: 1px solid #e5eaf1;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(15,23,42,.05);
    }

    .section-header {
        padding: 20px 24px;
        border-bottom: 1px solid #edf0f5;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #2563eb;
    }

    .section-title {
        font-weight: 700;
        color: #10233f;
        margin: 0;
    }

    .section-description {
        font-size: 12px;
        color: #94a3b8;
        margin: 2px 0 0;
    }

    .form-body {
        padding: 24px;
    }

    .form-label {
        font-weight: 600;
        font-size: 13px;
        color: #334155;
    }

    .form-control,
    .custom-select {
        border-radius: 10px;
        border-color: #dbe2ea;
        min-height: 44px;
        box-shadow: none !important;
    }

    .form-control:focus,
    .custom-select:focus {
        border-color: #2563eb;
    }

    textarea.form-control {
        min-height: 110px;
    }

    .photo-box {
        border: 2px dashed #d7e0eb;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        background: #fafcff;
    }

    .photo-preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 20px;
        margin: 0 auto 15px;
        display: block;
        border: 3px solid #fff;
        box-shadow: 0 5px 20px rgba(0,0,0,.1);
    }

    .photo-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 20px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 35px;
    }

    .btn-save {
        border-radius: 10px;
        padding: 11px 22px;
        font-weight: 600;
    }

</style>


@if($errors->any())

    <div class="alert alert-danger border-0 rounded-lg">

        <strong>
            Periksa kembali data berikut:
        </strong>

        <ul class="mb-0 mt-2">

            @foreach($errors->all() as $error)

                <li>
                    {{ $error }}
                </li>

            @endforeach

        </ul>

    </div>

@endif


<form
    action="{{ route('users.update', $user->id) }}"
    method="POST"
    enctype="multipart/form-data"
>

@csrf
@method('PUT')


<div class="row">


    {{-- KOLOM UTAMA --}}
    <div class="col-lg-8">


        {{-- DATA PRIBADI --}}
        <div class="form-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="fas fa-user"></i>
                </div>

                <div>

                    <h5 class="section-title">
                        Informasi Personal
                    </h5>

                    <p class="section-description">
                        Data identitas karyawan.
                    </p>

                </div>

            </div>


            <div class="form-body">

                <div class="row">


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $user->name) }}"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            NIK
                        </label>

                        <input
                            type="text"
                            name="nik"
                            class="form-control"
                            value="{{ old('nik', $user->nik) }}"
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nomor HP
                        </label>

                        <input
                            type="tel"
                            name="no_telepon"
                            class="form-control"
                            value="{{ old('no_telepon', $user->no_telepon) }}"
                            maxlength="20"
                            autocomplete="tel"
                        >

                    </div>


                    <div class="col-12">

                        <label class="form-label">
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            class="form-control"
                            rows="4"
                        >{{ old('alamat', $user->alamat) }}</textarea>

                    </div>

                </div>

            </div>

        </div>


        {{-- INFORMASI AKUN --}}
        <div class="form-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="fas fa-lock"></i>
                </div>

                <div>

                    <h5 class="section-title">
                        Informasi Akun
                    </h5>

                    <p class="section-description">
                        Data login dan akses sistem.
                    </p>

                </div>

            </div>


            <div class="form-body">

                <div class="row">


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Username <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            value="{{ old('username', $user->username) }}"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email', $user->email) }}"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Password Baru
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                        >

                        <small class="text-muted">
                            Kosongkan jika tidak ingin mengubah password.
                        </small>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Konfirmasi Password
                        </label>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-6">

                        <label class="form-label">
                            Role <span class="text-danger">*</span>
                        </label>

                        <select
                            name="role"
                            class="form-control custom-select"
                            required
                        >

                            <option value="">
                                Pilih Role
                            </option>

                            @foreach($roles as $role)

                                <option
                                    value="{{ $role->name }}"
                                    {{ old(
                                        'role',
                                        $user->roles->first()?->name
                                    ) == $role->name ? 'selected' : '' }}
                                >
                                    {{ ucfirst($role->name) }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

            </div>

        </div>


    </div>


    {{-- SIDEBAR --}}
    <div class="col-lg-4">


        {{-- FOTO --}}
        <div class="form-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="fas fa-camera"></i>
                </div>

                <div>

                    <h5 class="section-title">
                        Foto Profil
                    </h5>

                    <p class="section-description">
                        Ganti foto jika diperlukan.
                    </p>

                </div>

            </div>


            <div class="form-body">

                <div class="photo-box">


                    @if($user->foto)

                        <img
                            id="photoPreview"
                            src="{{ asset('storage/' . $user->foto) }}"
                            class="photo-preview"
                            alt="{{ $user->name }}"
                            onerror="showPlaceholder()"
                        >

                        <div
                            id="photoPlaceholder"
                            class="photo-placeholder"
                            style="display:none;"
                        >
                            <i class="fas fa-user"></i>
                        </div>

                    @else

                        <div
                            id="photoPlaceholder"
                            class="photo-placeholder"
                        >
                            <i class="fas fa-user"></i>
                        </div>

                        <img
                            id="photoPreview"
                            class="photo-preview"
                            style="display:none;"
                            alt="Preview"
                        >

                    @endif


                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        class="d-none"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <label
                        for="foto"
                        class="btn btn-primary"
                    >
                        <i class="fas fa-upload mr-1"></i>
                        Pilih Foto Baru
                    </label>


                    <div class="text-muted small mt-2">
                        Kosongkan jika foto tidak ingin diganti.
                    </div>

                </div>

            </div>

        </div>


        {{-- STATUS --}}
        <div class="form-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="fas fa-toggle-on"></i>
                </div>

                <div>

                    <h5 class="section-title">
                        Status User
                    </h5>

                    <p class="section-description">
                        Kontrol akses pengguna.
                    </p>

                </div>

            </div>


            <div class="form-body">

                <div class="custom-control custom-switch">

                    <input
                        type="checkbox"
                        class="custom-control-input"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                    >

                    <label
                        class="custom-control-label"
                        for="is_active"
                    >
                        User Aktif
                    </label>

                </div>

                <small class="text-muted">
                    User aktif dapat login ke sistem.
                </small>

            </div>

        </div>


        {{-- SIMPAN --}}
        <div class="form-card">

            <div class="form-body">

                <button
                    type="submit"
                    class="btn btn-primary btn-save btn-block"
                >
                    <i class="fas fa-save mr-2"></i>
                    Simpan Perubahan
                </button>

            </div>

        </div>


    </div>

</div>

</form>


<script>

function showPlaceholder()
{
    const preview = document.getElementById('photoPreview');
    const placeholder = document.getElementById('photoPlaceholder');

    if (preview) {
        preview.style.display = 'none';
    }

    if (placeholder) {
        placeholder.style.display = 'flex';
    }
}


document.getElementById('foto').addEventListener('change', function(event) {

    const file = event.target.files[0];

    if (!file) {
        return;
    }

    const reader = new FileReader();

    reader.onload = function(e) {

        const preview = document.getElementById('photoPreview');

        const placeholder = document.getElementById('photoPlaceholder');

        preview.src = e.target.result;

        preview.style.display = 'block';

        if (placeholder) {
            placeholder.style.display = 'none';
        }

    };

    reader.readAsDataURL(file);

});

</script>

@stop
