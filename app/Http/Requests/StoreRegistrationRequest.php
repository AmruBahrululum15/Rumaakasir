<?php

namespace App\Http\Requests;

use App\Models\StoreRegistration;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationRequest extends FormRequest
{
    private array $resolvedAddress = [];

    public function registrationData(): array
    {
        $data = $this->validated();
        unset($data['address_mode']);
        return array_merge($data, $this->resolvedAddress);
    }
    public function authorize(): bool { return true; }
    protected function prepareForValidation(): void
    {
        if (!$this->has('address_mode')) {
            $hasIds = collect(['province_id', 'city_id', 'district_id', 'village_id'])->contains(fn($key)=>$this->filled($key));
            $this->merge(['address_mode'=>$hasIds ? 'structured' : 'manual']);
        }
        $type = $this->input('business_type');
        if (is_string($type)) $this->merge(['business_type' => app(\App\Services\BusinessModules::class)->normalize($type)]);
        $this->merge(['owner_email' => strtolower(trim((string) $this->input('owner_email'))),
            'owner_username' => strtolower(trim((string) $this->input('owner_username')))]);
    }
    public function after(): array
    {
        return [function (\Illuminate\Validation\Validator $validator) {
            if ($validator->errors()->has('business_type') || $validator->errors()->has('requested_plan')) return;
            $plan = app(\App\Services\PackageAccess::class)->forType($this->input('business_type'), $this->input('requested_plan'));
            if (!$plan->is_active) $validator->errors()->add('requested_plan', 'Paket belum tersedia untuk jenis usaha ini.');
        }, function (\Illuminate\Validation\Validator $validator) {
            if ($validator->errors()->isNotEmpty()) return;
            if ($this->input('address_mode') === 'manual') {
                $this->resolvedAddress = ['province_id'=>null, 'city_id'=>null, 'district_id'=>null, 'village_id'=>null, 'address_verified'=>false];
                return;
            }
            try {
                $this->resolvedAddress = app(\App\Services\IndonesiaRegions::class)->resolve($this->all());
            } catch (\Illuminate\Validation\ValidationException $exception) {
                foreach ($exception->errors() as $field=>$messages) foreach ($messages as $message) $validator->errors()->add($field, $message);
            } catch (\App\Exceptions\RegionServiceUnavailable $exception) {
                $validator->errors()->add('address_mode', 'Data wilayah sedang tidak tersedia. Pilih input manual untuk melanjutkan; data form Anda tetap tersimpan.');
            }
        }];
    }
    public function rules(): array
    {
        return [
            'submission_token' => ['required', 'uuid', Rule::in([(string) $this->session()->get('store_registration_token')])],
            'store_name' => ['required', 'string', 'max:255'], 'legal_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['required', Rule::in(array_keys(config('business_modules.types')))],
            'business_type_other' => ['required_if:business_type,lainnya', 'nullable', 'string', 'max:255'],
            'store_phone' => ['required', 'string', 'max:50', 'regex:/^[+0-9][0-9 ()-]{6,49}$/'], 'store_email' => ['required', 'email', 'max:255'],
            'address_mode' => ['required', Rule::in(['structured', 'manual'])],
            'province_id' => ['required_if:address_mode,structured', 'prohibited_if:address_mode,manual', 'nullable', 'string', 'regex:'.\App\Services\IndonesiaRegions::PATTERNS['province']],
            'city_id' => ['required_if:address_mode,structured', 'prohibited_if:address_mode,manual', 'nullable', 'string', 'regex:'.\App\Services\IndonesiaRegions::PATTERNS['city']],
            'district_id' => ['required_if:address_mode,structured', 'prohibited_if:address_mode,manual', 'nullable', 'string', 'regex:'.\App\Services\IndonesiaRegions::PATTERNS['district']],
            'village_id' => ['required_if:address_mode,structured', 'prohibited_if:address_mode,manual', 'nullable', 'string', 'regex:'.\App\Services\IndonesiaRegions::PATTERNS['village']],
            'address_verified' => ['prohibited'],
            'address' => ['required', 'string', 'max:2000'], 'province' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:255'],
            'city' => ['required_if:address_mode,manual', 'nullable', 'string', 'max:255'], 'district' => ['nullable', 'string', 'max:255'],
            'village' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{5}$/D'], 'website' => ['nullable', 'url:http,https', 'max:500'],
            'social_media' => ['nullable', 'string', 'max:500'], 'owner_name' => ['required', 'string', 'max:255'],
            'owner_username' => ['required', 'alpha_dash:ascii', 'max:100', 'unique:users,username', Rule::unique('store_registrations', 'owner_username')->where(fn ($query) => $query->where('submission_token', '!=', (string) $this->session()->get('store_registration_token')))],
            'owner_email' => ['required', 'email', 'max:255'],
            'owner_phone' => ['required', 'string', 'max:50', 'regex:/^[+0-9][0-9 ()-]{6,49}$/'], 'owner_position' => ['nullable', 'string', 'max:255'],
            'requested_plan' => ['required', Rule::in(['free', 'premium']), Rule::exists('packages', 'code')],
            'billing_cycle' => ['exclude_unless:requested_plan,premium', 'required', Rule::in(['monthly', 'yearly'])],
            'terms' => ['accepted'],
            'business_id' => ['prohibited'], 'status' => ['prohibited'], 'role' => ['prohibited'],
        ];
    }
}
