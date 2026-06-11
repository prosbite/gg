<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
            'prefix' => [
                'required', 'string', 'max:10',
                Rule::unique('categories', 'prefix')->ignore($this->route('category')),
            ],
            'default_rental_fee' => ['required', 'numeric', 'min:0'],
            'default_security_deposit' => ['required', 'numeric', 'min:0'],
            'tag_types' => ['nullable', 'array'],
            'tag_types.*' => ['integer', 'exists:tag_types,id'],
        ];
    }
}
