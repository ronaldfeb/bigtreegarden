<?php

namespace App\Models;

use Database\Factories\MemorialPagePamphletBackgroundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'collection_id',
    'name',
    'image_path',
    'thumbnail_path',
    'sort_order',
    'is_active',
])]
class MemorialPagePamphletBackground extends Model
{
    /** @use HasFactory<MemorialPagePamphletBackgroundFactory> */
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

    public function collection(): BelongsTo
    {
        return $this->belongsTo(MemorialPagePamphletBackgroundCollection::class, 'collection_id');
    }

    public function getAssetPathAttribute(): string
    {
        return $this->image_path;
    }
}
