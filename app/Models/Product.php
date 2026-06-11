<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'item_code',
        'name',
        'category_id',
        'custom_rental_fee',
        'custom_security_deposit',
        'status',
        'description',
        'specifics',
        'is_active',
        'is_draft',
    ];

    protected function casts(): array
    {
        return [
            'custom_rental_fee' => 'decimal:2',
            'custom_security_deposit' => 'decimal:2',
            'specifics' => 'array',
            'is_active' => 'boolean',
            'is_draft' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag')->withTimestamps();
    }
}