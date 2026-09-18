<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>QR Code Semua Meja</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        /* =========================================================
           HEADER WEBSITE
        ========================================================= */

        .top-bar {
            max-width: 1100px;
            margin: 0 auto 25px auto;
            padding: 30px 20px 0;

            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;
        }

        .title h1 {
            margin: 0;
            font-size: 30px;
            color: #0f172a;
        }

        .title p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 15px;
        }

        .buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            border: none;
            border-radius: 10px;

            padding: 13px 20px;

            font-size: 15px;
            font-weight: 600;

            cursor: pointer;
            text-decoration: none;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            gap: 8px;
        }

        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #e2e8f0;
        }

        .btn-print {
            background: #111827;
            color: #ffffff;
        }

        /* =========================================================
           KERTAS A4
        ========================================================= */

        .paper {
            width: 210mm;
            min-height: 297mm;

            margin: 0 auto;

            background: #ffffff;

            padding: 10mm;

            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
        }

        /* =========================================================
           HEADER KERTAS
        ========================================================= */

        .header {
            text-align: center;

            margin-bottom: 7mm;
        }

        .header h2 {
            margin: 0;

            font-size: 23px;

            color: #0f172a;

            letter-spacing: 0.5px;
        }

        .header p {
            margin: 4px 0 0;

            color: #64748b;

            font-size: 12px;
        }

        /* =========================================================
           GRID QR
           2 KOLOM
        ========================================================= */

        .qr-grid {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            column-gap: 6mm;
            row-gap: 6mm;
        }

        /* =========================================================
           QR CARD
        ========================================================= */

        .qr-card {
            border: 1px solid #dbe3ec;

            border-radius: 10px;

            padding: 5mm 4mm;

            text-align: center;

            background: #ffffff;

            height: 61mm;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            page-break-inside: avoid;

            break-inside: avoid;
        }

        /* =========================================================
           NAMA MEJA
        ========================================================= */

        .table-name {
            font-size: 18px;

            font-weight: 700;

            color: #0f172a;

            margin-bottom: 2mm;
        }

        /* =========================================================
           QR CODE
        ========================================================= */

        .qr-code {
            display: flex;

            justify-content: center;
            align-items: center;

            margin: 1mm 0 2mm;
        }

        .qr-code svg {
            width: 37mm !important;
            height: 37mm !important;
        }

        /* =========================================================
           INSTRUKSI
        ========================================================= */

        .instruction {
            font-size: 9px;

            color: #64748b;

            line-height: 1.3;

            margin-top: 1mm;
        }

        /* =========================================================
           URL
        ========================================================= */

        .url {
            margin-top: 1mm;

            font-size: 7px;

            color: #94a3b8;

            word-break: break-all;

            max-width: 90%;
        }

        /* =========================================================
           EMPTY
        ========================================================= */

        .empty {
            text-align: center;

            padding: 60px 20px;

            color: #64748b;

            font-size: 18px;
        }

        /* =========================================================
           PRINT
        ========================================================= */

        @media print {

            @page {
                size: A4 portrait;
                margin: 0;
            }

            html,
            body {
                width: 210mm;
                min-height: 297mm;

                background: #ffffff;

                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .paper {
                width: 210mm;
                min-height: 297mm;

                margin: 0;

                padding: 10mm;

                box-shadow: none;
            }

            .qr-grid {
                grid-template-columns: repeat(2, 1fr);

                column-gap: 6mm;
                row-gap: 6mm;
            }

            .qr-card {
                height: 61mm;

                border: 1px solid #cbd5e1;

                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    {{-- =========================================================
         HEADER WEBSITE
    ========================================================== --}}

    <div class="top-bar no-print">

        <div class="title">

            <h1>
                QR Code Semua Meja
            </h1>

            <p>
                {{ $totalTables }} meja aktif
            </p>

        </div>

        <div class="buttons">

            <a href="{{ route('tables.index') }}"
               class="btn btn-back">

                ← Kembali

            </a>

            <button
                type="button"
                onclick="window.print()"
                class="btn btn-print">

                🖨 Cetak / Simpan PDF

            </button>

        </div>

    </div>


    {{-- =========================================================
         KERTAS A4
    ========================================================== --}}

    <div class="paper">

        {{-- HEADER --}}

        <div class="header">

            <h2>
                QR CODE MEJA
            </h2>

            <p>
                Scan QR Code untuk melakukan pemesanan
            </p>

        </div>


        {{-- =====================================================
             JIKA ADA MEJA AKTIF
        ====================================================== --}}

        @if($tables->count() > 0)

            <div class="qr-grid">

                @foreach($tables as $table)

                    <div class="qr-card">

                        {{-- NAMA MEJA --}}

                        <div class="table-name">

                            {{ $table->name }}

                        </div>


                        {{-- QR CODE --}}

                        <div class="qr-code">

                            {!! QrCode::size(150)
                                ->margin(1)
                                ->generate(
                                    url('/order/table/' . $table->qr_token)
                                )
                            !!}

                        </div>


                        {{-- INSTRUKSI --}}

                        <div class="instruction">

                            Scan QR untuk melihat menu<br>

                            dan melakukan pemesanan

                        </div>


                        {{-- URL --}}

                        <div class="url">

                            {{ url('/order/table/' . $table->qr_token) }}

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <div class="empty">

                Belum ada meja aktif.

            </div>

        @endif

    </div>

</body>

</html>