@extends('layouts.app')

@section('title', 'Detail User')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="font-weight-bold mb-1">Detail User</h1>
            <p class="text-muted mb-0">
                Informasi lengkap akun dan data karyawan.
            </p>
        </div>
    </div>
@stop

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DATA USER
    |--------------------------------------------------------------------------
    */

    $nama = $user->name ?? 'User';

    $username = !empty($user->username)
        ? $user->username
        : '-';

    $email = !empty($user->email)
        ? $user->email
        : '-';

    $nik = !empty($user->nik)
        ? $user->nik
        : '-';

    $noTelepon = !empty($user->no_telepon)
        ? $user->no_telepon
        : '-';

    $alamat = !empty($user->alamat)
        ? $user->alamat
        : '-';

    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */

    $role = '-';

    if (isset($user->roles) && $user->roles->count() > 0) {
        $role = $user->roles->first()->name;
    } elseif (!empty($user->role)) {
        $role = $user->role;
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    $statusAktif = true;

    if (isset($user->is_active)) {
        $statusAktif = (bool) $user->is_active;
    } elseif (isset($user->status)) {
        $statusAktif = in_array(
            strtolower((string) $user->status),
            ['1', 'aktif', 'active', 'true']
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FOTO
    |--------------------------------------------------------------------------
    */

    $fotoUrl = null;

    if (!empty($user->foto)) {
        $fotoPath = public_path('storage/' . $user->foto);

        if (file_exists($fotoPath)) {
            $fotoUrl = asset('storage/' . $user->foto);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | INISIAL
    |--------------------------------------------------------------------------
    */

    $initial = strtoupper(substr(trim($nama), 0, 1));
@endphp


<style>
    .user-detail-page {
        padding-bottom: 30px;
    }

    .user-detail-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.06);
    }

    .user-hero {
        padding: 32px;
        background: linear-gradient(
            135deg,
            #f5f9ff 0%,
            #ffffff 100%
        );
        border-bottom: 1px solid #edf0f4;
    }

    .user-hero-content {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .user-photo {
        width: 112px;
        height: 112px;
        min-width: 112px;
        border-radius: 22px;
        overflow: hidden;
        background: #2563eb;
        border: 4px solid #fff;
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.22);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .user-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .user-initial {
        color: #fff;
        font-size: 48px;
        font-weight: 700;
    }

    .user-name {
        font-size: 26px;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 5px 0;
    }

    .user-username {
        font-size: 15px;
        color: #64748b;
        margin-bottom: 12px;
    }

    .badge-custom {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 12px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 600;
        margin-right: 6px;
    }

    .badge-role {
        background: #eff6ff;
        color: #2563eb;
    }

    .badge-active {
        background: #ecfdf5;
        color: #059669;
    }

    .badge-inactive {
        background: #fef2f2;
        color: #dc2626;
    }

    .detail-section {
        padding: 30px 32px;
        border-bottom: 1px solid #edf0f4;
    }

    .detail-section:last-child {
        border-bottom: 0;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 28px;
    }

    .section-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #eff6ff;
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .detail-item {
        margin-bottom: 24px;
    }

    .detail-item:last-child {
        margin-bottom: 0;
    }

    .detail-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        margin-bottom: 8px;
    }

    .detail-value {
        font-size: 14px;
        color: #0f172a;
        font-weight: 500;
        word-break: break-word;
    }

    .detail-value.empty {
        color: #cbd5e1;
    }

    .address-box {
        background: #f8fafc;
        border-radius: 12px;
        padding: 15px 17px;
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
    }

    .contact-value {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .contact-value i {
        color: #2563eb;
        width: 16px;
    }

    .status-active {
        color: #059669;
        font-weight: 600;
    }

    .status-inactive {
        color: #dc2626;
        font-weight: 600;
    }

    .user-footer {
        padding: 20px 32px;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 10px;
        background: #fff;
    }

    .btn-back {
        border: 1px solid #dbe1e8;
        background: #fff;
        color: #334155;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 600;
        text-decoration: none !important;
    }

    .btn-back:hover {
        background: #f8fafc;
        color: #0f172a;
    }

    .btn-edit {
        border: 0;
        background: #2563eb;
        color: #fff !important;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 600;
        text-decoration: none !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
    }

    .btn-edit:hover {
        background: #1d4ed8;
    }

    @media (max-width: 768px) {

        .user-hero,
        .detail-section {
            padding: 22px;
        }

        .user-hero-content {
            align-items: flex-start;
        }

        .user-photo {
            width: 85px;
            height: 85px;
            min-width: 85px;
            border-radius: 18px;
        }

        .user-name {
            font-size: 21px;
        }

        .user-footer {
            padding: 18px 22px;
            flex-direction: column;
            align-items: stretch;
        }

        .btn-back,
        .btn-edit {
            text-align: center;
        }
    }
</style>


<div class="user-detail-page">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="font-weight-bold mb-1">
                Detail User
            </h2>

            <div class="text-muted">
                Informasi lengkap akun dan data karyawan.
            </div>
        </div>

    </div>


    {{-- CARD --}}
    <div class="user-detail-card">

        {{-- =========================================================
             HERO USER
        ========================================================== --}}
        <div class="user-hero">

            <div class="user-hero-content">

                {{-- FOTO --}}
                <div class="user-photo">

                    @if($fotoUrl)
                        <img
                            src="{{ $fotoUrl }}"
                            alt="{{ $nama }}"
                        >
                    @else
                        <span class="user-initial">
                            {{ $initial }}
                        </span>
                    @endif

                </div>


                {{-- IDENTITAS --}}
                <div>

                    <h3 class="user-name">
                        {{ $nama }}
                    </h3>

                    {{-- INI YANG DIPERBAIKI --}}
                    <div class="user-username">
                        @if(!empty($user->username))
                            {{ '@' . $user->username }}
                        @else
                            Username belum diisi
                        @endif
                    </div>


                    {{-- ROLE --}}
                    <div>

                        <span class="badge-custom badge-role">
                            <i class="fas fa-shield-alt"></i>
                            {{ ucfirst($role) }}
                        </span>


                        {{-- STATUS --}}
                        @if($statusAktif)

                            <span class="badge-custom badge-active">
                                <i class="fas fa-circle"></i>
                                Aktif
                            </span>

                        @else

                            <span class="badge-custom badge-inactive">
                                <i class="fas fa-circle"></i>
                                Nonaktif
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             INFORMASI PERSONAL
        ========================================================== --}}
        <div class="detail-section">

            <div class="section-title">

                <span class="section-icon">
                    <i class="fas fa-id-card"></i>
                </span>

                Informasi Personal

            </div>


            <div class="row">

                {{-- NIK --}}
                <div class="col-md-6">

                    <div class="detail-item">

                        <span class="detail-label">
                            NIK
                        </span>

                        <div class="detail-value {{ $nik === '-' ? 'empty' : '' }}">
                            {{ $nik }}
                        </div>

                    </div>

                </div>


                {{-- TELEPON --}}
                <div class="col-md-6">

                    <div class="detail-item">

                        <span class="detail-label">
                            Nomor Telepon
                        </span>

                        <div class="detail-value contact-value {{ $noTelepon === '-' ? 'empty' : '' }}">

                            @if($noTelepon !== '-')
                                <i class="fas fa-phone-alt"></i>
                            @endif

                            {{ $noTelepon }}

                        </div>

                    </div>

                </div>


                {{-- EMAIL --}}
                <div class="col-md-6">

                    <div class="detail-item">

                        <span class="detail-label">
                            Email
                        </span>

                        <div class="detail-value contact-value">

                            <i class="fas fa-envelope"></i>

                            {{ $email }}

                        </div>

                    </div>

                </div>


                {{-- ALAMAT --}}
                <div class="col-12">

                    <div class="detail-item mb-0">

                        <span class="detail-label">
                            Alamat
                        </span>

                        <div class="address-box">

                            @if($alamat !== '-')
                                <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                            @endif

                            {{ $alamat }}

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             INFORMASI AKUN
        ========================================================== --}}
        <div class="detail-section">

            <div class="section-title">

                <span class="section-icon">
                    <i class="fas fa-user-cog"></i>
                </span>

                Informasi Akun

            </div>


            <div class="row">

                {{-- USERNAME --}}
                <div class="col-md-6">

                    <div class="detail-item">

                        <span class="detail-label">
                            Username
                        </span>

                        <div class="detail-value {{ $username === '-' ? 'empty' : '' }}">

                            @if($username !== '-')
                                {{ $username }}
                            @else
                                Username belum diisi
                            @endif

                        </div>

                    </div>

                </div>


                {{-- EMAIL --}}
                <div class="col-md-6">

                    <div class="detail-item">

                        <span class="detail-label">
                            Email
                        </span>

                        <div class="detail-value">
                            {{ $email }}
                        </div>

                    </div>

                </div>


                {{-- ROLE --}}
                <div class="col-md-6">

                    <div class="detail-item mb-0">

                        <span class="detail-label">
                            Role
                        </span>

                        <div class="detail-value">
                            {{ ucfirst($role) }}
                        </div>

                    </div>

                </div>


                {{-- STATUS --}}
                <div class="col-md-6">

                    <div class="detail-item mb-0">

                        <span class="detail-label">
                            Status Akun
                        </span>

                        @if($statusAktif)

                            <div class="detail-value status-active">
                                <i class="fas fa-check-circle mr-1"></i>
                                Aktif
                            </div>

                        @else

                            <div class="detail-value status-inactive">
                                <i class="fas fa-times-circle mr-1"></i>
                                Nonaktif
                            </div>

                        @endif

                    </div>

                </div>

            </div>

        </div>


        {{-- =========================================================
             FOOTER
        ========================================================== --}}
        <div class="user-footer">

            {{-- HANYA SATU BUTTON KEMBALI --}}
            <a
                href="{{ route('users.index') }}"
                class="btn-back"
            >
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>


            <a
                href="{{ route('users.edit', $user->id) }}"
                class="btn-edit"
            >
                <i class="fas fa-pen mr-2"></i>
                Edit User
            </a>

        </div>

    </div>

</div>

@stop
