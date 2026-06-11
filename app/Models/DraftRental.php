<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftRental extends Model
{
    protected $fillable = [
        'receipt_number',
        'customer_id',
        'pickup_date',
        'return_date',
        'rental_items',
        'payments',
        'total_amount',
        'notes',
        'status',
        'rental_status',
    ];

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'return_date' => 'date',
            'rental_items' => 'array',
            'payments' => 'array',
            'total_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function draftImages(): HasMany
    {
        return $this->hasMany(DraftImage::class);
    }
}