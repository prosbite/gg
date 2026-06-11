<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'customer.first_name' => ['required', 'string', 'max:255'],
            'customer.last_name' => ['required', 'string', 'max:255'],
            'customer.contact_number' => ['required', 'string', 'max:50'],
            'customer.address' => ['required', 'string'],
            'customer.affiliation' => ['nullable', 'string', 'max:255'],
            'customer.social_media_link' => ['nullable', 'string', 'max:255'],

            'pickup_date' => ['required', 'date'],
            'return_date' => ['required', 'date', 'after_or_equal:pickup_date'],
            'internal_notes' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.images' => ['nullable', 'array'],
            'items.*.images.*.file' => ['nullable', 'image', 'max:10240'],
            'items.*.rental_fee' => ['nullable', 'numeric', 'min:0'],
            'items.*.security_deposit' => ['nullable', 'numeric', 'min:0'],

            'payments' => ['nullable', 'array'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.type' => ['required', 'string', 'in:downpayment,balance,security_deposit,penalty'],
            'payments.*.method' => ['required', 'string', 'in:cash,gcash,bank_transfer'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:255'],
            'payments.*.receipts' => ['nullable', 'array'],
            'payments.*.receipts.*.file' => ['nullable', 'image', 'max:10240'],
            'payments.*.receipts.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
