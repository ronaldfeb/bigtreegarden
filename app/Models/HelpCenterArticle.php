<?php

namespace App\Models;

use Database\Factories\HelpCenterArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'help_center_topic_id',
    'author_staff_user_id',
    'title',
    'slug',
    'excerpt',
    'body',
    'status',
    'published_at',
    'view_count',
    'sort_order',
])]
class HelpCenterArticle extends Model
{
    /** @use HasFactory<HelpCenterArticleFactory> */
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

    public function topic(): BelongsTo
    {
        return $this->belongsTo(HelpCenterTopic::class, 'help_center_topic_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'author_staff_user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(HelpCenterCategory::class, 'help_center_article_categories');
    }

    /**
     * @param  Builder<HelpCenterArticle>  $query
     * @return Builder<HelpCenterArticle>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', config('constants.help_center_article.status.published'))
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
