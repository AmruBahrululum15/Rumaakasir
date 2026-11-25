<x-guest-layout>

    <style>

        .reset-page {

            min-height:100vh;

            display:flex;

            align-items:center;

            justify-content:center;

            padding:30px 16px;

            background:
                radial-gradient(
                    circle at bottom left,
                    rgba(255,122,24,.10),
                    transparent 30%
                ),
                linear-gradient(
                    135deg,
                    #fffaf4 0%,
                    #f7f5fb 100%
                );

        }


        .reset-card {

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

        }


        .reset-icon {

            width:54px;
            height:54px;

            display:flex;

            align-items:center;
            justify-content:center;

            margin-bottom:18px;

            border-radius:16px;

            background:#fff1e7;

            color:#f36b0a;

            font-size:22px;

        }


        .reset-card h1 {

            margin:
                0 0 8px;

            color:#172033;

            font-size:23px;

            font-weight:800;

        }


        .reset-card p {

            margin:
                0 0 22px;

            color:#7d8494;

            font-size:13px;

            line-height:1.6;

        }


        .reset-label {

            display:block;

            margin-bottom:7px;

            color:#394254;

            font-size:12px;

            font-weight:700;

        }


        .reset-input {

            width:100%;

            height:48px;

            box-sizing:border-box;

            padding:
                0 14px;

            margin-bottom:14px;

            border:
                1px solid #ddd9e4;

            border-radius:12px;

            background:#fbfafc;

            color:#172033;

            outline:none;

        }


        .reset-input:focus {

            border-color:#f36b0a;

            background:#fff;

            box-shadow:
                0 0 0 3px
                rgba(243,107,10,.08);

        }


        .reset-button {

            width:100%;

            height:48px;

            border:0;

            border-radius:12px;

            background:#f36b0a;

            color:#fff;

            font-size:13px;

            font-weight:800;

            cursor:pointer;

        }


        .reset-button:hover {

            background:#df5d08;

        }


        .reset-error {

            margin-top:-8px;

            margin-bottom:10px;

            color:#dc3545;

            font-size:11px;

        }

    </style>


    <div class="reset-page">

        <div class="reset-card">

            <div class="reset-icon">

                <i class="fas fa-lock"></i>

            </div>


            <h1>
                Password Baru
            </h1>


            <p>
                Kode berhasil diverifikasi.
                Buat password baru untuk akun Anda.
            </p>


            <form
                method="POST"
                action="{{ route('password.otp.update') }}"
            >

                @csrf


                <label
                    for="password"
                    class="reset-label"
                >

                    Password Baru

                </label>


                <input
                    id="password"
                    type="password"
                    name="password"
                    class="reset-input"
                    placeholder="Minimal 8 karakter"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >


                @error('password')

                    <div class="reset-error">

                        {{ $message }}

                    </div>

                @enderror


                <label
                    for="password_confirmation"
                    class="reset-label"
                >

                    Konfirmasi Password

                </label>


                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="reset-input"
                    placeholder="Ulangi password baru"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >


                <button
                    type="submit"
                    class="reset-button"
                >

                    <i class="fas fa-check mr-1"></i>

                    Simpan Password Baru

                </button>

            </form>

        </div>

    </div>

</x-guest-layout>