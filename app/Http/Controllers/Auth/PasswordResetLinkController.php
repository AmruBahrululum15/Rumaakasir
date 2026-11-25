<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['username' => ['required', 'string', 'max:100']]);
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $username = trim($request->string('username')->toString());
                \App\Models\User::where('username', $username)->lockForUpdate()->first();
                Password::sendResetLink(['username' => $username]);
            });
        } catch (\Throwable $exception) {
            // SMTP exceptions may include credentials or recipient data; do not report raw messages.
            \Illuminate\Support\Facades\Log::warning('Password reset delivery failed', ['exception_type' => get_class($exception)]);
        }
        return back()->with('status', 'Jika akun tersebut valid, instruksi reset password akan dikirim.');
    }
}
