<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalRequest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'contact_number',
        'fb_profile_link',
        'item_code_requested',
        'requested_pickup_date',
        'images',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_pickup_date' => 'date',
            'images' => 'array',
        ];
    }
}