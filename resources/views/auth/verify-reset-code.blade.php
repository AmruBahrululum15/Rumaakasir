<x-guest-layout>

    <style>

        .verify-page {

            min-height:100vh;

            display:flex;

            align-items:center;

            justify-content:center;

            padding:30px 16px;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(255,122,24,.10),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffaf4 0%,
                    #f7f5fb 100%
                );

        }


        .verify-card {

            width:100%;

            max-width:430px;

            padding:34px;

            background:#fff;

            border:
                1px solid #ece8ef;

            border-radius:22px;

            box-shadow:
                0 22px 60px
                rgba(23,32,51,.10);

            text-align:center;

        }


        .verify-icon {

            width:60px;
            height:60px;

            margin:
                0 auto 18px;

            display:flex;

            align-items:center;
            justify-content:center;

            border-radius:18px;

            background:#fff1e7;

            color:#f36b0a;

            font-size:24px;

        }


        .verify-card h1 {

            margin:
                0 0 8px;

            color:#172033;

            font-size:23px;

            font-weight:800;

        }


        .verify-card p {

            margin:
                0 0 18px;

            color:#7d8494;

            font-size:13px;

            line-height:1.6;

        }


        .verify-email {

            display:inline-block;

            margin-bottom:18px;

            padding:
                7px 11px;

            border-radius:999px;

            background:#fff8f2;

            color:#d95d08;

            font-size:11px;

            font-weight:700;

        }


        .otp-input {

            width:100%;

            height:58px;

            box-sizing:border-box;

            padding:
                0 14px;

            border:
                1px solid #ddd9e4;

            border-radius:14px;

            background:#fbfafc;

            color:#172033;

            outline:none;

            text-align:center;

            font-size:23px;

            font-weight:800;

            letter-spacing:8px;

        }


        .otp-input:focus {

            border-color:#f36b0a;

            background:#fff;

            box-shadow:
                0 0 0 3px
                rgba(243,107,10,.08);

        }


        .otp-button {

            width:100%;

            height:48px;

            margin-top:14px;

            border:0;

            border-radius:12px;

            background:#f36b0a;

            color:#fff;

            font-size:13px;

            font-weight:800;

            cursor:pointer;

        }


        .otp-button:hover {

            background:#df5d08;

        }


        .verify-status {

            padding:11px 13px;

            margin-bottom:16px;

            border-radius:10px;

            background:#eef9f2;

            color:#198754;

            font-size:12px;

            text-align:left;

        }


        .verify-error {

            margin-top:7px;

            color:#dc3545;

            font-size:11px;

            text-align:left;

        }


        .resend-area {

            margin-top:18px;

            color:#9197a4;

            font-size:11px;

        }


        .resend-button {

            border:0;

            padding:0;

            background:transparent;

            color:#f36b0a;

            font-weight:800;

            cursor:pointer;

        }


        .resend-button:disabled {

            color:#aaa;

            cursor:not-allowed;

        }


        .verify-back {

            display:block;

            margin-top:17px;

            color:#7d8494;

            font-size:11px;

            text-decoration:none;

        }

    </style>


    <div class="verify-page">

        <div class="verify-card">

            <div class="verify-icon">

                <i class="fas fa-envelope-open-text"></i>

            </div>


            <h1>
                Verifikasi Kode
            </h1>


            <p>
                Masukkan kode 6 digit yang kami kirim ke:
            </p>


            <div class="verify-email">

                {{ $maskedEmail }}

            </div>


            @if(session('status'))

                <div class="verify-status">

                    {{ session('status') }}

                </div>

            @endif


            <form
                method="POST"
                action="{{ route('password.otp.verify') }}"
            >

                @csrf


                <input
                    type="text"
                    name="code"
                    class="otp-input"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    minlength="6"
                    autocomplete="one-time-code"
                    placeholder="000000"
                    required
                    autofocus
                >


                @error('code')

                    <div class="verify-error">

                        {{ $message }}

                    </div>

                @enderror


                <button
                    type="submit"
                    class="otp-button"
                >

                    Verifikasi Kode

                </button>

            </form>


            <div class="resend-area">

                Tidak menerima email?

                <span id="resendTimer"></span>


                <form
                    method="POST"
                    action="{{ route('password.otp.resend') }}"
                    style="display:inline;"
                >

                    @csrf


                    <button
                        type="submit"
                        id="resendButton"
                        class="resend-button"
                        {{ $resendIn > 0 ? 'disabled' : '' }}
                    >

                        Kirim ulang

                    </button>

                </form>

            </div>


            <a
                href="{{ route('password.request') }}"
                class="verify-back"
            >

                <i class="fas fa-arrow-left mr-1"></i>

                Gunakan email lain

            </a>

        </div>

    </div>


    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                let remaining =
                    {{ (int) $resendIn }};


                const timer =
                    document.getElementById(
                        'resendTimer'
                    );


                const button =
                    document.getElementById(
                        'resendButton'
                    );


                function updateTimer() {

                    if (
                        remaining <= 0
                    ) {

                        timer.textContent =
                            '';

                        button.disabled =
                            false;

                        return;
                    }


                    timer.textContent =
                        ' Tunggu ' +
                        remaining +
                        ' detik.';


                    button.disabled =
                        true;


                    remaining--;


                    setTimeout(
                        updateTimer,
                        1000
                    );

                }


                updateTimer();


                const otp =
                    document.querySelector(
                        '.otp-input'
                    );


                if (otp) {

                    otp.addEventListener(
                        'input',
                        function () {

                            this.value =
                                this.value
                                    .replace(
                                        /\D/g,
                                        ''
                                    )
                                    .slice(
                                        0,
                                        6
                                    );

                        }
                    );

                }

            }
        );

    </script>

</x-guest-layout>