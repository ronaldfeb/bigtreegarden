<?php

namespace App\Models;

use Database\Factories\HelpCenterTopicFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'description',
    'icon',
    'sort_order',
    'is_active',
])]
class HelpCenterTopic extends Model
{
    /** @use HasFactory<HelpCenterTopicFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function articles(): HasMany
    {
        return $this->hasMany(HelpCenterArticle::class)->orderBy('sort_order');
    }

    public function helpCenterArticles(): HasMany
    {
        return $this->articles();
    }

    /**
     * @param  Builder<HelpCenterTopic>  $query
     * @return Builder<HelpCenterTopic>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
