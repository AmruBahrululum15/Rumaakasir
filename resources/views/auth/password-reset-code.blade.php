<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Kode Reset Password
    </title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background:#f5f3ef;
        font-family:Arial,Helvetica,sans-serif;
        color:#172033;
    "
>

<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background:#f5f3ef;
        padding:40px 15px;
    "
>

    <tr>

        <td align="center">

            <table
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    max-width:560px;
                    background:#ffffff;
                    border:1px solid #ece7e1;
                    border-radius:18px;
                    overflow:hidden;
                "
            >

                {{-- HEADER --}}
                <tr>

                    <td
                        style="
                            padding:28px 32px;
                            background:#fffaf5;
                            border-bottom:1px solid #eee7df;
                        "
                    >

                        <div
                            style="
                                font-size:20px;
                                font-weight:800;
                                color:#172033;
                            "
                        >

                            {{ $appName }}

                        </div>


                        <div
                            style="
                                margin-top:5px;
                                font-size:12px;
                                color:#8a909d;
                            "
                        >

                            Keamanan akun

                        </div>

                    </td>

                </tr>


                {{-- CONTENT --}}
                <tr>

                    <td
                        style="
                            padding:32px;
                        "
                    >

                        <div
                            style="
                                margin-bottom:8px;
                                font-size:23px;
                                font-weight:800;
                            "
                        >

                            Kode Reset Password

                        </div>


                        <div
                            style="
                                font-size:14px;
                                line-height:1.7;
                                color:#687083;
                            "
                        >

                            Halo {{ $userName }},

                            <br>

                            Kami menerima permintaan untuk
                            mengatur ulang password akun
                            {{ $appName }} Anda.

                            <br><br>

                            Gunakan kode berikut:

                        </div>


                        {{-- OTP --}}
                        <div
                            style="
                                margin:28px 0;
                                text-align:center;
                            "
                        >

                            <div
                                style="
                                    display:inline-block;
                                    padding:18px 28px;
                                    border-radius:14px;
                                    background:#fff1e7;
                                    border:1px solid #ffd9bb;
                                    color:#f36b0a;
                                    font-size:32px;
                                    font-weight:800;
                                    letter-spacing:8px;
                                "
                            >

                                {{ $code }}

                            </div>

                        </div>


                        <div
                            style="
                                font-size:13px;
                                line-height:1.7;
                                color:#687083;
                            "
                        >

                            Kode berlaku selama

                            <strong
                                style="color:#172033;"
                            >
                                {{ $expiresIn }} menit
                            </strong>.

                        </div>


                        {{-- SECURITY --}}
                        <div
                            style="
                                margin-top:18px;
                                padding:14px 15px;
                                border-radius:10px;
                                background:#fff8f2;
                                color:#7d8494;
                                font-size:12px;
                                line-height:1.6;
                            "
                        >

                            Jangan bagikan kode ini kepada
                            siapa pun.

                            <br>

                            Tim {{ $appName }}
                            tidak akan meminta kode ini
                            melalui chat atau telepon.

                        </div>


                        <div
                            style="
                                margin-top:24px;
                                font-size:12px;
                                line-height:1.6;
                                color:#9aa0ad;
                            "
                        >

                            Bila Anda tidak meminta
                            reset password, abaikan email ini.

                        </div>

                    </td>

                </tr>


                {{-- FOOTER --}}
                <tr>

                    <td
                        style="
                            padding:20px 32px;
                            background:#faf9f7;
                            border-top:1px solid #eee7df;
                            text-align:center;
                            font-size:11px;
                            color:#9aa0ad;
                        "
                    >

                        Email otomatis
                        {{ $appName }}.

                    </td>

                </tr>

            </table>

        </td>

    </tr>

</table>

</body>

</html>