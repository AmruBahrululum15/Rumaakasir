@extends('layouts.app')

@section('content')

<style>

    /* ============================================================
       DESIGN TOKENS
    ============================================================ */

    .tables-page {
        --tp-bg: #f6f7fb;
        --tp-surface: #ffffff;
        --tp-border: #e7e9f0;
        --tp-border-soft: #eef0f5;
        --tp-ink: #14161f;
        --tp-ink-soft: #6b7280;
        --tp-ink-faint: #9aa1b1;
        --tp-accent: #6d5ef7;
        --tp-accent-dark: #5a49e6;
        --tp-accent-soft: #f1effe;
        --tp-success: #16a34a;
        --tp-success-soft: #eafbf1;
        --tp-danger: #e0413a;
        --tp-danger-soft: #fdecec;
        --tp-warning: #b7791f;
        --tp-warning-soft: #fef7e6;
        --tp-radius-lg: 20px;
        --tp-radius-md: 14px;
        --tp-radius-sm: 10px;
        --tp-shadow-xs: 0 1px 2px rgba(20, 22, 31, .04);
        --tp-shadow-sm: 0 6px 18px rgba(20, 22, 31, .05);
        --tp-shadow-md: 0 14px 34px rgba(20, 22, 31, .08);
    }


    /* ============================================================
       PAGE
    ============================================================ */

    .tables-page {
        background: var(--tp-bg);
        background-image:
            radial-gradient(circle at 0% 0%, rgba(109, 94, 247, .06), transparent 45%),
            radial-gradient(circle at 100% 0%, rgba(109, 94, 247, .04), transparent 40%);

        min-height: calc(100vh - 70px);

        padding: 32px;

        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;

        color: var(--tp-ink);
    }


    .tables-container {
        max-width: 1500px;

        margin: 0 auto;
    }


    /* ============================================================
       HEADER
    ============================================================ */

    .page-header {
        display: flex;

        justify-content: space-between;

        align-items: flex-end;

        gap: 25px;

        margin-bottom: 28px;
    }


    .page-header-left {
        min-width: 0;
    }


    .page-kicker {
        display: inline-flex;

        align-items: center;

        gap: 7px;

        font-size: 11.5px;

        font-weight: 700;

        color: var(--tp-accent-dark);

        text-transform: uppercase;

        letter-spacing: .09em;

        margin-bottom: 10px;

        background: var(--tp-accent-soft);

        padding: 5px 10px 5px 8px;

        border-radius: 999px;
    }


    .page-kicker i {
        font-size: 10px;
    }


    .page-title {
        margin: 0;

        font-size: 30px;

        line-height: 1.15;

        font-weight: 800;

        color: var(--tp-ink);

        letter-spacing: -.6px;
    }


    .page-description {
        margin: 8px 0 0;

        color: var(--tp-ink-soft);

        font-size: 14px;

        line-height: 1.6;

        max-width: 520px;
    }


    /* ============================================================
       HEADER ACTION
       ============================================================ */

    .header-actions {
        display: flex;

        align-items: center;

        justify-content: flex-end;

        gap: 10px;

        flex-wrap: wrap;
    }


    .modern-btn {
        min-height: 42px;

        border-radius: var(--tp-radius-sm);

        padding: 10px 16px;

        font-size: 13px;

        font-weight: 650;

        text-decoration: none;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        transition:
            transform .18s ease,
            box-shadow .18s ease,
            background .18s ease,
            border-color .18s ease;

        cursor: pointer;

        white-space: nowrap;

        border: 1px solid transparent;
    }


    .modern-btn:hover {
        transform: translateY(-1px);

        text-decoration: none;
    }


    .modern-btn:active {
        transform: translateY(0);
    }


    .btn-primary-modern {
        background: linear-gradient(135deg, var(--tp-accent), var(--tp-accent-dark));

        color: #ffffff;

        box-shadow: 0 8px 18px rgba(109, 94, 247, .28);
    }


    .btn-primary-modern:hover {
        color: #ffffff;

        box-shadow: 0 10px 24px rgba(109, 94, 247, .36);
    }


    .btn-secondary-modern {
        background: var(--tp-surface);

        color: #3a3f4d;

        border: 1px solid var(--tp-border);

        box-shadow: var(--tp-shadow-xs);
    }


    .btn-secondary-modern:hover {
        color: var(--tp-ink);

        background: #fbfbfd;

        border-color: #d9dce5;
    }


    /* ============================================================
       ALERT
    ============================================================ */

    .alert-modern {
        border: 1px solid transparent;

        border-radius: var(--tp-radius-md);

        padding: 14px 16px;

        margin-bottom: 20px;

        font-size: 13px;

        line-height: 1.5;

        display: flex;

        align-items: flex-start;

        gap: 10px;

        box-shadow: var(--tp-shadow-xs);
    }


    .alert-modern i {
        margin-top: 1px;
    }


    .alert-success-modern {
        background: var(--tp-success-soft);

        color: #14532d;

        border-color: #c9f0d8;
    }


    .alert-info-modern {
        background: var(--tp-accent-soft);

        color: #3f36b0;

        border-color: #dcd8fb;
    }


    .alert-danger-modern {
        background: var(--tp-danger-soft);

        color: #a1281f;

        border-color: #f8cfcc;
    }


    /* ============================================================
       STATISTICS
    ============================================================ */

    .stats-grid {
        display: grid;

        grid-template-columns:
            repeat(3, minmax(0, 1fr));

        gap: 16px;

        margin-bottom: 24px;
    }


    .stat-card {
        position: relative;

        overflow: hidden;

        background: var(--tp-surface);

        border: 1px solid var(--tp-border-soft);

        border-radius: var(--tp-radius-lg);

        padding: 20px;

        display: flex;

        align-items: center;

        gap: 16px;

        box-shadow: var(--tp-shadow-sm);

        transition: transform .2s ease, box-shadow .2s ease;
    }


    .stat-card:hover {
        transform: translateY(-3px);

        box-shadow: var(--tp-shadow-md);
    }


    .stat-icon {
        position: relative;

        z-index: 1;

        width: 46px;

        height: 46px;

        min-width: 46px;

        border-radius: 13px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 17px;
    }


    .stat-icon-total {
        background: var(--tp-accent-soft);

        color: var(--tp-accent-dark);
    }


    .stat-icon-active {
        background: var(--tp-success-soft);

        color: var(--tp-success);
    }


    .stat-icon-inactive {
        background: var(--tp-danger-soft);

        color: var(--tp-danger);
    }


    .stat-content {
        position: relative;

        z-index: 1;
    }


    .stat-label {
        font-size: 12px;

        color: var(--tp-ink-soft);

        margin-bottom: 5px;

        font-weight: 600;
    }


    .stat-value {
        font-size: 25px;

        line-height: 1;

        font-weight: 800;

        color: var(--tp-ink);

        letter-spacing: -.4px;
    }


    /* ============================================================
       MAIN CARD
    ============================================================ */

    .main-card {
        background: var(--tp-surface);

        border: 1px solid var(--tp-border-soft);

        border-radius: 22px;

        box-shadow: var(--tp-shadow-md);

        overflow: hidden;
    }


    .main-card-header {
        padding: 22px 24px;

        border-bottom: 1px solid var(--tp-border-soft);

        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 15px;

        background: linear-gradient(180deg, #fcfcfe, #ffffff);
    }


    .main-card-title {
        display: flex;

        align-items: center;

        gap: 11px;

        margin: 0;

        font-size: 17px;

        font-weight: 750;

        color: var(--tp-ink);
    }


    .main-card-title-icon {
        width: 34px;

        height: 34px;

        border-radius: 10px;

        display: flex;

        align-items: center;

        justify-content: center;

        background: var(--tp-accent-soft);

        color: var(--tp-accent-dark);

        font-size: 14px;
    }


    .main-card-subtitle {
        font-size: 12.5px;

        color: var(--tp-ink-soft);

        margin-top: 5px;

        padding-left: 45px;
    }


    .today-indicator {
        display: inline-flex;

        align-items: center;

        gap: 7px;

        padding: 8px 12px;

        border-radius: 999px;

        background: var(--tp-surface);

        border: 1px solid var(--tp-border);

        color: #475069;

        font-size: 11.5px;

        font-weight: 650;
    }


    .today-indicator-dot {
        width: 7px;

        height: 7px;

        border-radius: 50%;

        background: var(--tp-success);

        box-shadow: 0 0 0 3px var(--tp-success-soft);
    }


    /* ============================================================
       TABLE GRID
    ============================================================ */

    .tables-grid {
        padding: 24px;

        display: grid;

        grid-template-columns:
            repeat(4, minmax(0, 1fr));

        gap: 18px;
    }


    /* ============================================================
       TABLE CARD
    ============================================================ */

    .table-card {
        position: relative;

        background: var(--tp-surface);

        border: 1px solid var(--tp-border-soft);

        border-radius: var(--tp-radius-lg);

        padding: 16px;

        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease;

        overflow: hidden;
    }


    .table-card:hover {
        transform: translateY(-3px);

        border-color: #dfe2ec;

        box-shadow: var(--tp-shadow-md);
    }


    .table-card.has-order {
        border-color: #ded9fb;
    }


    .table-card.has-order::before {
        content: "";

        position: absolute;

        left: 0;

        top: 0;

        width: 100%;

        height: 3px;

        background: linear-gradient(90deg, var(--tp-accent), #9d8bff);
    }


    /* ============================================================
       TABLE HEADER
    ============================================================ */

    .table-card-header {
        display: flex;

        justify-content: space-between;

        align-items: center;

        gap: 10px;

        margin-bottom: 13px;
    }


    .table-title-wrapper {
        min-width: 0;

        display: flex;

        align-items: center;

        gap: 10px;
    }


    .table-avatar {
        width: 36px;

        height: 36px;

        min-width: 36px;

        border-radius: 10px;

        background: var(--tp-accent-soft);

        color: var(--tp-accent-dark);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 14px;
    }


    .table-number {
        font-size: 15.5px;

        font-weight: 750;

        color: var(--tp-ink);

        line-height: 1.25;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .table-id {
        margin-top: 2px;

        color: var(--tp-ink-faint);

        font-size: 10px;

        font-weight: 600;
    }


    /* ============================================================
       STATUS BADGE
    ============================================================ */

    .status-badge {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 5px 9px;

        border-radius: 999px;

        font-size: 10px;

        font-weight: 700;

        white-space: nowrap;
    }


    .status-badge-dot {
        width: 5px;

        height: 5px;

        border-radius: 50%;
    }


    .status-active {
        background: var(--tp-success-soft);

        color: #157a3c;
    }


    .status-active .status-badge-dot {
        background: var(--tp-success);
    }


    .status-inactive {
        background: var(--tp-danger-soft);

        color: #b8342c;
    }


    .status-inactive .status-badge-dot {
        background: var(--tp-danger);
    }


    /* ============================================================
       QR
    ============================================================ */

    .qr-container {
        width: 100%;

        aspect-ratio: 1 / 1;

        background: #fbfbfd;

        border: 1px dashed #e2e5ee;

        border-radius: var(--tp-radius-md);

        padding: 14px;

        display: flex;

        align-items: center;

        justify-content: center;

        margin-bottom: 13px;

        position: relative;

        overflow: hidden;
    }


    .qr-container img {
        position: relative;

        z-index: 2;

        width: 100%;

        height: 100%;

        max-width: 175px;

        max-height: 175px;

        object-fit: contain;

        display: block;
    }


    .qr-disabled {
        position: relative;

        z-index: 2;

        text-align: center;

        color: var(--tp-ink-faint);

        font-size: 12px;

        font-weight: 600;
    }


    .qr-disabled i {
        display: block;

        font-size: 26px;

        margin-bottom: 8px;

        color: #d3d7e2;
    }


    /* ============================================================
       ORDER SUMMARY
    ============================================================ */

    .order-summary {
        background: #faf9ff;

        border: 1px solid #efedfd;

        border-radius: var(--tp-radius-sm);

        padding: 12px;

        margin-bottom: 13px;
    }


    .order-summary-empty {
        background: #fafafc;

        border-color: var(--tp-border-soft);

        color: var(--tp-ink-faint);

        text-align: center;

        font-size: 11px;

        padding: 13px 8px;
    }


    .order-top {
        display: flex;

        justify-content: space-between;

        align-items: flex-start;

        gap: 10px;

        margin-bottom: 10px;
    }


    .order-count-label {
        color: var(--tp-ink-soft);

        font-size: 10px;

        font-weight: 600;
    }


    .order-count {
        margin-top: 2px;

        color: var(--tp-ink);

        font-size: 14px;

        font-weight: 750;
    }


    .order-total {
        text-align: right;

        color: var(--tp-accent-dark);

        font-size: 14px;

        font-weight: 750;

        white-space: nowrap;
    }


    .order-total-label {
        color: var(--tp-ink-faint);

        font-size: 9px;

        font-weight: 600;

        margin-bottom: 2px;
    }


    .order-status-row {
        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 7px;

        border-top: 1px dashed #e4e1fa;

        padding-top: 9px;
    }


    .order-status {
        display: inline-flex;

        align-items: center;

        gap: 5px;

        padding: 5px 8px;

        border-radius: 7px;

        font-size: 9px;

        font-weight: 700;

        background: var(--tp-warning-soft);

        color: var(--tp-warning);
    }


    .order-status i {
        font-size: 8px;
    }


    .order-time {
        color: var(--tp-ink-faint);

        font-size: 9px;

        white-space: nowrap;
    }


    /* ============================================================
       LAST ORDER
    ============================================================ */

    .last-order {
        padding-top: 10px;

        margin-top: 9px;

        border-top: 1px solid #efedfd;
    }


    .last-order-label {
        color: var(--tp-ink-faint);

        font-size: 9px;

        font-weight: 650;

        margin-bottom: 3px;
    }


    .last-order-code {
        font-size: 10px;

        font-weight: 750;

        color: #3c4257;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    .last-order-customer {
        margin-top: 2px;

        color: var(--tp-ink-soft);

        font-size: 9px;

        overflow: hidden;

        text-overflow: ellipsis;

        white-space: nowrap;
    }


    /* ============================================================
       ACTIONS
    ============================================================ */

    .table-actions {
        display: grid;

        grid-template-columns:
            1fr 1fr;

        gap: 7px;
    }


    .action-btn {
        min-height: 35px;

        border: 0;

        border-radius: 9px;

        padding: 7px 7px;

        font-size: 10px;

        font-weight: 700;

        text-decoration: none;

        display: inline-flex;

        justify-content: center;

        align-items: center;

        gap: 5px;

        cursor: pointer;

        transition:
            transform .16s ease,
            background .16s ease,
            box-shadow .16s ease;

        white-space: nowrap;
    }


    .action-btn:hover {
        text-decoration: none;

        transform: translateY(-1px);
    }


    .action-order {
        background: var(--tp-accent-soft);

        color: var(--tp-accent-dark);
    }


    .action-order:hover {
        background: #e5e1fd;

        color: #443a9e;
    }


    .action-qr {
        background: #eef4ff;

        color: #2b5fc9;
    }


    .action-qr:hover {
        background: #dfeaff;

        color: #234ba3;
    }


    .action-edit {
        background: #f2f3f7;

        color: #3a3f4d;
    }


    .action-edit:hover {
        background: #e7e9f0;

        color: #14161f;
    }


    .action-status {
        background: var(--tp-warning-soft);

        color: var(--tp-warning);
    }


    .action-status:hover {
        background: #fdefc8;

        color: #92600f;
    }


    .action-delete {
        background: var(--tp-danger-soft);

        color: var(--tp-danger);
    }


    .action-delete:hover {
        background: #fbdcda;

        color: #b8342c;
    }


    .action-disabled {
        background: #f2f3f7;

        color: var(--tp-ink-faint);

        cursor: not-allowed;

        pointer-events: none;
    }


    .action-form {
        margin: 0;
    }


    .action-full {
        grid-column: 1 / -1;
    }


    /* ============================================================
       EMPTY STATE
    ============================================================ */

    .empty-state {
        grid-column: 1 / -1;

        text-align: center;

        padding: 80px 20px;
    }


    .empty-icon {
        width: 74px;

        height: 74px;

        border-radius: 20px;

        background: var(--tp-accent-soft);

        margin: 0 auto 18px;

        display: flex;

        align-items: center;

        justify-content: center;

        color: var(--tp-accent-dark);

        font-size: 27px;
    }


    .empty-title {
        font-size: 18px;

        font-weight: 750;

        color: #3a3f4d;
    }


    .empty-text {
        color: var(--tp-ink-faint);

        font-size: 13px;

        margin-top: 6px;

        margin-bottom: 20px;
    }


    /* ============================================================
       PAGINATION
    ============================================================ */

    .pagination-wrapper {
        padding: 18px 24px;

        border-top: 1px solid var(--tp-border-soft);

        display: flex;

        justify-content: center;
    }


    .pagination {
        margin: 0;

        gap: 5px;
    }


    .pagination .page-item .page-link {
        border: 1px solid var(--tp-border);

        border-radius: 8px !important;

        color: #475069;

        background: var(--tp-surface);

        min-width: 35px;

        height: 35px;

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 12px;

        margin: 0;
    }


    .pagination .page-item .page-link:hover {
        background: #fbfbfd;

        color: var(--tp-ink);
    }


    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--tp-accent), var(--tp-accent-dark));

        border-color: var(--tp-accent-dark);

        color: #ffffff;
    }


    .pagination .page-item.disabled .page-link {
        color: #d0d3dd;

        background: #fafafc;
    }


    /* ============================================================
       RESPONSIVE
    ============================================================ */

    @media (max-width: 1350px) {

        .tables-grid {
            grid-template-columns:
                repeat(3, minmax(0, 1fr));
        }

    }


    @media (max-width: 1050px) {

        .tables-page {
            padding: 22px;
        }


        .page-header {
            align-items: flex-start;

            flex-direction: column;
        }


        .header-actions {
            width: 100%;

            justify-content: flex-start;
        }


        .tables-grid {
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
        }

    }


    @media (max-width: 700px) {

        .tables-page {
            padding: 15px;
        }


        .page-title {
            font-size: 24px;
        }


        .page-description {
            font-size: 13px;
        }


        .stats-grid {
            grid-template-columns: 1fr;

            gap: 10px;
        }


        .header-actions {
            display: grid;

            grid-template-columns:
                1fr 1fr;

            width: 100%;
        }


        .modern-btn {
            width: 100%;

            min-height: 40px;

            padding: 9px 10px;

            font-size: 11px;
        }


        .main-card-header {
            align-items: flex-start;

            flex-direction: column;

            padding: 17px;
        }


        .main-card-subtitle {
            padding-left: 0;
        }


        .today-indicator {
            width: 100%;

            justify-content: center;
        }


        .tables-grid {
            grid-template-columns: 1fr;

            padding: 15px;

            gap: 13px;
        }


        .pagination-wrapper {
            overflow-x: auto;

            justify-content: flex-start;
        }

    }


    @media (max-width: 430px) {

        .header-actions {
            grid-template-columns: 1fr;
        }


        .table-actions {
            grid-template-columns:
                1fr 1fr;
        }

    }

