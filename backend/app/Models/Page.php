<?php

namespace App\Models;

use App\Enums\BaseStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'image',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'slug',
        'category_id',
        'tags',
        'status',
    ];

    protected $attributes = [
        'status' => BaseStatusEnum::DRAFT,
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'tags' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
