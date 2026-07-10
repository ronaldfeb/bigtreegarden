<?php

namespace App\Models;

use Database\Factories\BlogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'blog_category_id',
    'author_staff_user_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'cover_image_path',
    'status',
    'published_at',
    'meta_title',
    'meta_description',
    'view_count',
])]
class Blog extends Model
{
    /** @use HasFactory<BlogFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'author_staff_user_id');
    }

    /**
     * @param  Builder<Blog>  $query
     * @return Builder<Blog>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', config('constants.blog.status.published'))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
