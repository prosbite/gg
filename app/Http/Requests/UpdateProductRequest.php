<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_code' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'item_code')->ignore($this->route('product')),
            ],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'name')->ignore($this->route('product')),
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'custom_rental_fee' => ['nullable', 'numeric', 'min:0'],
            'custom_security_deposit' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:available,rented,maintenance,retired'],
            'description' => ['nullable', 'string'],
            'specifics' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
            'new_images' => ['nullable', 'array'],
            'new_images.*.file' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'new_images.*.label' => ['nullable', 'string', 'max:255'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ];
    }
}
