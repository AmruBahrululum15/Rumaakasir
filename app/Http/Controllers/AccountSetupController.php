<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AccountSetupController extends Controller
{
    public function pending(): View
    {
        return view('auth.pending');
    }

    public function editFirstLogin(Request $request): View|RedirectResponse
    {
        if (! $request->user()->must_change_credentials) {
            return redirect()->route('dashboard');
        }

        return view('auth.first-login');
    }

    public function updateFirstLogin(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->must_change_credentials, 403);

        $validated = $request->validate([
            'username' => ['required', 'string', 'alpha_dash', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->forceFill([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'must_change_credentials' => false,
        ])->save();

        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Pengaturan akun berhasil. Selamat datang di POS Enterprise.');
    }
}
