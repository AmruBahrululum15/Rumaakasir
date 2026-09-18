<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Pembayaran Pesanan</title>

    <script
        type="text/javascript"
        src="{{ config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
        data-client-key="{{ config('midtrans.client_key') }}"
    ></script>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family:
                Inter,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #fff7ed,
                    #fff,
                    #ffedd5
                );

            min-height: 100vh;
            color: #292524;
        }

        .container {
            width: min(560px, 92%);
            margin: 0 auto;
            padding: 40px 0;
        }

        .card {
            background: #fff;
            border-radius: 28px;
            padding: 30px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, .08);
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            background: #f97316;
            color: white;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 26px;
            font-weight: 800;

            margin-bottom: 20px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 26px;
        }

        .subtitle {
            color: #78716c;
            margin-bottom: 28px;
        }

        .order-box {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 24px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 7px 0;
        }

        .label {
            color: #78716c;
        }

        .value {
            font-weight: 700;
            text-align: right;
        }

        .total {
            border-top: 1px dashed #fdba74;
            margin-top: 10px;
            padding-top: 14px;
            font-size: 20px;
            color: #ea580c;
        }

        .pay-button {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 16px 20px;

            background: #f97316;
            color: #fff;

            font-size: 16px;
            font-weight: 800;

            cursor: pointer;

            box-shadow:
                0 12px 25px rgba(249, 115, 22, .25);
        }

        .pay-button:hover {
            background: #ea580c;
        }

        .info {
            margin-top: 18px;
            text-align: center;
            color: #78716c;
            font-size: 13px;
            line-height: 1.6;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <div class="logo">
            ₹
        </div>

        <h1>
            Selesaikan Pembayaran
        </h1>

        <div class="subtitle">
            Silakan lanjutkan pembayaran pesanan Anda.
        </div>

        <div class="order-box">

            <div class="row">
                <span class="label">
                    Nomor Pesanan
                </span>

                <span class="value">
                    {{ $sale->code }}
                </span>
            </div>

            <div class="row">
                <span class="label">
                    Meja
                </span>

                <span class="value">
                    {{ $table->name }}
                </span>
            </div>

            <div class="row">
                <span class="label">
                    Pelanggan
                </span>

                <span class="value">
                    {{ $customerName }}
                </span>
            </div>

            <div class="row total">
                <span>
                    Total
                </span>

                <span>
                    Rp {{ number_format($sale->total, 0, ',', '.') }}
                </span>
            </div>

        </div>

        <button
            type="button"
            id="pay-button"
            class="pay-button"
        >
            Lanjutkan Pembayaran
        </button>

        <div class="info">
            Pembayaran diproses secara aman melalui
            Midtrans.
            <br>
            QRIS dan Virtual Account tersedia
            sesuai metode yang aktif.
        </div>

    </div>

</div>

<script>
    const snapToken = @json($snapToken);

    const payButton =
        document.getElementById('pay-button');

    payButton.addEventListener(
        'click',
        function () {

            window.snap.pay(
                snapToken,
                {
                    language: 'id',

                    onSuccess: function (result) {

                        console.log(
                            'PAYMENT SUCCESS',
                            result
                        );

                        window.location.href =
                            "{{ route(
                                'customer.checkout.success',
                                [
                                    'table' => $table->id,
                                    'sale' => $sale->id
                                ]
                            ) }}";
                    },

                    onPending: function (result) {

                        console.log(
                            'PAYMENT PENDING',
                            result
                        );

                        window.location.href =
                            "{{ route(
                                'customer.checkout.success',
                                [
                                    'table' => $table->id,
                                    'sale' => $sale->id
                                ]
                            ) }}";
                    },

                    onError: function (result) {

                        console.error(
                            'PAYMENT ERROR',
                            result
                        );

                        alert(
                            'Pembayaran gagal. Silakan coba lagi.'
                        );
                    },

                    onClose: function () {

                        console.log(
                            'Customer menutup pembayaran.'
                        );
                    }
                }
            );

        }
    );
</script>

</body>
</html>