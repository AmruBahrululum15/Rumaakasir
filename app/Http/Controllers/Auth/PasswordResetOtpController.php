<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Throwable;

class PasswordResetOtpController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS = 5;

    private function cacheKey(string $email): string
    {
        return 'password-reset-otp:' .
            hash(
                'sha256',
                strtolower(trim($email))
            );
    }

    private function cacheStore()
    {
        /*
        |--------------------------------------------------------------------------
        | FILE CACHE
        |--------------------------------------------------------------------------
        | Sengaja memakai file agar tidak membutuhkan migration tambahan.
        */
        return Cache::store('file');
    }

    private function saveOtp(
        string $email,
        array $data
    ): void {

        $expiresAt =
            (int) (
                $data['expires_at']
                ?? now()
                    ->addMinutes(self::OTP_TTL_MINUTES)
                    ->timestamp
            );

        $seconds =
            max(
                1,
                $expiresAt - now()->timestamp
            );

        $this->cacheStore()->put(
            $this->cacheKey($email),
            $data,
            $seconds
        );
    }

    private function findUser(
        string $email
    ): ?User {

        return User::query()
            ->whereRaw(
                'LOWER(email) = ?',
                [strtolower(trim($email))]
            )
            ->first();
    }

    private function maskEmail(
        string $email
    ): string {

        $parts =
            explode(
                '@',
                $email,
                2
            );

        if (count($parts) !== 2) {
            return $email;
        }

        [$local, $domain] = $parts;

        if (strlen($local) <= 2) {

            return
                substr($local, 0, 1)
                .
                str_repeat(
                    '*',
                    max(
                        1,
                        strlen($local) - 1
                    )
                )
                .
                '@'
                .
                $domain;
        }

        return
            substr($local, 0, 1)
            .
            str_repeat(
                '*',
                max(
                    2,
                    strlen($local) - 2
                )
            )
            .
            substr($local, -1)
            .
            '@'
            .
            $domain;
    }

    private function generateAndSendCode(
        User $user
    ): void {

        $email =
            strtolower(
                trim($user->email)
            );

        $code =
            (string) random_int(
                100000,
                999999
            );

        $now = now();

        $expiresAt =
            $now->copy()
                ->addMinutes(
                    self::OTP_TTL_MINUTES
                );

        $data = [

            'email' =>
                $email,

            'code_hash' =>
                Hash::make($code),

            'attempts' =>
                0,

            'expires_at' =>
                $expiresAt->timestamp,

            'resend_at' =>
                $now->copy()
                    ->addSeconds(
                        self::RESEND_COOLDOWN_SECONDS
                    )
                    ->timestamp,

        ];

        $this->saveOtp(
            $email,
            $data
        );

        Notification::route(
            'mail',
            [
                $user->email =>
                    $user->name ?: 'Pengguna',
            ]
        )->notify(
            new PasswordResetCodeNotification(
                $code,
                $user->name ?: 'Pengguna'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN LUPA PASSWORD
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view(
            'auth.forgot-password'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | KIRIM KODE
    |--------------------------------------------------------------------------
    */
    public function sendCode(
        Request $request
    ) {

        $validated =
            $request->validate([
                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],
            ]);

        $email =
            strtolower(
                trim($validated['email'])
            );

        $user =
            $this->findUser(
                $email
            );

        /*
        |--------------------------------------------------------------------------
        | JANGAN MEMBOCORKAN EMAIL ADA / TIDAK
        |--------------------------------------------------------------------------
        */
        if (!$user) {

            return back()
                ->withInput()
                ->with(
                    'status',
                    'Jika email tersebut terdaftar, kode reset akan dikirim ke email tersebut.'
                );
        }

        try {

            $this->generateAndSendCode(
                $user
            );

            $request->session()->put([

                'password_reset_email' =>
                    $email,

                'password_reset_verified' =>
                    false,

            ]);

            return redirect()
                ->route(
                    'password.otp'
                )
                ->with(
                    'status',
                    'Kode verifikasi 6 digit telah dikirim ke email Anda.'
                );

        } catch (Throwable $e) {

            report($e);

            $this->cacheStore()->forget(
                $this->cacheKey($email)
            );

            return back()
                ->withInput()
                ->withErrors([
                    'email' =>
                        'Kode reset gagal dikirim. Periksa konfigurasi email aplikasi.',
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN VERIFIKASI
    |--------------------------------------------------------------------------
    */
    public function showVerify(
        Request $request
    ) {

        $email =
            $request->session()->get(
                'password_reset_email'
            );

        if (!$email) {

            return redirect()
                ->route(
                    'password.request'
                );
        }

        $data =
            $this->cacheStore()->get(
                $this->cacheKey($email)
            );

        $resendIn = 0;

        if (is_array($data)) {

            $resendIn =
                max(
                    0,
                    (int) (
                        $data['resend_at']
                        ?? 0
                    )
                    -
                    now()->timestamp
                );
        }

        return view(
            'auth.verify-reset-code',
            [
                'email' =>
                    $email,

                'maskedEmail' =>
                    $this->maskEmail(
                        $email
                    ),

                'resendIn' =>
                    $resendIn,
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFIKASI KODE
    |--------------------------------------------------------------------------
    */
    public function verifyCode(
        Request $request
    ) {

        $email =
            $request->session()->get(
                'password_reset_email'
            );

        if (!$email) {

            return redirect()
                ->route(
                    'password.request'
                );
        }

        $validated =
            $request->validate([
                'code' => [
                    'required',
                    'digits:6',
                ],
            ]);

        $key =
            $this->cacheKey(
                $email
            );

        $data =
            $this->cacheStore()->get(
                $key
            );

        if (!is_array($data)) {

            return back()
                ->withErrors([
                    'code' =>
                        'Kode telah kedaluwarsa. Silakan minta kode baru.',
                ]);
        }

        if (
            now()->timestamp
            >
            (int) (
                $data['expires_at']
                ?? 0
            )
        ) {

            $this->cacheStore()
                ->forget($key);

            return back()
                ->withErrors([
                    'code' =>
                        'Kode telah kedaluwarsa. Silakan minta kode baru.',
                ]);
        }

        $attempts =
            (int) (
                $data['attempts']
                ?? 0
            );

        if (
            $attempts
            >=
            self::MAX_ATTEMPTS
        ) {

            $this->cacheStore()
                ->forget($key);

            return back()
                ->withErrors([
                    'code' =>
                        'Batas percobaan tercapai. Silakan minta kode baru.',
                ]);
        }

        $valid =
            Hash::check(
                $validated['code'],
                $data['code_hash'] ?? ''
            );

        if (!$valid) {

            $data['attempts'] =
                $attempts + 1;

            $this->saveOtp(
                $email,
                $data
            );

            $remaining =
                max(
                    0,
                    self::MAX_ATTEMPTS
                    -
                    $data['attempts']
                );

            return back()
                ->withErrors([
                    'code' =>
                        "Kode salah. Sisa percobaan: {$remaining}.",
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | KODE BENAR
        |--------------------------------------------------------------------------
        */
        $request->session()->put([

            'password_reset_verified' =>
                true,

            'password_reset_verified_at' =>
                now()->timestamp,

        ]);

        $data['verified'] =
            true;

        $this->saveOtp(
            $email,
            $data
        );

        return redirect()
            ->route(
                'password.otp.reset'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | KIRIM ULANG
    |--------------------------------------------------------------------------
    */
    public function resendCode(
        Request $request
    ) {

        $email =
            $request->session()->get(
                'password_reset_email'
            );

        if (!$email) {

            return redirect()
                ->route(
                    'password.request'
                );
        }

        $key =
            $this->cacheKey(
                $email
            );

        $data =
            $this->cacheStore()->get(
                $key
            );

        if (is_array($data)) {

            $remaining =
                max(
                    0,
                    (int) (
                        $data['resend_at']
                        ?? 0
                    )
                    -
                    now()->timestamp
                );

            if ($remaining > 0) {

                return back()
                    ->withErrors([
                        'code' =>
                            "Tunggu {$remaining} detik sebelum mengirim ulang kode.",
                    ]);
            }
        }

        $user =
            $this->findUser(
                $email
            );

        if (!$user) {

            return redirect()
                ->route(
                    'password.request'
                );
        }

        try {

            $this->generateAndSendCode(
                $user
            );

            $request->session()->put(
                'password_reset_verified',
                false
            );

            return back()
                ->with(
                    'status',
                    'Kode baru telah dikirim ke email Anda.'
                );

        } catch (Throwable $e) {

            report($e);

            return back()
                ->withErrors([
                    'code' =>
                        'Kode gagal dikirim ulang. Periksa konfigurasi email.',
                ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HALAMAN PASSWORD BARU
    |--------------------------------------------------------------------------
    */
    public function showReset(
        Request $request
    ) {

        $verified =
            $request->session()->get(
                'password_reset_verified',
                false
            );

        $verifiedAt =
            (int) $request->session()->get(
                'password_reset_verified_at',
                0
            );

        if (
            !$verified
            ||
            !$verifiedAt
            ||
            (
                now()->timestamp
                -
                $verifiedAt
            )
            >
            self::OTP_TTL_MINUTES * 60
        ) {

            $request->session()->forget([

                'password_reset_email',

                'password_reset_verified',

                'password_reset_verified_at',

            ]);

            return redirect()
                ->route(
                    'password.request'
                )
                ->withErrors([
                    'email' =>
                        'Sesi reset password telah kedaluwarsa.',
                ]);
        }

        return view(
            'auth.reset-password-code'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function updatePassword(
        Request $request
    ) {

        $email =
            $request->session()->get(
                'password_reset_email'
            );

        $verified =
            $request->session()->get(
                'password_reset_verified',
                false
            );

        $verifiedAt =
            (int) $request->session()->get(
                'password_reset_verified_at',
                0
            );

        if (
            !$email
            ||
            !$verified
            ||
            !$verifiedAt
            ||
            (
                now()->timestamp
                -
                $verifiedAt
            )
            >
            self::OTP_TTL_MINUTES * 60
        ) {

            $request->session()->forget([

                'password_reset_email',

                'password_reset_verified',

                'password_reset_verified_at',

            ]);

            return redirect()
                ->route(
                    'password.request'
                )
                ->withErrors([
                    'email' =>
                        'Sesi reset password telah kedaluwarsa.',
                ]);
        }

        $request->validate(
            [
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'password.required' =>
                    'Password baru wajib diisi.',

                'password.min' =>
                    'Password minimal 8 karakter.',

                'password.confirmed' =>
                    'Konfirmasi password tidak cocok.',
            ]
        );

        $user =
            $this->findUser(
                $email
            );

        if (!$user) {

            return redirect()
                ->route(
                    'password.request'
                )
                ->withErrors([
                    'email' =>
                        'Akun tidak ditemukan.',
                ]);
        }

        $user->password =
            Hash::make(
                $request->password
            );

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | HAPUS OTP
        |--------------------------------------------------------------------------
        */
        $this->cacheStore()
            ->forget(
                $this->cacheKey(
                    $email
                )
            );

        /*
        |--------------------------------------------------------------------------
        | HAPUS SESSION RESET
        |--------------------------------------------------------------------------
        */
        $request->session()->forget([

            'password_reset_email',

            'password_reset_verified',

            'password_reset_verified_at',

        ]);

        return redirect()
            ->route('login')
            ->with(
                'status',
                'Password berhasil diubah. Silakan login menggunakan password baru.'
            );
    }
}