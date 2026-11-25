@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content_header')
    <div class="page-header">
        <div>
            <h1>Manajemen User</h1>
            <p>Kelola akun, data karyawan, role dan akses sistem.</p>
        </div>

        <a href="{{ route('users.create') }}" class="btn-add-user">
            <i class="fas fa-plus"></i>
            <span>Tambah User</span>
        </a>
    </div>
@stop

@section('content')

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert-success-custom">
            <div class="alert-icon">
                <i class="fas fa-check"></i>
            </div>
            <div>
                <strong>Berhasil</strong>
                <div>{{ session('success') }}</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="alert-danger-custom">
            <div class="alert-icon">
                <i class="fas fa-exclamation"></i>
            </div>
            <div>
                <strong>Gagal</strong>
                <div>{{ session('error') }}</div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- VALIDATION ERROR --}}
    @if($errors->any())
        <div class="alert-danger-custom">
            <div class="alert-icon">
                <i class="fas fa-exclamation"></i>
            </div>

            <div>
                <strong>Terjadi kesalahan</strong>

                <ul class="error-list">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif


    {{-- FILTER CARD --}}
    <div class="user-card">

        <form action="{{ route('users.index') }}" method="GET">

            <div class="filter-wrapper">

                {{-- SEARCH --}}
                <div class="filter-search">
                    <label for="search">Cari User</label>

                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Nama, username, NIK, email..."
                        >
                    </div>
                </div>


                {{-- ROLE --}}
                <div class="filter-item">
                    <label for="role">Role</label>

                    <select name="role" id="role">

                        <option value="">Semua Role</option>

                        @if(isset($roles))

                            @foreach($roles as $role)
                                <option
                                    value="{{ is_object($role) ? $role->name : $role }}"
                                    {{ request('role') == (is_object($role) ? $role->name : $role) ? 'selected' : '' }}
                                >
                                    {{ is_object($role) ? ucfirst($role->name) : ucfirst($role) }}
                                </option>
                            @endforeach

                        @else

                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>
                                Admin
                            </option>

                            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>
                                Kasir
                            </option>

                        @endif

                    </select>
                </div>


                {{-- STATUS --}}
                <div class="filter-item">
                    <label for="status">Status</label>

                    <select name="status" id="status">

                        <option value="">Semua Status</option>

                        <option
                            value="1"
                            {{ request('status') == '1' ? 'selected' : '' }}
                        >
                            Aktif
                        </option>

                        <option
                            value="0"
                            {{ request('status') === '0' ? 'selected' : '' }}
                        >
                            Nonaktif
                        </option>

                    </select>
                </div>


                {{-- BUTTON --}}
                <div class="filter-buttons">

                    <button type="submit" class="btn-search">
                        <i class="fas fa-search"></i>
                        <span>Cari</span>
                    </button>

                    <a href="{{ route('users.index') }}" class="btn-reset">
                        Reset
                    </a>

                </div>

            </div>

        </form>


        {{-- TABLE --}}
        <div class="table-wrapper">

            <table class="user-table">

                <thead>
                    <tr>

                        <th class="user-column">
                            USER
                        </th>

                        <th>
                            NIK
                        </th>

                        <th>
                            EMAIL
                        </th>

                        <th>
                            NOMOR HP
                        </th>

                        <th>
                            ROLE
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th class="action-column">
                            AKSI
                        </th>

                    </tr>
                </thead>


                <tbody>

                    @forelse($users as $user)

                        <tr>

                            {{-- =========================
                                USER
                            ========================== --}}
                            <td>

                                <div class="user-info">

                                    {{-- FOTO --}}
                                    <div class="user-avatar">

                                        @php
                                            $fotoPath = $user->foto ?? null;

                                            /*
                                             * Menghindari path:
                                             * storage/storage/...
                                             */
                                            if ($fotoPath) {
                                                $fotoPath = str_replace('\\', '/', $fotoPath);

                                                $fotoUrl = route('users.photo', $user->id);
                                            } else {
                                                $fotoUrl = null;
                                            }

                                            $namaUser = $user->name ?? 'User';

                                            $initial = strtoupper(
                                                substr(
                                                    trim($namaUser),
                                                    0,
                                                    1
                                                )
                                            );
                                        @endphp


                                        @if($fotoUrl)

                                            <img
                                                src="{{ $fotoUrl }}"
                                                alt="{{ $namaUser }}"
                                                class="profile-image"
                                                onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';"
                                            >

                                            <div
                                                class="avatar-fallback"
                                                style="display:none;"
                                            >
                                                {{ $initial }}
                                            </div>

                                        @else

                                            <div class="avatar-fallback">
                                                {{ $initial }}
                                            </div>

                                        @endif

                                    </div>


                                    {{-- NAMA + USERNAME --}}
                                    <div class="user-detail">

                                        <div class="user-name">
                                            {{ $user->name ?? '-' }}
                                        </div>

                                        <div class="user-username">

                                            @if(!empty($user->username))

                                                {{ '@' . $user->username }}

                                            @else

                                                Username belum diisi

                                            @endif

                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- =========================
                                NIK
                            ========================== --}}
                            <td>

                                @if(!empty($user->nik))

                                    <span class="nik-text">
                                        {{ $user->nik }}
                                    </span>

                                @else

                                    <span class="empty-data">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- =========================
                                KONTAK
                            ========================== --}}
                            <td>

                                <div class="contact-wrapper">

                                    {{-- EMAIL --}}
                                    @if(!empty($user->email))

                                        <div class="contact-item">

                                            <i class="fas fa-envelope email-icon"></i>

                                            <span>
                                                {{ $user->email }}
                                            </span>

                                        </div>

                                    @endif


                                    @if(empty($user->email))

                                        <span class="empty-data">
                                            -
                                        </span>

                                    @endif

                                </div>

                            </td>


                            {{-- =========================
                                NOMOR HP
                            ========================== --}}
                            <td>

                                @if(!empty($user->no_telepon))

                                    {{ $user->no_telepon }}

                                @else

                                    <span class="empty-data">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- =========================
                                ROLE
                            ========================== --}}
                            <td>

                                @php
                                    $roleName = null;

                                    if (
                                        method_exists($user, 'roles') &&
                                        $user->relationLoaded('roles') &&
                                        $user->roles->count()
                                    ) {
                                        $roleName = $user->roles->first()->name;
                                    }

                                    if (!$roleName && isset($user->role)) {
                                        $roleName = $user->role;
                                    }

                                    if (!$roleName) {
                                        $roleName = 'User';
                                    }
                                @endphp


                                <span class="role-badge">
                                    {{ ucfirst($roleName) }}
                                </span>

                            </td>


                            {{-- =========================
                                STATUS
                            ========================== --}}
                            <td>

                                @php

                                    /*
                                     * Mendukung beberapa kemungkinan
                                     * field status:
                                     *
                                     * status
                                     * is_active
                                     * active
                                     */

                                    if (isset($user->status)) {

                                        $isActive =
                                            $user->status === 1 ||
                                            $user->status === '1' ||
                                            $user->status === true ||
                                            $user->status === 'active';

                                    } elseif (isset($user->is_active)) {

                                        $isActive =
                                            $user->is_active === 1 ||
                                            $user->is_active === '1' ||
                                            $user->is_active === true;

                                    } elseif (isset($user->active)) {

                                        $isActive =
                                            $user->active === 1 ||
                                            $user->active === '1' ||
                                            $user->active === true;

                                    } else {

                                        /*
                                         * Default aktif jika field
                                         * status tidak tersedia.
                                         */
                                        $isActive = true;

                                    }

                                @endphp


                                @if($isActive)

                                    <span class="status-badge status-active">
                                        <span class="status-dot"></span>
                                        Aktif
                                    </span>

                                @else

                                    <span class="status-badge status-inactive">
                                        <span class="status-dot"></span>
                                        Nonaktif
                                    </span>

                                @endif

                            </td>


                            {{-- =========================
                                AKSI
                            ========================== --}}
                            <td>

                                <div class="action-buttons">

                                    {{-- SHOW --}}
                                    <a
                                        href="{{ route('users.show', $user->id) }}"
                                        class="action-btn"
                                        title="Lihat User"
                                    >
                                        <i class="fas fa-eye"></i>
                                    </a>


                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('users.edit', $user->id) }}"
                                        class="action-btn"
                                        title="Edit User"
                                    >
                                        <i class="fas fa-pen"></i>
                                    </a>


                                    {{-- DELETE --}}
                                    @if(auth()->id() != $user->id)

                                        <form
                                            action="{{ route('users.destroy', $user->id) }}"
                                            method="POST"
                                            class="delete-form"
                                            onsubmit="return confirmDelete('{{ addslashes($user->name ?? 'User') }}')"
                                        >

                                            @csrf

                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="action-btn delete-btn"
                                                title="Hapus User"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="empty-table"
                            >

                                <div class="empty-state">

                                    <div class="empty-icon">
                                        <i class="fas fa-users"></i>
                                    </div>

                                    <h4>
                                        Belum ada user
                                    </h4>

                                    <p>
                                        Data user yang tersedia akan muncul di sini.
                                    </p>

                                    <a
                                        href="{{ route('users.create') }}"
                                        class="btn-add-empty"
                                    >
                                        <i class="fas fa-plus"></i>
                                        Tambah User
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PAGINATION --}}
        @if(method_exists($users, 'links'))

            @if($users->hasPages())

                <div class="pagination-wrapper">

                    <div class="pagination-info">

                        Menampilkan

                        <strong>
                            {{ $users->firstItem() ?? 0 }}
                        </strong>

                        sampai

                        <strong>
                            {{ $users->lastItem() ?? 0 }}
                        </strong>

                        dari

                        <strong>
                            {{ $users->total() }}
                        </strong>

                        user

                    </div>

                    <div>
                        {{ $users->withQueryString()->links() }}
                    </div>

                </div>

            @endif

        @endif

    </div>

