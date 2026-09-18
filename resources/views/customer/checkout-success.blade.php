<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Pesanan Berhasil - {{ $table->name }}
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            min-height: 100vh;

            font-family:
                'Inter',
                -apple-system,
                BlinkMacSystemFont,
                'Segoe UI',
                sans-serif;

            background:

                radial-gradient(
                    circle at 10% 0%,
                    rgba(249,115,22,.10),
                    transparent 30%
                ),

                radial-gradient(
                    circle at 90% 20%,
                    rgba(245,158,11,.09),
                    transparent 28%
                ),

                #f8f9fc;

            color: #172033;

        }


        .page {

            width: 100%;

            max-width: 720px;

            margin: auto;

            padding:
                30px
                18px
                60px;

        }


        /* =========================================================
           SUCCESS HEADER
        ========================================================= */

        .success-card {

            position: relative;

            overflow: hidden;

            background:

                radial-gradient(
                    circle at 90% 10%,
                    rgba(255,255,255,.22),
                    transparent 28%
                ),

                linear-gradient(
                    135deg,
                    #f97316,
                    #fb923c 55%,
                    #f59e0b
                );

            color: white;

            border-radius: 28px;

            padding:
                38px
                28px
                34px;

            text-align: center;

            box-shadow:
                0 20px 45px
                rgba(249,115,22,.20);

        }


        .success-card::before {

            content: "";

            position: absolute;

            width: 220px;
            height: 220px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.08);

            right: -80px;
            top: -130px;

        }


        .success-card::after {

            content: "";

            position: absolute;

            width: 140px;
            height: 140px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.06);

            left: -70px;
            bottom: -80px;

        }


        .success-content {

            position: relative;

            z-index: 2;

        }


        /* =========================================================
           CHECK ICON
        ========================================================= */

        .success-icon {

            width: 82px;
            height: 82px;

            margin:
                0
                auto
                20px;

            border-radius: 50%;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                rgba(255,255,255,.18);

            border:
                5px solid
                rgba(255,255,255,.22);

            font-size: 35px;

            animation:
                successPop .5s ease;

        }


        @keyframes successPop {

            0% {

                transform:
                    scale(.5);

                opacity: 0;

            }

            70% {

                transform:
                    scale(1.08);

            }

            100% {

                transform:
                    scale(1);

                opacity: 1;

            }

        }


        .success-card h1 {

            font-family:
                'Poppins',
                sans-serif;

            font-size: 30px;

            font-weight: 800;

            margin-bottom: 8px;

        }


        .success-card p {

            font-size: 13px;

            line-height: 1.6;

            opacity: .94;

        }


        .table-badge {

            display: inline-flex;

            align-items: center;

            gap: 8px;

            margin-top: 20px;

            padding:
                10px
                16px;

            border-radius: 999px;

            background:
                rgba(255,255,255,.18);

            border:
                1px solid
                rgba(255,255,255,.16);

            font-size: 12px;

            font-weight: 800;

        }


        /* =========================================================
           CARD
        ========================================================= */

        .card {

            background: #fff;

            border:
                1px solid
                #edf0f5;

            border-radius: 22px;

            padding: 23px;

            margin-top: 18px;

            box-shadow:
                0 8px 30px
                rgba(15,23,42,.045);

        }


        .card-header {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 18px;

        }


        .card-icon {

            width: 42px;
            height: 42px;

            border-radius: 13px;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                #fff7ed;

            color: #f97316;

        }


        .card-title {

            font-family:
                'Poppins',
                sans-serif;

            font-size: 16px;

            font-weight: 800;

        }


        .card-subtitle {

            margin-top: 3px;

            color: #98a2b3;

            font-size: 11px;

        }


        /* =========================================================
           ORDER CODE
        ========================================================= */

        .order-code {

            padding:
                17px
                18px;

            border-radius: 15px;

            background:
                linear-gradient(
                    135deg,
                    #fffaf5,
                    #fff7ed
                );

            border:
                1px solid
                #fed7aa;

            text-align: center;

        }


        .order-code-label {

            color: #9a6b3f;

            font-size: 10px;

            font-weight: 800;

            letter-spacing: 1px;

            margin-bottom: 5px;

        }


        .order-code-value {

            color: #ea580c;

            font-family:
                'Poppins',
                sans-serif;

            font-size: 21px;

            font-weight: 800;

            letter-spacing: .5px;

        }


        /* =========================================================
           ITEMS
        ========================================================= */

        .order-item {

            display: flex;

            justify-content: space-between;

            gap: 15px;

            padding:
                14px
                0;

            border-bottom:
                1px solid
                #edf0f5;

        }


        .order-item:last-child {

            border-bottom: none;

        }


        .item-left {

            min-width: 0;

        }


        .item-name {

            color: #172033;

            font-size: 13px;

            font-weight: 800;

        }


        .item-meta {

            margin-top: 4px;

            color: #8a94a7;

            font-size: 11px;

        }


        .item-note {

            display: flex;

            align-items: flex-start;

            gap: 6px;

            margin-top: 7px;

            padding:
                7px
                9px;

            border-radius: 9px;

            background:
                #fff7ed;

            color: #c2410c;

            font-size: 10px;

            line-height: 1.4;

        }


        .item-price {

            white-space: nowrap;

            color: #172033;

            font-size: 12px;

            font-weight: 800;

        }


        /* =========================================================
           TOTAL
        ========================================================= */

        .total-box {

            margin-top: 17px;

            padding-top: 18px;

            border-top:
                1px dashed
                #dce1e8;

        }


        .total-row {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

        }


        .total-label {

            color: #667085;

            font-size: 13px;

            font-weight: 600;

        }


        .total-value {

            color: #f97316;

            font-family:
                'Poppins',
                sans-serif;

            font-size: 22px;

            font-weight: 800;

        }


        /* =========================================================
           CUSTOMER INFO
        ========================================================= */

        .info-row {

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                12px
                0;

            border-bottom:
                1px solid
                #f0f2f5;

        }


        .info-row:last-child {

            border-bottom: none;

            padding-bottom: 0;

        }


        .info-icon {

            width: 35px;
            height: 35px;

            flex-shrink: 0;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f8fafc;

            color: #f97316;

            font-size: 12px;

        }


        .info-content {

            min-width: 0;

        }


        .info-label {

            color: #98a2b3;

            font-size: 10px;

            margin-bottom: 2px;

        }


        .info-value {

            color: #344054;

            font-size: 12px;

            font-weight: 700;

            word-break: break-word;

        }


        /* =========================================================
           STATUS
        ========================================================= */

        .status-box {

            display: flex;

            align-items: center;

            gap: 12px;

            padding:
                14px
                15px;

            border-radius: 14px;

            background:
                #f0fdf4;

            border:
                1px solid
                #bbf7d0;

        }


        .status-icon {

            width: 35px;
            height: 35px;

            border-radius: 10px;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                #dcfce7;

            color: #16a34a;

            flex-shrink: 0;

        }


        .status-title {

            color: #166534;

            font-size: 12px;

            font-weight: 800;

        }


        .status-text {

            color: #4d7c5a;

            font-size: 10px;

            margin-top: 2px;

        }


        /* =========================================================
           BUTTON
        ========================================================= */

        .actions {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 10px;

            margin-top: 18px;

        }


        .button {

            min-height: 50px;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 8px;

            border-radius: 13px;

            font-size: 12px;

            font-weight: 800;

            transition: .2s;

        }


        .button-primary {

            background:
                linear-gradient(
                    135deg,
                    #f97316,
                    #fb923c
                );

            color: white;

            box-shadow:
                0 10px 22px
                rgba(249,115,22,.20);

        }


        .button-primary:hover {

            transform:
                translateY(-1px);

        }


        .button-secondary {

            background: #fff;

            color: #ea580c;

            border:
                1px solid
                #fed7aa;

        }


        .button-secondary:hover {

            background:
                #fff7ed;

        }


        /* =========================================================
           FOOTER
        ========================================================= */

        .footer {

            text-align: center;

            margin-top: 20px;

            color: #98a2b3;

            font-size: 10px;

            line-height: 1.6;

        }


        /* =========================================================
           MOBILE
        ========================================================= */

        @media (max-width: 600px) {

            .page {

                padding:
                    14px
                    12px
                    40px;

            }


            .success-card {

                padding:
                    31px
                    20px
                    28px;

                border-radius: 22px;

            }


            .success-card h1 {

                font-size: 25px;

            }


            .success-icon {

                width: 72px;
                height: 72px;

                font-size: 29px;

            }


            .card {

                padding: 18px;

                border-radius: 18px;

            }


            .actions {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<div class="page">


    {{-- =========================================================
         SUCCESS
    ========================================================== --}}

    <div class="success-card">

        <div class="success-content">

            <div class="success-icon">

                <i class="fas fa-check"></i>

            </div>


            <h1>
                Pesanan Berhasil!
            </h1>


            <p>

                Terima kasih sudah melakukan
                pemesanan. Pesanan kamu sudah
                berhasil dicatat.

            </p>


            <div class="table-badge">

                <i class="fas fa-chair"></i>

                {{ $table->name }}

            </div>

        </div>

    </div>


    {{-- =========================================================
         ORDER CODE
    ========================================================== --}}

    <div class="card">

        <div class="order-code">

            <div class="order-code-label">

                NOMOR PESANAN

            </div>

            <div class="order-code-value">

                {{ $sale->code }}

            </div>

        </div>

    </div>


    {{-- =========================================================
         DETAIL PESANAN
    ========================================================== --}}

    <div class="card">

        <div class="card-header">

            <div class="card-icon">

                <i class="fas fa-receipt"></i>

            </div>

            <div>

                <div class="card-title">
                    Detail Pesanan
                </div>

                <div class="card-subtitle">
                    Rincian menu yang kamu pesan
                </div>

            </div>

        </div>


        @php

            $saleDetails = $sale->saleDetails()
                ->with('product')
                ->get();

        @endphp


        @foreach($saleDetails as $detail)

            <div class="order-item">

                <div class="item-left">

                    <div class="item-name">

                        {{ $detail->product->name ?? 'Produk' }}

                    </div>


                    <div class="item-meta">

                        {{ $detail->quantity }}

                        ×

                        Rp
                        {{ number_format($detail->price, 0, ',', '.') }}

                    </div>


                    @if(!empty($detail->note))

                        <div class="item-note">

                            <i class="fas fa-comment-dots"></i>

                            <span>

                                {{ $detail->note }}

                            </span>

                        </div>

                    @endif

                </div>


                <div class="item-price">

                    Rp
                    {{ number_format($detail->subtotal, 0, ',', '.') }}

                </div>

            </div>

        @endforeach


        {{-- TOTAL --}}

        <div class="total-box">

            <div class="total-row">

                <div class="total-label">

                    Total Pesanan

                </div>

                <div class="total-value">

                    Rp
                    {{ number_format($sale->total, 0, ',', '.') }}

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         DATA CUSTOMER
    ========================================================== --}}

    @php

        $notes = collect(
            preg_split(
                '/\r\n|\r|\n/',
                (string) $sale->notes
            )
        );

        $customerName =
            $notes
                ->first(
                    fn ($line) =>
                        str_starts_with(
                            $line,
                            'CUSTOMER_NAME='
                        )
                );

        $customerPhone =
            $notes
                ->first(
                    fn ($line) =>
                        str_starts_with(
                            $line,
                            'CUSTOMER_PHONE='
                        )
                );

        $customerEmail =
            $notes
                ->first(
                    fn ($line) =>
                        str_starts_with(
                            $line,
                            'CUSTOMER_EMAIL='
                        )
                );

        $customerName =
            $customerName
                ? substr(
                    $customerName,
                    strlen('CUSTOMER_NAME=')
                )
                : '-';

        $customerName = $sale->customer_display_name;

        $customerPhone =
            $customerPhone
                ? substr(
                    $customerPhone,
                    strlen('CUSTOMER_PHONE=')
                )
                : '-';

        $customerEmail =
            $customerEmail
                ? substr(
                    $customerEmail,
                    strlen('CUSTOMER_EMAIL=')
                )
                : '-';

    @endphp


    <div class="card">

        <div class="card-header">

            <div class="card-icon">

                <i class="fas fa-user"></i>

            </div>

            <div>

                <div class="card-title">
                    Data Pelanggan
                </div>

                <div class="card-subtitle">
                    Informasi yang digunakan saat checkout
                </div>

            </div>

        </div>

        <div class="info-row"><div class="info-icon"><i class="fas fa-credit-card"></i></div><div class="info-content"><div class="info-label">Pembayaran</div><div class="info-value">{{ $sale->payment_method_display }} — {{ $sale->payment_status_display }}</div></div></div>
        <div class="info-row"><div class="info-icon"><i class="fas fa-qrcode"></i></div><div class="info-content"><div class="info-label">Sumber Pesanan</div><div class="info-value">{{ $sale->order_source_display }}@if($sale->table) — Meja {{ $sale->table->name }}@endif</div></div></div>


        {{-- NAME --}}

        <div class="info-row">

            <div class="info-icon">

                <i class="fas fa-user"></i>

            </div>

            <div class="info-content">

                <div class="info-label">
                    Nama
                </div>

                <div class="info-value">
                    {{ $customerName }}
                </div>

            </div>

        </div>


        {{-- PHONE --}}

        <div class="info-row">

            <div class="info-icon">

                <i class="fas fa-phone"></i>

            </div>

            <div class="info-content">

                <div class="info-label">
                    Nomor HP
                </div>

                <div class="info-value">
                    {{ $customerPhone }}
                </div>

            </div>

        </div>


        {{-- EMAIL --}}

        <div class="info-row">

            <div class="info-icon">

                <i class="fas fa-envelope"></i>

            </div>

            <div class="info-content">

                <div class="info-label">
                    Email
                </div>

                <div class="info-value">
                    {{ $customerEmail }}
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         STATUS
    ========================================================== --}}

    <div class="card">

        <div class="status-box">

            <div class="status-icon">

                <i class="fas fa-check"></i>

            </div>

            <div>

                <div class="status-title">

                    Pesanan sudah diterima

                </div>

                <div class="status-text">

                    Silakan tunggu pesanan kamu
                    diproses oleh restoran.

                </div>

            </div>

        </div>


        {{-- ACTIONS --}}

        <div class="actions">

            <a
                href="{{ url('/order/table/' . $table->qr_token) }}"
                class="button button-primary"
            >

                <i class="fas fa-utensils"></i>

                Pesan Lagi

            </a>


            <button
                type="button"
                class="button button-secondary"
                onclick="window.print()"
            >

                <i class="fas fa-print"></i>

                Cetak

            </button>

        </div>

    </div>


    <div class="footer">

        Simpan nomor pesanan kamu:

        <strong>
            {{ $sale->code }}
        </strong>

        <br>

        Terima kasih telah melakukan pemesanan ❤️

    </div>


</div>


<style>

@media print {

    body {

        background: white;

    }


    .page {

        max-width: 100%;

        padding: 0;

    }


    .actions,
    .footer {

        display: none !important;

    }


    .card {

        box-shadow: none;

        break-inside: avoid;

    }

}

</style>


</body>

</html>
