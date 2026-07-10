<?php

namespace App\Models;

use Database\Factories\MemorialPagePamphletBackgroundCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'slug',
    'description',
    'sort_order',
    'is_active',
])]
class MemorialPagePamphletBackgroundCollection extends Model
{
    /** @use HasFactory<MemorialPagePamphletBackgroundCollectionFactory> */
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

    public function backgrounds(): HasMany
    {
        return $this->hasMany(MemorialPagePamphletBackground::class, 'collection_id');
    }
}
