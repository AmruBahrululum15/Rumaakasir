<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', ['registrationRoles' => ['Admin', 'Gudang', 'Kasir', 'Atasan']]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // This is an allow-list, never a client-controlled role assignment.
            'requested_role' => ['required', 'string', Rule::in(['Admin', 'Gudang', 'Kasir', 'Atasan'])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'requested_role' => $request->requested_role,
            'status' => 'pending',
            'is_active' => false,
        ]);

        return redirect()->route('account.pending')
            ->with('status', 'Registrasi berhasil. Akun Anda sedang menunggu persetujuan Atasan.');
    }
}
