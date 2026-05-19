<?php

namespace App\Models;

use Database\Factories\MemorialAdditionalSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['memorial_page_id', 'title', 'content', 'sort_order'])]
class MemorialAdditionalSection extends Model
{
    /** @use HasFactory<MemorialAdditionalSectionFactory> */
    use HasFactory, HasUuids;

    public function memorialPage(): BelongsTo
    {
        return $this->belongsTo(MemorialPage::class);
    }
}
