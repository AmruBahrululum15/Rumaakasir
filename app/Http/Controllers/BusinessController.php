<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class BusinessController extends Controller
{
    public function index(): View
    {
        return view('businesses.index', [
            'businesses' => Business::query()->with(['owner' => fn ($query) => $query->withoutGlobalScope('business')])->withCount(['users' => fn ($query) => $query->withoutGlobalScope('business')])->orderBy('id')->paginate(15),
            'totalBusinesses' => Business::count(),
            'activeBusinesses' => Business::where('is_active', true)->count(),
            'inactiveBusinesses' => Business::where('is_active', false)->count(),
            'totalUsers' => User::withoutGlobalScope('business')->count(),
            'packageNames' => \App\Models\Package::query()->pluck('name', 'code'),
        ]);
    }

    public function create(): View
    {
        return view('businesses.form', ['business' => new Business()]);
    }

    public function show(Business $business): View
    {
        $business->load(['owner' => fn ($query) => $query->withoutGlobalScope('business')]);
        return view('businesses.show', compact('business'));
    }

    public function status(Request $request, Business $business): RedirectResponse
    {
        $business->update($request->validate(['is_active' => ['required', 'boolean']]));
        return to_route('businesses.index')->with('success', 'Status toko berhasil diperbarui.');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $supervisor = $request->validate([
            'supervisor_name' => ['required', 'string', 'max:255'],
            'supervisor_username' => ['required', 'string', 'alpha_dash', 'max:100', 'unique:users,username'],
            'supervisor_email' => ['required', 'email', 'max:255'],
            'supervisor_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $business = DB::transaction(function () use ($data, $supervisor) {
            $business = Business::create($data);
            $context = app(TenantContext::class);
            $context->set($business);

            try {
                \App\Models\Setting::setValue('app_name', $business->name);
                $owner = User::create([
                    'name' => $supervisor['supervisor_name'],
                    'username' => $supervisor['supervisor_username'],
                    'email' => $supervisor['supervisor_email'],
                    'password' => Hash::make($supervisor['supervisor_password']),
                    'status' => 'approved',
                    'is_active' => true,
                    'must_change_credentials' => true,
                ]);
            } finally {
                $context->clear();
            }

            $owner->assignRole(Role::findOrCreate('Atasan', 'web'));
            $business->forceFill(['owner_id' => $owner->id])->save();

            return $business;
        });

        return redirect()->route('businesses.edit', $business)
            ->with('success', 'Toko dan akun Atasan Utama berhasil ditambahkan.');
    }

    public function edit(Business $business): View
    {
        return view('businesses.form', compact('business'));
    }

    public function update(Request $request, Business $business): RedirectResponse
    {
        $data = $this->validated($request, $business);
        DB::transaction(function () use ($business, $data) {
            $current = Business::lockForUpdate()->findOrFail($business->id);
            $current->fill($data);
            // An explicit platform override returns ownership to the existing
            // manual subscription flow (including unlimited Premium grants).
            if ($current->isDirty(['subscription_plan', 'subscription_status', 'subscription_ends_at'])) {
                $current->subscription_source = null;
            }
            $current->save();
        });

        return redirect()->route('businesses.index')
            ->with('success', 'Data toko berhasil diperbarui.');
    }

    public function activate(Request $request, Business $business): RedirectResponse
    {
        abort_unless($business->allowsOperations(), 422, 'Toko atau langganan tidak aktif tidak dapat dipilih.');

        $request->session()->put('super_admin_business_id', $business->id);

        return redirect()->route('dashboard')
            ->with('success', 'Anda sedang mengelola '.$business->name.'.');
    }

    private function validated(Request $request, ?Business $business = null): array
    {
        if ($request->filled('slug')) {
            $request->merge(['slug' => Str::slug((string) $request->string('slug'))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 'alpha_dash', 'max:255',
                Rule::unique('businesses', 'slug')->ignore($business?->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'subscription_plan' => ['required', Rule::in(['free', 'premium'])],
            'subscription_status' => ['required', Rule::in(['active', 'inactive', 'expired', 'suspended'])],
            'subscription_ends_at' => ['nullable', 'date'],
            'user_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'product_limit' => ['nullable', 'integer', 'min:1', 'max:1000000'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('slug')) {
            $data['slug'] = Str::slug((string) $request->string('slug'));
        } else {
            unset($data['slug']);
        }

        return $data;
    }
}
