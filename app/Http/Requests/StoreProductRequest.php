<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDraft = $this->boolean('is_draft');

        return [
            'item_code' => $isDraft
                ? ['nullable', 'string', 'max:50', 'unique:products,item_code']
                : ['required', 'string', 'max:50', 'unique:products,item_code'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => $isDraft
                ? ['nullable', 'integer', 'exists:categories,id']
                : ['required', 'integer', 'exists:categories,id'],
            'custom_rental_fee' => ['nullable', 'numeric', 'min:0'],
            'custom_security_deposit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:available,rented,maintenance,retired'],
            'description' => ['nullable', 'string'],
            'specifics' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'is_draft' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'images' => ['nullable', 'array'],
            'images.*.file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'images.*.label' => ['nullable', 'string', 'max:255'],
        ];
    }
}
