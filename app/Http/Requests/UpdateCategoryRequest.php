<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'name' => "required|max:100|unique:categories,name,$id",
            'description' => 'nullable|max:500',
            'status' => 'required|boolean',
        ];
    }
}