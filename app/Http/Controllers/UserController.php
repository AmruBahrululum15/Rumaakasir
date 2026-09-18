<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user.
     */
    public function index(Request $request)
    {
        $query = User::query();

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_telepon', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER ROLE
        |--------------------------------------------------------------------------
        */
        if ($request->filled('role')) {
            $role = $request->role;

            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', 1);
            }

            if ($request->status === 'nonaktif') {
                $query->where('is_active', 0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | DATA USER
        |--------------------------------------------------------------------------
        */
        $users = $query
            ->with('roles')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | ROLE
        |--------------------------------------------------------------------------
        */
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('users.index', compact(
            'users',
            'roles'
        ));
    }

    /**
     * Form tambah user.
     */
    public function create()
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
                'unique:users,username',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],

            'nik' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,nik',
            ],

            'no_telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'alamat' => [
                'nullable',
                'string',
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'role' => [
                'required',
                'exists:roles,name',
            ],

            'is_active' => [
                'nullable',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.min' => 'Password minimal 6 karakter.',
            'nik.unique' => 'NIK sudah digunakan.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        |
        | Jika checkbox/toggle dicentang = 1
        | Jika tidak dicentang = 0
        |
        */
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        /*
        |--------------------------------------------------------------------------
        | FOTO
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('foto')) {
            $validated['foto'] = $request
                ->file('foto')
                ->store('users', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE TIDAK DISIMPAN DI TABLE USERS
        |--------------------------------------------------------------------------
        */
        $role = $validated['role'];

        unset($validated['role']);

        /*
        |--------------------------------------------------------------------------
        | CREATE USER
        |--------------------------------------------------------------------------
        */
        $user = User::create($validated);

        /*
        |--------------------------------------------------------------------------
        | ASSIGN ROLE
        |--------------------------------------------------------------------------
        */
        $user->assignRole($role);

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail user.
     */
    public function show(User $user)
    {
        $user->load('roles');

        return view('users.show', compact('user'));
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        $user->load('roles');

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('users.edit', compact(
            'user',
            'roles'
        ));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
                Rule::unique('users', 'username')
                    ->ignore($user->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:6',
                'confirmed',
            ],

            'nik' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'nik')
                    ->ignore($user->id),
            ],

            'no_telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'alamat' => [
                'nullable',
                'string',
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'role' => [
                'required',
                'exists:roles,name',
            ],

            'is_active' => [
                'nullable',
            ],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.min' => 'Password minimal 6 karakter.',
            'nik.unique' => 'NIK sudah digunakan.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Foto harus JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'role.required' => 'Role wajib dipilih.',
        ]);

        // A user-management permission is never a reason for a user to
        // elevate their own role or disable/enable their own account.
        if ($request->user()->is($user) && (
            $validated['role'] !== $user->getRoleNames()->first()
            || $request->has('is_active') !== $user->is_active
        )) {
            abort(403, 'Anda tidak dapat mengubah role atau status akun sendiri.');
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        /*
        |--------------------------------------------------------------------------
        | ROLE
        |--------------------------------------------------------------------------
        */
        $role = $validated['role'];

        unset($validated['role']);

        /*
        |--------------------------------------------------------------------------
        | PASSWORD
        |--------------------------------------------------------------------------
        |
        | Kalau password kosong, jangan ubah password lama.
        |
        */
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        /*
        |--------------------------------------------------------------------------
        | FOTO BARU
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('foto')) {

            /*
            | Hapus foto lama
            */
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }

            /*
            | Simpan foto baru
            */
            $validated['foto'] = $request
                ->file('foto')
                ->store('users', 'public');
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE USER
        |--------------------------------------------------------------------------
        */
        $user->update($validated);

        /*
        |--------------------------------------------------------------------------
        | UPDATE ROLE
        |--------------------------------------------------------------------------
        */
        $user->syncRoles([$role]);

        return redirect()
            ->route('users.show', $user->id)
            ->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        /*
        | Jangan sampai user yang sedang login terhapus.
        */
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('users.index')
                ->with('error', 'User yang sedang login tidak dapat dihapus.');
        }

        /*
        | Hapus foto.
        */
        if ($user->foto) {
            Storage::disk('public')->delete($user->foto);
        }

        /*
        | Hapus user.
        */
        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
