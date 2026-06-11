<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftImage extends Model
{
    protected $fillable = [
        'draft_rental_id',
        'file_path',
        'label',
    ];

    public function draftRental(): BelongsTo
    {
        return $this->belongsTo(DraftRental::class);
    }
}