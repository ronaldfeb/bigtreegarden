<?php

namespace App\Models;

use Database\Factories\BackgroundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'background_collection_id',
    'name',
    'asset_path',
    'thumbnail_path',
    'is_active',
    'sort_order',
])]
class Background extends Model
{
    /** @use HasFactory<BackgroundFactory> */
    use HasFactory, HasUuids;

    public function backgroundCollection(): BelongsTo
    {
        return $this->belongsTo(BackgroundCollection::class);
    }
}
