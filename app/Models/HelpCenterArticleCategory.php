<?php

namespace App\Models;

use Database\Factories\HelpCenterArticleCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'help_center_article_id',
    'help_center_category_id',
])]
class HelpCenterArticleCategory extends Pivot
{
    /** @use HasFactory<HelpCenterArticleCategoryFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'help_center_article_categories';

    public function helpCenterArticle(): BelongsTo
    {
        return $this->belongsTo(HelpCenterArticle::class);
    }

    public function helpCenterCategory(): BelongsTo
    {
        return $this->belongsTo(HelpCenterCategory::class);
    }
}
