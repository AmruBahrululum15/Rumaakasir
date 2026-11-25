<?php

namespace App\Http\Controllers;

use App\Models\StoreRegistration;
use App\Services\StoreRegistrationService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRegistrationReviewController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('business_type') && is_string($request->input('business_type'))) $request->merge(['business_type' => app(\App\Services\BusinessModules::class)->normalize($request->input('business_type'))]);
        $filters = $request->validate(['status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'requested_plan' => ['nullable', Rule::in(['free', 'premium'])],
            'business_type' => ['nullable', Rule::in(array_keys(config('business_modules.types')))], 'search' => ['nullable', 'string', 'max:255']]);
        $query = StoreRegistration::query();
        foreach (['status', 'requested_plan'] as $key) if (! empty($filters[$key])) $query->where($key, $filters[$key]);
        if (! empty($filters['business_type'])) {
            $values = [$filters['business_type'], ...array_keys(config('business_modules.aliases'), $filters['business_type'])];
            $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(business_type)'), $values);
        }
        if (! empty($filters['search'])) $query->where(function ($q) use ($filters) {
            foreach (['store_name', 'owner_name', 'owner_email', 'store_email'] as $field) $q->orWhere($field, 'like', '%'.$filters['search'].'%');
        });
        return view('super-admin.store-registrations.index', ['registrations' => $query->latest()->paginate(15)->withQueryString()]);
    }
    public function show(StoreRegistration $registration) { return view('super-admin.store-registrations.show', compact('registration')); }
    public function approve(Request $request, StoreRegistration $registration, StoreRegistrationService $service)
    {
        try { $service->review($registration, $request->user(), true); }
        catch (UniqueConstraintViolationException $exception) { throw ValidationException::withMessages(['owner_username' => 'Username sudah dipakai. Approval dibatalkan tanpa membuat toko.']); }
        return back()->with('success', 'Pengajuan disetujui. Pengiriman email aktivasi telah diproses. Jika email belum diterima, periksa log pengiriman atau kirim ulang tautan.');
    }
    public function reject(Request $request, StoreRegistration $registration, StoreRegistrationService $service)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:2000']]);
        $service->review($registration, $request->user(), false, $data['rejection_reason']);
        return back()->with('success', 'Pengajuan ditolak. Pemberitahuan diproses melalui email.');
    }
    public function resend(StoreRegistration $registration, StoreRegistrationService $service)
    {
        abort_unless($registration->status === 'approved' && ! $registration->activated_at, 409);
        $service->approvalMail($registration);
        return back()->with('success', 'Pengiriman ulang tautan aktivasi telah diproses.');
    }
}
