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

        body {
            margin: 0;
            padding: 30px;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #111827;
        }

        .top-bar {
            max-width: 1100px;
            margin: 0 auto 25px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 15mm;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .header h2 {
            margin: 0;
            font-size: 25px;
            color: #0f172a;
            letter-spacing: 0.5px;
        }

        .header p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .qr-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10mm;
        }

        .qr-card {
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            padding: 12px 10px;
            text-align: center;
            background: #ffffff;
            min-height: 110mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            page-break-inside: avoid;
        }

        .table-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .qr-code {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 3px 0 8px;
        }

        .qr-code svg {
            width: 150px !important;
            height: 150px !important;
        }

        .instruction {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
            margin-top: 4px;
        }

        .url {
            margin-top: 5px;
            font-size: 9px;
            color: #94a3b8;
            word-break: break-all;
            max-width: 85%;
        }

        .empty {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
            font-size: 18px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            html,
            body {
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
                padding: 12mm;
                box-shadow: none;
            }

            .qr-grid {
                gap: 7mm;
            }

            .qr-card {
                border: 1px solid #cbd5e1;
                min-height: 115mm;
            }
        }
    </style>
</head>

<body>

    {{-- HEADER WEBSITE --}}
    <div class="top-bar no-print">

        <div class="title">
            <h1>QR Code Semua Meja</h1>

            <p>
                {{ $totalTables }} meja aktif
            </p>
        </div>

        <div class="buttons">

            <a href="{{ route('tables.index') }}" class="btn btn-back">
                ← Kembali
            </a>

            <button onclick="window.print()" class="btn btn-print">
                🖨 Cetak / Simpan PDF
            </button>

        </div>

    </div>


    {{-- KERTAS A4 --}}
    <div class="paper">

        <div class="header">

            <h2>QR CODE MEJA</h2>

            <p>
                Scan QR Code untuk melakukan pemesanan
            </p>

        </div>


        @if($tables->count() > 0)

            <div class="qr-grid">

                @foreach($tables as $table)

                    <div class="qr-card">

                        <div class="table-name">
                            {{ $table->name }}
                        </div>

                        <div class="qr-code">

                            {!! QrCode::size(150)
                                ->margin(1)
                                ->generate(url('/order/table/' . $table->qr_token))
                            !!}

                        </div>

                        <div class="instruction">
                            Scan QR untuk melihat menu<br>
                            dan melakukan pemesanan
                        </div>

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