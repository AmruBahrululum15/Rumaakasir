@extends('layouts.app')

@section('content')

<style>
    .qr-page {
        min-height: calc(100vh - 70px);
        background: #f7f8fc;
        padding: 35px 20px;
    }

    .qr-wrapper {
        max-width: 650px;
        margin: auto;
    }

    .qr-card {
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 24px;
        padding: 35px;
        text-align: center;
        box-shadow: 0 10px 35px rgba(15, 23, 42, .06);
    }

    .qr-title {
        font-size: 26px;
        font-weight: 750;
        color: #111827;
        margin-bottom: 6px;
    }

    .qr-subtitle {
        color: #718096;
        font-size: 14px;
        margin-bottom: 28px;
    }

    .qr-box {
        width: 360px;
        max-width: 100%;
        margin: 0 auto 25px;
        padding: 20px;
        background: #fff;
        border: 1px solid #edf0f5;
        border-radius: 20px;
    }

    .qr-box img {
        width: 100%;
        height: auto;
        display: block;
    }

    .table-name {
        font-size: 21px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 5px;
    }

    .table-url {
        background: #f8fafc;
        border-radius: 10px;
        padding: 11px;
        font-size: 12px;
        color: #64748b;
        word-break: break-all;
        margin-bottom: 22px;
    }

    .qr-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .qr-btn {
        border: 0;
        border-radius: 11px;
        padding: 11px 18px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-back {
        background: #f1f5f9;
        color: #334155;
    }

    .btn-print {
        background: #111827;
        color: #fff;
    }

    .btn-open {
        background: #eef2ff;
        color: #4338ca;
    }

    .qr-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    @media print {

        body * {
            visibility: hidden;
        }

        .print-area,
        .print-area * {
            visibility: visible;
        }

        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        .qr-card {
            box-shadow: none;
            border: 0;
        }

        .qr-actions,
        .table-url {
            display: none;
        }
    }
</style>


<div class="qr-page">

    <div class="qr-wrapper">

        <div class="qr-card print-area">

            <div class="qr-title">
                {{ $table->name }}
            </div>

            <div class="qr-subtitle">
                Scan QR Code untuk melakukan pemesanan
            </div>


            <div class="qr-box">

                <img
                    src="{{ route('tables.qr.image', $table) }}"
                    alt="QR Code {{ $table->name }}"
                >

            </div>


            <div class="table-name">
                {{ $table->name }}
            </div>


            <div class="qr-subtitle">
                Silakan scan menggunakan kamera HP
            </div>


            <div class="table-url">
                {{ $url }}
            </div>


            <div class="qr-actions">

                <a
                    href="{{ route('tables.index') }}"
                    class="qr-btn btn-back">

                    <i class="fas fa-arrow-left"></i>

                    Kembali

                </a>


                <button
                    type="button"
                    onclick="window.print()"
                    class="qr-btn btn-print">

                    <i class="fas fa-print"></i>

                    Cetak QR

                </button>


                <a
                    href="{{ $url }}"
                    target="_blank"
                    class="qr-btn btn-open">

                    <i class="fas fa-external-link-alt"></i>

                    Buka Link

                </a>

            </div>

        </div>

    </div>

</div>

@endsection