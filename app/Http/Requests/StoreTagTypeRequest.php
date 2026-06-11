<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust policy if needed
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:tag_types,name'],
        ];
    }
}
