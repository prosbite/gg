<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',
        'prefix',
        'default_rental_fee',
        'default_security_deposit',
    ];

    protected $casts = [
        'default_rental_fee' => 'decimal:2',
        'default_security_deposit' => 'decimal:2',
    ];

    public function tagTypes(): BelongsToMany
    {
        return $this->belongsToMany(TagType::class, 'category_tag_type')->withTimestamps();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
