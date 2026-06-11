<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'contact_number' => ['sometimes', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'affiliation' => ['nullable', 'string', 'max:255'],
            'social_media_link' => ['nullable', 'url'],
            'identification' => ['nullable', 'string', 'max:255'],
            'detail_info' => ['nullable', 'array'],
            'is_blacklisted' => ['boolean'],
            'admin_notes' => ['nullable', 'string'],
        ];
    }
}
