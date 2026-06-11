<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'social_media_link' => ['nullable', 'url'],
            'identification' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'detail_info' => ['nullable', 'array'],
            'is_blacklisted' => ['nullable', 'boolean'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}

