<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'prefix' => ['required', 'string', 'max:10', 'unique:categories,prefix'],
            'default_rental_fee' => ['required', 'numeric', 'min:0'],
            'default_security_deposit' => ['required', 'numeric', 'min:0'],
            'tag_types' => ['nullable', 'array'],
            'tag_types.*' => ['integer', 'exists:tag_types,id'],
        ];
    }
}
