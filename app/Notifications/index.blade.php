@extends('layouts.app')

@section('title', 'Notifikasi')


{{-- ========================================================= --}}
{{-- CONTENT HEADER --}}
{{-- ========================================================= --}}

@section('content_header')

<div class="notification-page-header">

    <div>
        <div class="notification-eyebrow">
            POS ENTERPRISE
        </div>

        <h2>
            <i class="fas fa-bell mr-2"></i>
            Notifikasi
        </h2>

        <p>
            Pusat informasi dan aktivitas sistem
        </p>
    </div>


    @if($notifications->count() > 0)

        <form
            action="{{ route('notifications.destroy-all') }}"
            method="POST"
            onsubmit="return confirm('Yakin ingin menghapus semua notifikasi?')"
        >

            @csrf

            @method('DELETE')

            <button
                type="submit"
                class="notification-delete-all"
            >

                <i class="fas fa-trash-alt mr-2"></i>

                Hapus Semua

            </button>

        </form>

    @endif

</div>

@stop


{{-- ========================================================= --}}
{{-- CONTENT --}}
{{-- ========================================================= --}}

@section('content')

<div class="notification-container">


    {{-- ===================================================== --}}
    {{-- FLASH MESSAGE --}}
    {{-- ===================================================== --}}

    @if(session('success'))

        <div class="notification-alert notification-alert-success">

            <div class="notification-alert-icon">
                <i class="fas fa-check"></i>
            </div>

            <div>
                <strong>Berhasil</strong>

                <div>
                    {{ session('success') }}
                </div>
            </div>

        </div>

    @endif


    @if(session('error'))

        <div class="notification-alert notification-alert-error">

            <div class="notification-alert-icon">
                <i class="fas fa-exclamation"></i>
            </div>

            <div>
                <strong>Terjadi Kesalahan</strong>

                <div>
                    {{ session('error') }}
                </div>
            </div>

        </div>

    @endif


    {{-- ===================================================== --}}
    {{-- SUMMARY --}}
    {{-- ===================================================== --}}

    <div class="notification-summary">

        <div class="notification-summary-icon">
            <i class="fas fa-bell"></i>
        </div>

        <div class="notification-summary-content">

            <span>
                TOTAL NOTIFIKASI
            </span>

            <strong>
                {{ $notifications->total() }}
            </strong>

        </div>

    </div>


    {{-- ===================================================== --}}
    {{-- NOTIFICATION CARD --}}
    {{-- ===================================================== --}}

    <div class="notification-card">

        <div class="notification-card-header">

            <div>

                <h4>
                    <i class="fas fa-inbox mr-2"></i>
                    Daftar Notifikasi
                </h4>

                <p>
                    Informasi terbaru dari sistem POS Enterprise
                </p>

            </div>


            <span class="notification-count">

                {{ $notifications->total() }}

                Notifikasi

            </span>

        </div>


        <div class="notification-card-body">


            @forelse($notifications as $notification)

                @php

                    $data = $notification->data;

                    if (is_string($data)) {

                        $decoded = json_decode(
                            $data,
                            true
                        );

                        $data = is_array($decoded)
                            ? $decoded
                            : [];

                    }

                    if (!is_array($data)) {
                        $data = [];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DATA NOTIFIKASI
                    |--------------------------------------------------------------------------
                    */

                    $title =
                        $data['title']
                        ?? $data['judul']
                        ?? 'Notifikasi Sistem';


                    $message =
                        $data['message']
                        ?? $data['body']
                        ?? $data['description']
                        ?? $data['pesan']
                        ?? 'Ada informasi baru dari sistem.';


                    $type =
                        strtolower(
                            $data['type']
                            ?? $data['status']
                            ?? 'info'
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | ICON
                    |--------------------------------------------------------------------------
                    */

                    switch ($type) {

                        case 'success':

                            $icon =
                                'fa-check-circle';

                            break;


                        case 'warning':

                            $icon =
                                'fa-exclamation-triangle';

                            break;


                        case 'danger':

                        case 'error':

                            $icon =
                                'fa-times-circle';

                            break;


                        case 'purchase':

                        case 'pembelian':

                            $icon =
                                'fa-shopping-cart';

                            break;


                        case 'sale':

                        case 'sales':

                        case 'penjualan':

                            $icon =
                                'fa-cash-register';

                            break;


                        default:

                            $icon =
                                'fa-bell';

                            break;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CLASS
                    |--------------------------------------------------------------------------
                    */

                    switch ($type) {

                        case 'success':

                            $notificationClass =
                                'notification-success';

                            break;


                        case 'warning':

                            $notificationClass =
                                'notification-warning';

                            break;


                        case 'danger':

                        case 'error':

                            $notificationClass =
                                'notification-danger';

                            break;


                        case 'purchase':

                        case 'pembelian':

                            $notificationClass =
                                'notification-orange';

                            break;


                        case 'sale':

                        case 'sales':

                        case 'penjualan':

                            $notificationClass =
                                'notification-orange';

                            break;


                        default:

                            $notificationClass =
                                'notification-info';

                            break;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | READ STATUS
                    |--------------------------------------------------------------------------
                    */

                    $isUnread =
                        is_null(
                            $notification->read_at
                        );

                @endphp


                <div
                    class="notification-item
                    {{ $isUnread ? 'notification-unread' : '' }}"
                >


                    {{-- ICON --}}

                    <div
                        class="notification-icon {{ $notificationClass }}"
                    >

                        <i class="fas {{ $icon }}"></i>

                    </div>


                    {{-- CONTENT --}}

                    <div class="notification-content">

                        <div class="notification-top">

                            <div>

                                <h5>

                                    {{ $title }}

                                    @if($isUnread)

                                        <span class="notification-new">
                                            BARU
                                        </span>

                                    @endif

                                </h5>

                            </div>


                            <span class="notification-time">

                                <i class="far fa-clock mr-1"></i>

                                {{ $notification->created_at?->diffForHumans() }}

                            </span>

                        </div>


                        <p>
                            {{ $message }}
                        </p>


                        <div class="notification-actions">


                            {{-- MARK AS READ --}}

                            @if($isUnread)

                                <form
                                    action="{{ route('notifications.read', $notification->id) }}"
                                    method="POST"
                                    class="d-inline"
                                >

                                    @csrf

                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="notification-action notification-action-primary"
                                    >

                                        <i class="fas fa-check mr-1"></i>

                                        Tandai Dibaca

                                    </button>

                                </form>

                            @else

                                <span class="notification-read-label">

                                    <i class="fas fa-check-circle mr-1"></i>

                                    Sudah Dibaca

                                </span>

                            @endif


                            {{-- DELETE --}}

                            <form
                                action="{{ route('notifications.destroy', $notification->id) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Hapus notifikasi ini?')"
                            >

                                @csrf

                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="notification-action notification-action-delete"
                                >

                                    <i class="fas fa-trash-alt mr-1"></i>

                                    Hapus

                                </button>

                            </form>


                        </div>

                    </div>

                </div>


            @empty


                {{-- EMPTY STATE --}}

                <div class="notification-empty">

                    <div class="notification-empty-icon">

                        <i class="far fa-bell-slash"></i>

                    </div>


                    <h4>
                        Belum Ada Notifikasi
                    </h4>


                    <p>
                        Semua informasi dan aktivitas sistem
                        akan muncul di halaman ini.
                    </p>


                    <a
                        href="{{ route('dashboard') }}"
                        class="notification-back-dashboard"
                    >

                        <i class="fas fa-home mr-2"></i>

                        Kembali ke Dashboard

                    </a>

                </div>

            @endforelse


        </div>


        {{-- ================================================= --}}
        {{-- PAGINATION --}}
        {{-- ================================================= --}}

        @if($notifications->hasPages())

            <div class="notification-pagination">

                <div class="notification-pagination-info">

                    Menampilkan

                    <strong>
                        {{ $notifications->firstItem() }}
                    </strong>

                    sampai

                    <strong>
                        {{ $notifications->lastItem() }}
                    </strong>

                    dari

                    <strong>
                        {{ $notifications->total() }}
                    </strong>

                    notifikasi

                </div>


                <div class="notification-pagination-links">

                    {{ $notifications->links() }}

                </div>

            </div>

        @endif

    </div>

</div>

@stop


{{-- ========================================================= --}}
{{-- CSS --}}
{{-- ========================================================= --}}

@section('css')

<style>

/* =========================================================
   PAGE
========================================================= */

.notification-page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 22px;

}


.notification-eyebrow {

    font-size: 12px;

    font-weight: 700;

    letter-spacing: 1.2px;

    color: #f36b0a;

    margin-bottom: 4px;

}


.notification-page-header h2 {

    margin: 0;

    color: #172033;

    font-size: 32px;

    font-weight: 700;

}


.notification-page-header h2 i {

    color: #ff7a18;

}


.notification-page-header p {

    margin: 5px 0 0;

    color: #7d8494;

    font-size: 15px;

}


/* =========================================================
   DELETE ALL
========================================================= */

.notification-delete-all {

    border: 0;

    background: #fff1e7;

    color: #f36b0a;

    padding: 11px 18px;

    border-radius: 10px;

    font-weight: 600;

    cursor: pointer;

    transition: all .2s ease;

}


.notification-delete-all:hover {

    background: #ffe3cf;

    transform: translateY(-1px);

}


/* =========================================================
   CONTAINER
========================================================= */

.notification-container {

    width: 100%;

}


/* =========================================================
   ALERT
========================================================= */

.notification-alert {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 14px 18px;

    margin-bottom: 18px;

    border-radius: 12px;

    border: 1px solid;

}


.notification-alert-success {

    background: #f0fbf4;

    border-color: #ccebd7;

    color: #217a42;

}


.notification-alert-error {

    background: #fff2f2;

    border-color: #f3cccc;

    color: #a52a2a;

}


.notification-alert-icon {

    width: 34px;

    height: 34px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: rgba(255,255,255,.7);

}


/* =========================================================
   SUMMARY
========================================================= */

.notification-summary {

    display: flex;

    align-items: center;

    gap: 15px;

    background: #fffaf4;

    border: 1px solid #eee3d8;

    border-radius: 14px;

    padding: 18px 22px;

    margin-bottom: 18px;

    box-shadow: 0 3px 14px rgba(30,40,60,.04);

}


.notification-summary-icon {

    width: 48px;

    height: 48px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    background: #fff0df;

    color: #ff7a18;

    font-size: 20px;

}


.notification-summary-content {

    display: flex;

    flex-direction: column;

}


.notification-summary-content span {

    color: #8b8f9a;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: .8px;

}


.notification-summary-content strong {

    color: #172033;

    font-size: 22px;

    line-height: 1.2;

}


/* =========================================================
   MAIN CARD
========================================================= */

.notification-card {

    background: #fff;

    border: 1px solid #e9e5df;

    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 5px 20px rgba(30,40,60,.05);

}


/* =========================================================
   CARD HEADER
========================================================= */

.notification-card-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 20px 24px;

    border-bottom: 1px solid #eeeae5;

}


.notification-card-header h4 {

    margin: 0;

    color: #172033;

    font-size: 19px;

    font-weight: 700;

}


.notification-card-header h4 i {

    color: #ff7a18;

}


.notification-card-header p {

    margin: 5px 0 0;

    color: #9297a2;

    font-size: 13px;

}


.notification-count {

    background: #fff1e7;

    color: #f36b0a;

    border-radius: 8px;

    padding: 8px 12px;

    font-size: 12px;

    font-weight: 700;

}


/* =========================================================
   BODY
========================================================= */

.notification-card-body {

    padding: 0;

}


/* =========================================================
   ITEM
========================================================= */

.notification-item {

    display: flex;

    gap: 16px;

    padding: 20px 24px;

    border-bottom: 1px solid #f0ece7;

    transition: background .2s ease;

}


.notification-item:hover {

    background: #fffcf8;

}


.notification-item:last-child {

    border-bottom: 0;

}


.notification-unread {

    background: #fffaf4;

}


/* =========================================================
   ICON
========================================================= */

.notification-icon {

    width: 46px;

    height: 46px;

    min-width: 46px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 12px;

    font-size: 18px;

}


.notification-info {

    background: #eef6ff;

    color: #3d7fc4;

}


.notification-success {

    background: #edf9f1;

    color: #29934c;

}


.notification-warning {

    background: #fff8e8;

    color: #c58918;

}


.notification-danger {

    background: #fff0f0;

    color: #d74b4b;

}


.notification-orange {

    background: #fff1e7;

    color: #ff7a18;

}


/* =========================================================
   CONTENT
========================================================= */

.notification-content {

    flex: 1;

    min-width: 0;

}


.notification-top {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 15px;

}


.notification-top h5 {

    margin: 0;

    color: #172033;

    font-size: 15px;

    font-weight: 700;

}


.notification-content p {

    margin: 7px 0 10px;

    color: #697180;

    font-size: 14px;

    line-height: 1.6;

}


.notification-time {

    color: #a0a5ae;

    font-size: 12px;

    white-space: nowrap;

}


/* =========================================================
   NEW LABEL
========================================================= */

.notification-new {

    display: inline-block;

    margin-left: 7px;

    padding: 3px 7px;

    border-radius: 5px;

    background: #ff7a18;

    color: white;

    font-size: 9px;

    font-weight: 700;

    vertical-align: middle;

}


/* =========================================================
   ACTIONS
========================================================= */

.notification-actions {

    display: flex;

    align-items: center;

    gap: 8px;

}


.notification-action {

    border: 0;

    background: transparent;

    padding: 5px 0;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;

}


.notification-action-primary {

    color: #ff7a18;

}


.notification-action-primary:hover {

    color: #f36b0a;

}


.notification-action-delete {

    color: #9b9fa7;

}


.notification-action-delete:hover {

    color: #d74b4b;

}


.notification-read-label {

    color: #29934c;

    font-size: 12px;

    font-weight: 600;

}


/* =========================================================
   EMPTY
========================================================= */

.notification-empty {

    text-align: center;

    padding: 70px 20px;

}


.notification-empty-icon {

    width: 74px;

    height: 74px;

    margin: 0 auto 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 20px;

    background: #fff5ec;

    color: #ffb36d;

    font-size: 30px;

}


.notification-empty h4 {

    margin-bottom: 8px;

    color: #172033;

    font-size: 18px;

    font-weight: 700;

}


.notification-empty p {

    max-width: 420px;

    margin: 0 auto 20px;

    color: #8a909b;

    font-size: 13px;

    line-height: 1.6;

}


.notification-back-dashboard {

    display: inline-flex;

    align-items: center;

    padding: 10px 16px;

    background: #ff7a18;

    color: #fff !important;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none !important;

    transition: all .2s ease;

}


.notification-back-dashboard:hover {

    background: #f36b0a;

    transform: translateY(-1px);

}


/* =========================================================
   PAGINATION
========================================================= */

.notification-pagination {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 18px 24px;

    border-top: 1px solid #eeeae5;

    background: #fffdfa;

}


.notification-pagination-info {

    color: #8b909a;

    font-size: 12px;

}


.notification-pagination-info strong {

    color: #172033;

}


.notification-pagination-links .pagination {

    margin: 0;

}


.notification-pagination-links .page-link {

    color: #6d7480;

    border-color: #e5e1dc;

    border-radius: 7px;

    margin-left: 4px;

}


.notification-pagination-links .page-item.active .page-link {

    background: #ff7a18;

    border-color: #ff7a18;

    color: #fff;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .notification-page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .notification-page-header h2 {

        font-size: 26px;

    }


    .notification-card-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

    }


    .notification-item {

        padding: 17px;

    }


    .notification-top {

        flex-direction: column;

        gap: 5px;

    }


    .notification-pagination {

        flex-direction: column;

        align-items: flex-start;

        gap: 12px;

    }

}

</style>

@stop