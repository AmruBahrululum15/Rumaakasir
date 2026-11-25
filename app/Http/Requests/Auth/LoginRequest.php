<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->string('login')->trim()->toString();

        $field = 'username';
        // Preserve the existing Super Admin email login only when unambiguous.
        if (! \App\Models\User::where('username', $login)->exists() && filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $matches = \App\Models\User::where('email', $login)->limit(2)->get();
            if ($matches->count() === 1 && $matches->first()->hasRole('Super Admin')) {
                $field = 'id';
                $login = $matches->first()->id;
            }
        }

        if (! Auth::attempt([
            $field => $login,
            'password' => $this->string('password')->toString(),
        ], $this->boolean('remember'))) {

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Username atau password yang Anda masukkan salah.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        /**
         * Cek status akun setelah authentication berhasil.
         */
        $user = Auth::user();

        if ($user->status === 'pending') {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Akun Anda masih menunggu persetujuan Atasan.',
            ]);
        }

        if ($user->status === 'rejected') {
            Auth::logout();
            throw ValidationException::withMessages([
                'login' => 'Registrasi akun Anda ditolak.',
            ]);
        }

        if (! $user->is_active) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn(
            $this->throttleKey()
        );

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower($this->string('login')->toString())
            . '|' .
            $this->ip()
        );
    }
}
