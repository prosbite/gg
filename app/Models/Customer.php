<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'contact_number',
        'email',
        'address',
        'affiliation',
        'social_media_link',
        'identification',
        'detail_info',
        'is_blacklisted',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'detail_info' => 'array',
            'is_blacklisted' => 'boolean',
        ];
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }
}