</style>


<div class="tables-page">

    <div class="tables-container">


        {{-- ========================================================
             HEADER
        ========================================================= --}}

        <div class="page-header">

            <div class="page-header-left">

                <div class="page-kicker">

                    <i class="fas fa-store"></i>

                    Operasional Restoran

                </div>


                <h1 class="page-title">

                    Manajemen Meja

                </h1>


                <p class="page-description">

                    Kelola meja, QR Code, dan pantau pesanan pelanggan
                    secara real-time dari satu halaman.

                </p>

            </div>


            <div class="header-actions">


                {{-- CETAK SEMUA QR --}}

                <a
                    href="{{ route('tables.printAllQr') }}"
                    target="_blank"
                    class="modern-btn btn-secondary-modern"
                >

                    <i class="fas fa-qrcode"></i>

                    Cetak Semua QR

                </a>


                {{-- ATUR JUMLAH --}}

                <a
                    href="{{ route('tables.create') }}"
                    class="modern-btn btn-secondary-modern"
                >

                    <i class="fas fa-layer-group"></i>

                    Atur Jumlah Meja

                </a>


                {{-- TAMBAH --}}

                <a
                    href="{{ route('tables.create') }}"
                    class="modern-btn btn-primary-modern"
                >

                    <i class="fas fa-plus"></i>

                    Tambah Meja

                </a>

            </div>

        </div>



        {{-- ========================================================
             ALERT SUCCESS
        ========================================================= --}}

        @if(session('success'))

            <div class="alert-modern alert-success-modern">

                <i class="fas fa-check-circle"></i>

                <span>{{ session('success') }}</span>

            </div>

        @endif



        {{-- ========================================================
             ALERT INFO
        ========================================================= --}}

        @if(session('info'))

            <div class="alert-modern alert-info-modern">

                <i class="fas fa-info-circle"></i>

                <span>{{ session('info') }}</span>

            </div>

        @endif



        {{-- ========================================================
             VALIDATION ERROR
        ========================================================= --}}

        @if($errors->any())

            <div class="alert-modern alert-danger-modern">

                <i class="fas fa-triangle-exclamation"></i>

                <div>

                    <strong>

                        Terjadi kesalahan:

                    </strong>


                    <ul style="margin:7px 0 0 18px;">

                        @foreach($errors->all() as $error)

                            <li>

                                {{ $error }}

                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        @endif



        {{-- ========================================================
             STATISTICS
        ========================================================= --}}

        <div class="stats-grid">


            {{-- TOTAL --}}

            <div class="stat-card stat-total">

                <div class="stat-icon stat-icon-total">

                    <i class="fas fa-chair"></i>

                </div>


                <div class="stat-content">

                    <div class="stat-label">

                        Total Meja

                    </div>


                    <div class="stat-value">

                        {{ $totalTables }}

                    </div>

                </div>

            </div>



            {{-- ACTIVE --}}

            <div class="stat-card stat-active">

                <div class="stat-icon stat-icon-active">

                    <i class="fas fa-check"></i>

                </div>


                <div class="stat-content">

                    <div class="stat-label">

                        Meja Aktif

                    </div>


                    <div class="stat-value">

                        {{ $activeTables }}

                    </div>

                </div>

            </div>



            {{-- INACTIVE --}}

            <div class="stat-card stat-inactive">

                <div class="stat-icon stat-icon-inactive">

                    <i class="fas fa-ban"></i>

                </div>


                <div class="stat-content">

                    <div class="stat-label">

                        Meja Nonaktif

                    </div>


                    <div class="stat-value">

                        {{ $inactiveTables }}

                    </div>

                </div>

            </div>

        </div>



        {{-- ========================================================
             MAIN CARD
        ========================================================= --}}

        <div class="main-card">


            {{-- HEADER --}}

            <div class="main-card-header">

                <div>

                    <h2 class="main-card-title">

                        <span class="main-card-title-icon">

                            <i class="fas fa-th-large"></i>

                        </span>

                        Daftar Meja

                    </h2>


                    <div class="main-card-subtitle">

                        Pantau QR Code dan aktivitas pesanan
                        masing-masing meja.

                    </div>

                </div>


                <div class="today-indicator">

                    <span class="today-indicator-dot"></span>

                    Data pesanan hari ini

                </div>

            </div>



            {{-- ====================================================
                 TABLE GRID
            ===================================================== --}}

            <div class="tables-grid">


                @forelse($tables as $table)


                    @php

                        $latestOrder =
                            $table->latest_order ?? null;

                        $hasOrder =
                            $table->has_order ?? false;

                        $orderCount =
                            $table->order_count ?? 0;

                        $totalOrderAmount =
                            $table->total_order_amount ?? 0;

                    @endphp



                    {{-- =================================================
                         TABLE CARD
                    ================================================== --}}

                    <div
                        class="table-card {{ $hasOrder ? 'has-order' : '' }}"
                    >


                        {{-- =================================================
                             TABLE HEADER
                        ================================================== --}}

                        <div class="table-card-header">


                            <div class="table-title-wrapper">

                                <div class="table-avatar">

                                    <i class="fas fa-utensils"></i>

                                </div>


                                <div>

                                    <div class="table-number">

                                        {{ $table->name }}

                                    </div>


                                    <div class="table-id">

                                        ID Meja #{{ $table->id }}

                                    </div>

                                </div>

                            </div>



                            {{-- STATUS MEJA --}}

                            @if($table->is_active)

                                <span class="status-badge status-active">

                                    <span class="status-badge-dot"></span>

                                    Aktif

                                </span>

                            @else

                                <span class="status-badge status-inactive">

                                    <span class="status-badge-dot"></span>

                                    Nonaktif

                                </span>

                            @endif

                        </div>



                        {{-- =================================================
                             QR CODE
                        ================================================== --}}

                        <div class="qr-container">


                            @if($table->is_active)

                                <img
                                    src="{{ route('tables.qr.image', $table) }}"
                                    alt="QR Code {{ $table->name }}"
                                    loading="lazy"
                                    onerror="
                                        this.style.display='none';
                                        this.nextElementSibling.style.display='block';
                                    "
                                >


                                <div
                                    style="display:none;"
                                    class="qr-disabled"
                                >

                                    <i class="fas fa-exclamation-triangle"></i>

                                    QR tidak dapat dimuat

                                </div>

                            @else

                                <div class="qr-disabled">

                                    <i class="fas fa-ban"></i>

                                    Meja nonaktif

                                </div>

                            @endif

                        </div>



                        {{-- =================================================
                             ORDER SUMMARY
                        ================================================== --}}

                        @if($hasOrder)


                            <div class="order-summary">


                                <div class="order-top">


                                    {{-- JUMLAH ORDER --}}

                                    <div>

                                        <div class="order-count-label">

                                            Pesanan hari ini

                                        </div>


                                        <div class="order-count">

                                            {{ $orderCount }}

                                            {{ $orderCount == 1 ? 'Pesanan' : 'Pesanan' }}

                                        </div>

                                    </div>



                                    {{-- TOTAL --}}

                                    <div class="order-total">

                                        <div class="order-total-label">

                                            Total transaksi

                                        </div>


                                        Rp {{ number_format(
                                            $totalOrderAmount,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    </div>

                                </div>



                                {{-- STATUS TERAKHIR --}}

                                @if($latestOrder)

                                    <div class="order-status-row">


                                        <span class="order-status">

                                            @if(
                                                ($latestOrder['order_status'] ?? '')
                                                === 'Sudah Dibayar'
                                            )

                                                <i class="fas fa-check-circle"></i>

                                            @elseif(
                                                ($latestOrder['order_status'] ?? '')
                                                === 'Menunggu Pembayaran'
                                            )

                                                <i class="fas fa-clock"></i>

                                            @elseif(
                                                ($latestOrder['order_status'] ?? '')
                                                === 'Dibatalkan'
                                            )

                                                <i class="fas fa-times-circle"></i>

                                            @else

                                                <i class="fas fa-receipt"></i>

                                            @endif


                                            {{ $latestOrder['order_status'] ?? 'Pesanan Baru' }}

                                        </span>



                                        @if(
                                            !empty($latestOrder['created_at'])
                                        )

                                            <span class="order-time">

                                                {{ \Carbon\Carbon::parse(
                                                    $latestOrder['created_at']
                                                )->format('H:i') }}

                                            </span>

                                        @endif

                                    </div>



                                    {{-- LAST ORDER --}}

                                    <div class="last-order">


                                        <div class="last-order-label">

                                            Pesanan terakhir

                                        </div>


                                        <div class="last-order-code">

                                            {{ $latestOrder['code'] ?? '-' }}

                                        </div>


                                        @if(
                                            !empty(
                                                $latestOrder['customer_name']
                                            )
                                        )

                                            <div class="last-order-customer">

                                                <i class="fas fa-user mr-1"></i>

                                                {{ $latestOrder['customer_name'] }}

                                            </div>

                                        @endif

                                    </div>

                                @endif


                            </div>


                        @else


                            <div class="order-summary order-summary-empty">

                                <i class="fas fa-receipt mr-1"></i>

                                Belum ada pesanan hari ini

                            </div>


                        @endif



                        {{-- =================================================
                             ACTIONS
                        ================================================== --}}

                        <div class="table-actions">


                            {{-- =================================================
                                 LIHAT PESANAN
                            ================================================== --}}

                            @if($hasOrder && $latestOrder)


                                <a
                                    href="{{ route(
                                        'sales.show',
                                        $latestOrder['id']
                                    ) }}"
                                    class="action-btn action-order action-full"
                                >

                                    <i class="fas fa-receipt"></i>

                                    Lihat Pesanan

                                </a>


                            @else

                                <span
                                    class="action-btn action-disabled action-full"
                                >

                                    <i class="fas fa-receipt"></i>

                                    Belum Ada Pesanan

                                </span>

                            @endif



                            {{-- =================================================
                                 LIHAT QR
                            ================================================== --}}

                            @if($table->is_active)

                                <a
                                    href="{{ route(
                                        'tables.qr',
                                        $table
                                    ) }}"
                                    class="action-btn action-qr"
                                >

                                    <i class="fas fa-qrcode"></i>

                                    Lihat QR

                                </a>

                            @else

                                <span
                                    class="action-btn action-disabled"
                                >

                                    <i class="fas fa-qrcode"></i>

                                    QR Nonaktif

                                </span>

                            @endif



                            {{-- =================================================
                                 EDIT
                            ================================================== --}}

                            <a
                                href="{{ route(
                                    'tables.edit',
                                    $table
                                ) }}"
                                class="action-btn action-edit"
                            >

                                <i class="fas fa-pen"></i>

                                Edit

                            </a>



                            {{-- =================================================
                                 TOGGLE STATUS
                            ================================================== --}}

                            <form
                                action="{{ route(
                                    'tables.toggleStatus',
                                    $table
                                ) }}"
                                method="POST"
                                class="action-form"
                            >

                                @csrf

                                @method('PATCH')


                                <button
                                    type="submit"
                                    class="action-btn action-status"
                                    style="width:100%;"
                                >

                                    @if($table->is_active)

                                        <i class="fas fa-toggle-off"></i>

                                        Nonaktifkan

                                    @else

                                        <i class="fas fa-toggle-on"></i>

                                        Aktifkan

                                    @endif

                                </button>

                            </form>



                            {{-- =================================================
                                 DELETE
                            ================================================== --}}

                            <form
                                action="{{ route(
                                    'tables.destroy',
                                    $table
                                ) }}"
                                method="POST"
                                class="action-form"
                                onsubmit="
                                    return confirm(
                                        'Yakin ingin menghapus {{ $table->name }}?'
                                    );
                                "
                            >

                                @csrf

                                @method('DELETE')


                                <button
                                    type="submit"
                                    class="action-btn action-delete"
                                    style="width:100%;"
                                >

                                    <i class="fas fa-trash"></i>

                                    Hapus

                                </button>

                            </form>


                        </div>


                    </div>


                @empty


                    {{-- =================================================
                         EMPTY STATE
                    ================================================== --}}

                    <div class="empty-state">


                        <div class="empty-icon">

                            <i class="fas fa-chair"></i>

                        </div>


                        <div class="empty-title">

                            Belum ada meja

                        </div>


                        <div class="empty-text">

                            Tambahkan meja terlebih dahulu
                            untuk membuat QR Code pemesanan.

                        </div>


                        <a
                            href="{{ route('tables.create') }}"
                            class="modern-btn btn-primary-modern"
                        >

                            <i class="fas fa-plus"></i>

                            Tambah Meja

                        </a>

                    </div>


                @endforelse


            </div>



            {{-- ========================================================
                 PAGINATION
            ========================================================= --}}

            @if($tables->hasPages())

                <div class="pagination-wrapper">

                    {{ $tables->onEachSide(1)->links() }}

                </div>

            @endif


        </div>

    </div>

</div>

@endsection