<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(): View
    {
        return view('packages.index', ['packages' => Package::query()->orderBy('id')->get(), 'types' => config('business_modules.types')]);
    }

    public function update(Request $request, Package $package): RedirectResponse
    {
        $request->validate(['business_type' => ['sometimes', 'required', \Illuminate\Validation\Rule::in(array_keys(config('business_modules.types')))]]);
        $rules = ['name' => ['required', 'string', 'max:100']];
        $rules['price_monthly'] = ['sometimes', 'required', 'integer', 'min:1', 'max:999999999999'];
        $rules['price_yearly'] = ['sometimes', 'required', 'integer', 'min:1', 'max:999999999999'];
        $rules['is_active'] = ['sometimes', 'required', 'boolean'];
        foreach (Package::LIMITS as $limit) {
            $rules[$limit] = ['present', 'nullable', 'integer', 'min:1', 'max:1000000'];
        }
        foreach (Package::FEATURES as $feature) {
            $rules[$feature] = ['required', 'boolean'];
        }
        if ($request->filled('business_type')) {
            $definitions = app(\App\Services\PremiumFeatures::class)->definitions($request->input('business_type'));
            $rules['feature_statuses'] = ['sometimes', 'array:'.implode(',', array_keys($definitions))];
            foreach ($definitions as $key => $definition) {
                $states = $definition['implemented'] ? ['available','disabled'] : ['disabled'];
                $rules['feature_statuses.'.$key] = isset($definition['inherits']) ? ['prohibited'] : ['sometimes', \Illuminate\Validation\Rule::in($states)];
            }
        }
        $data = $request->validate($rules);
        if ($request->filled('business_type')) {
            foreach (($data['feature_statuses'] ?? []) as $key => $state) {
                if ($flag = ($definitions[$key]['package_flag'] ?? null)) $data[$flag] = $state === 'available';
            }
            $previous = \App\Models\PackageVariant::where('package_id', $package->id)->where('business_type', $request->input('business_type'))->first()?->settings ?? [];
            if (isset($data['feature_statuses'])) $data['feature_statuses'] = array_replace($previous['feature_statuses'] ?? [], $data['feature_statuses']);
            $data = array_replace($previous, $data);
            \App\Models\PackageVariant::updateOrCreate(
                ['package_id' => $package->id, 'business_type' => $request->input('business_type')], ['settings' => $data]);
        } else {
            $package->update($data);
        }

        return redirect()->route('packages.index')->with('success', 'Fitur dan paket berhasil diperbarui. Perubahan langsung berlaku untuk toko pada paket ini.');
    }
}
