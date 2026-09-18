<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\RegistrationDecisionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserApprovalController extends Controller
{
    private const ROLES = ['Admin', 'Gudang', 'Kasir', 'Atasan'];

    public function index(): View
    {
        $users = User::query()->where('status', 'pending')->latest()->paginate(15);
        return view('users.approvals', compact('users'));
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->guardTarget($request, $user);
        abort_unless($user->status === 'pending', 422, 'Hanya akun pending yang dapat disetujui.');
        abort_unless(in_array($user->requested_role, self::ROLES, true), 422, 'Role yang diminta tidak valid.');

        $role = Role::where('guard_name', 'web')->where('name', $user->requested_role)->firstOrFail();
        $user->syncRoles([$role]);
        $user->update(['status' => 'approved', 'is_active' => true]);
        $user->notify(new RegistrationDecisionNotification(true));

        return back()->with('success', 'Akun berhasil disetujui.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->guardTarget($request, $user);
        abort_unless($user->status === 'pending', 422, 'Hanya akun pending yang dapat ditolak.');
        $user->update(['status' => 'rejected', 'is_active' => false]);
        $user->notify(new RegistrationDecisionNotification(false));

        return back()->with('success', 'Registrasi akun ditolak.');
    }

    private function guardTarget(Request $request, User $user): void
    {
        abort_if($request->user()->is($user), 403, 'Anda tidak dapat mengubah status akun sendiri.');
    }
}
