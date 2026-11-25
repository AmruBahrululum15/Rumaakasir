<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Tenancy\TenantContext;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('category')->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->where('business_id', app(TenantContext::class)->id())->ignore($id)],
            'description' => 'nullable|string',
            'status' => 'required|boolean',
        ];
    }
}
