<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Read-only platform pages; routes require the existing Super Admin role. */
class SuperAdminPageController extends Controller
{
    public function subscriptions(): View
    {
        return view('super-admin.subscriptions', [
            'businesses' => Business::orderBy('name')->paginate(20),
            'packageNames' => Package::pluck('name', 'code'),
            'orders' => \App\Models\SubscriptionOrder::withoutGlobalScope('business')->with('business')->latest('id')->paginate(20, ['*'], 'orders_page'),
        ]);
    }

    public function users(Request $request): View
    {
        $ownersOnly = $request->routeIs('super-admin.owners.*');
        $users = User::withoutGlobalScope('business')->with(['business', 'roles'])
            ->when($ownersOnly, fn ($query) => $query->whereHas('roles', fn ($roles) =>
                $roles->where('guard_name', 'web')->whereIn('name', ['Atasan', 'Pemilik'])))
            ->orderBy('name')->paginate(20);

        return view('super-admin.users', compact('users', 'ownersOnly'));
    }

    public function activities(): View
    {
        // No platform audit log exists yet; never fabricate activity records.
        return view('super-admin.activities');
    }

    public function notifications(Request $request): View
    {
        return view('super-admin.notifications', [
            'notifications' => $request->user()->notifications()->latest()->paginate(15),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('super-admin.profile', ['user' => $request->user()]);
    }

    public function settings(): View
    {
        return view('super-admin.settings');
    }
}
