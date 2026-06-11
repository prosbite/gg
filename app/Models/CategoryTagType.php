<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryTagType extends Model
{
    protected $table = 'category_tagtypes';

    protected $fillable = [
        'category_id',
        'tag_type_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tagType(): BelongsTo
    {
        return $this->belongsTo(TagType::class);
    }
}