@stop


@section('css')

<style>

    /* =====================================================
       GLOBAL
    ===================================================== */

    .content-wrapper {
        background: #f5f7fb !important;
    }

    .content {
        padding: 0 16px 30px;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        gap: 20px;
    }

    .page-header h1 {
        margin: 0;
        color: #12233f;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -0.5px;
    }

    .page-header p {
        margin: 5px 0 0;
        color: #7d8da5;
        font-size: 13px;
    }


    /* =====================================================
       ADD USER
    ===================================================== */

    .btn-add-user {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 12px 20px;
        min-height: 46px;
        border-radius: 12px;
        background: #1769e0;
        color: #fff !important;
        text-decoration: none !important;
        font-size: 13px;
        font-weight: 700;
        border: none;
        transition: all .2s ease;
        box-shadow: 0 5px 15px rgba(23, 105, 224, .18);
    }

    .btn-add-user:hover {
        background: #0958c9;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(23, 105, 224, .25);
    }


    /* =====================================================
       CARD
    ===================================================== */

    .user-card {
        background: #fff;
        border: 1px solid #e3e9f2;
        border-radius: 17px;
        overflow: hidden;
        box-shadow: 0 7px 25px rgba(30, 55, 90, .05);
    }


    /* =====================================================
       FILTER
    ===================================================== */

    .filter-wrapper {
        display: grid;
        grid-template-columns: minmax(250px, 1fr) 170px 170px auto;
        gap: 10px;
        padding: 20px;
        align-items: end;
        border-bottom: 1px solid #e8edf4;
        background: #fff;
    }

    .filter-wrapper label {
        display: block;
        margin-bottom: 8px;
        color: #52657d;
        font-size: 11px;
        font-weight: 700;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ba9bc;
        font-size: 13px;
    }

    .search-input-wrapper input,
    .filter-item select {
        width: 100%;
        height: 42px;
        border: 1px solid #d8e1ed;
        border-radius: 10px;
        background: #fff;
        color: #263b57;
        font-size: 13px;
        outline: none;
        transition: .2s;
    }

    .search-input-wrapper input {
        padding: 0 14px 0 40px;
    }

    .filter-item select {
        padding: 0 12px;
    }

    .search-input-wrapper input:focus,
    .filter-item select:focus {
        border-color: #1769e0;
        box-shadow: 0 0 0 3px rgba(23, 105, 224, .08);
    }

    .search-input-wrapper input::placeholder {
        color: #9aa8b9;
    }


    /* =====================================================
       FILTER BUTTONS
    ===================================================== */

    .filter-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-search,
    .btn-reset {
        height: 42px;
        border-radius: 10px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none !important;
        white-space: nowrap;
    }

    .btn-search {
        border: none;
        background: #1769e0;
        color: #fff;
    }

    .btn-search:hover {
        background: #0958c9;
    }

    .btn-reset {
        border: 1px solid #d8e1ed;
        background: #fff;
        color: #52657d !important;
    }

    .btn-reset:hover {
        background: #f6f8fb;
    }


    /* =====================================================
       TABLE
    ===================================================== */

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .user-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1050px;
    }

    .user-table thead th {
        height: 46px;
        padding: 0 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e4eaf2;
        color: #71839b;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .5px;
        text-align: left;
        white-space: nowrap;
    }

    .user-table tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid #edf1f6;
        color: #314761;
        font-size: 11px;
        vertical-align: middle;
    }

    .user-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-table tbody tr:hover {
        background: #fbfcfe;
    }

    .user-column {
        width: 27%;
    }

    .action-column {
        width: 140px;
    }


    /* =====================================================
       USER
    ===================================================== */

    .user-info {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 200px;
    }

    .user-avatar {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 12px;
        overflow: hidden;
        background: #edf4ff;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e1e9f5;
    }

    .profile-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .avatar-fallback {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #edf4ff;
        color: #1769e0;
        font-size: 17px;
        font-weight: 800;
    }

    .user-detail {
        min-width: 0;
    }

    .user-name {
        color: #12233f;
        font-size: 12px;
        font-weight: 800;
        line-height: 1.4;
        white-space: nowrap;
    }

    .user-username {
        margin-top: 3px;
        color: #8796a9;
        font-size: 10px;
        line-height: 1.3;
        white-space: nowrap;
    }


    /* =====================================================
       NIK
    ===================================================== */

    .nik-text {
        color: #344b68;
        font-size: 10px;
        white-space: nowrap;
    }


    /* =====================================================
       CONTACT
    ===================================================== */

    .contact-wrapper {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 170px;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
    }

    .contact-item i {
        width: 13px;
        text-align: center;
        font-size: 10px;
    }

    .phone-icon,
    .email-icon {
        color: #1769e0;
    }


    /* =====================================================
       EMPTY
    ===================================================== */

    .empty-data {
        color: #aeb9c7;
    }


    /* =====================================================
       ROLE
    ===================================================== */

    .role-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 8px;
        background: #eef4ff;
        color: #1769e0;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }


    /* =====================================================
       STATUS
    ===================================================== */

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    .status-active {
        background: #eafaf2;
        color: #079455;
    }

    .status-active .status-dot {
        background: #079455;
    }

    .status-inactive {
        background: #fff0f0;
        color: #d92d20;
    }

    .status-inactive .status-dot {
        background: #d92d20;
    }


    /* =====================================================
       ACTION
    ===================================================== */

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .action-btn {
        width: 34px;
        height: 34px;
        border: 1px solid #dce4ee;
        border-radius: 9px;
        background: #fff;
        color: #425a76 !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        cursor: pointer;
        transition: all .2s ease;
    }

    .action-btn:hover {
        color: #1769e0 !important;
        border-color: #b9d0f3;
        background: #f5f9ff;
    }

    .delete-form {
        margin: 0;
        padding: 0;
    }

    .delete-btn:hover {
        color: #d92d20 !important;
        border-color: #f2c4c1;
        background: #fff6f5;
    }


    /* =====================================================
       EMPTY STATE
    ===================================================== */

    .empty-table {
        padding: 50px !important;
        text-align: center !important;
    }

    .empty-state {
        padding: 20px;
    }

    .empty-icon {
        width: 60px;
        height: 60px;
        margin: 0 auto 15px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #edf4ff;
        color: #1769e0;
        font-size: 22px;
    }

    .empty-state h4 {
        margin: 0 0 6px;
        color: #12233f;
        font-size: 16px;
        font-weight: 800;
    }

    .empty-state p {
        margin: 0 0 18px;
        color: #8b99aa;
        font-size: 12px;
    }

    .btn-add-empty {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 15px;
        border-radius: 9px;
        background: #1769e0;
        color: #fff !important;
        text-decoration: none !important;
        font-size: 12px;
        font-weight: 700;
    }


    /* =====================================================
       ALERT
    ===================================================== */

    .alert-success-custom,
    .alert-danger-custom {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        margin-bottom: 18px;
        border-radius: 12px;
        font-size: 12px;
        position: relative;
    }

    .alert-success-custom {
        background: #ecfdf3;
        border: 1px solid #b7ebce;
        color: #087443;
    }

    .alert-danger-custom {
        background: #fff1f1;
        border: 1px solid #f2c5c5;
        color: #b42318;
    }

    .alert-icon {
        width: 26px;
        height: 26px;
        min-width: 26px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,.7);
    }

    .alert-close {
        margin-left: auto;
        border: none;
        background: transparent;
        color: inherit;
        cursor: pointer;
        opacity: .7;
    }

    .error-list {
        margin: 5px 0 0;
        padding-left: 17px;
    }


    /* =====================================================
       PAGINATION
    ===================================================== */

    .pagination-wrapper {
        padding: 15px 20px;
        border-top: 1px solid #edf1f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }

    .pagination-info {
        color: #8291a5;
        font-size: 11px;
    }

    .pagination-info strong {
        color: #314761;
    }


    /* =====================================================
       RESPONSIVE
    ===================================================== */

    @media(max-width: 1100px) {

        .filter-wrapper {
            grid-template-columns: 1fr 1fr;
        }

        .filter-search {
            grid-column: span 2;
        }

        .filter-buttons {
            justify-content: flex-start;
        }

    }


    @media(max-width: 768px) {

        .content {
            padding: 0 10px 20px;
        }

        .page-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .page-header h1 {
            font-size: 23px;
        }

        .btn-add-user {
            width: 100%;
        }

        .filter-wrapper {
            grid-template-columns: 1fr;
            padding: 15px;
        }

        .filter-search {
            grid-column: auto;
        }

        .filter-buttons {
            width: 100%;
        }

        .btn-search,
        .btn-reset {
            flex: 1;
        }

        .pagination-wrapper {
            align-items: flex-start;
            flex-direction: column;
        }

    }

</style>

@stop


@section('js')

<script>

    function confirmDelete(name) {

        return confirm(
            'Apakah Anda yakin ingin menghapus user "' +
            name +
            '"?\n\nData yang sudah dihapus tidak dapat dikembalikan.'
        );

    }


    // Auto hide alert
    setTimeout(function() {

        document
            .querySelectorAll(
                '.alert-success-custom, .alert-danger-custom'
            )
            .forEach(function(alert) {

                alert.style.transition = 'opacity .4s ease';

                alert.style.opacity = '0';

                setTimeout(function() {
                    alert.remove();
                }, 400);

            });

    }, 5000);

</script>

@stop
