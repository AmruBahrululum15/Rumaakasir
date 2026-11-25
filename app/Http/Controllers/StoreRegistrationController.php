<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\{Package, StoreRegistration, User};
use App\Services\StoreRegistrationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreRegistrationController extends Controller
{
    public function create(Request $request)
    {
        if (! $request->session()->has('store_registration_token')) $request->session()->put('store_registration_token', (string) Str::uuid());
        return view('store-registration.create', ['packages' => Package::whereIn('code', ['free', 'premium'])->get(), 'types' => config('business_modules.types')]);
    }

    public function store(StoreRegistrationRequest $request, StoreRegistrationService $service)
    {
        $data = $request->registrationData();
        unset($data['terms']);
        $data['billing_cycle'] = $data['requested_plan'] === 'premium' ? $data['billing_cycle'] : null;
        try {
            $registration = StoreRegistration::firstOrCreate(['submission_token' => $data['submission_token']],
                [...$data, 'status' => 'pending', 'terms_accepted_at' => now()]);
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['owner_username' => 'Username sudah memiliki pengajuan. Hubungi pengelola untuk status pengajuan.']);
        }
        if ($registration->wasRecentlyCreated) $service->received($registration);
        return to_route('store-registration.success');
    }

    public function activate(StoreRegistration $registration)
    {
        abort_unless($registration->status === 'approved' && ! $registration->activated_at, 410, 'Tautan aktivasi sudah digunakan atau tidak berlaku. Silakan login atau hubungi pengelola.');
        return view('store-registration.activate', compact('registration'));
    }

    public function activateAccount(Request $request, StoreRegistration $registration)
    {
        $owner = DB::transaction(function () use ($registration) {
            $current = StoreRegistration::lockForUpdate()->findOrFail($registration->id);
            abort_unless($current->status === 'approved' && ! $current->activated_at, 410);
            $owner = User::withoutGlobalScope('business')->lockForUpdate()->findOrFail($current->owner_id);
            abort_unless($owner->status === 'approved' && $owner->is_active && $owner->must_change_credentials && $owner->business->allowsOperations(), 403);
            $current->update(['activated_at' => now()]);
            return $owner;
        });
        Auth::login($owner);
        $request->session()->regenerate();
        return to_route('first-login.edit');
    }
}
