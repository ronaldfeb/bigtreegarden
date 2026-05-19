<?php

namespace App\Models;

use Database\Factories\MemorialPageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pamphlet_id', 'funeral_programme', 'obituary', 'hymns', 'gallery_enabled'])]
class MemorialPage extends Model
{
    /** @use HasFactory<MemorialPageFactory> */
    use HasFactory, HasUuids;

    public function pamphlet(): BelongsTo
    {
        return $this->belongsTo(Pamphlet::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(MemorialGalleryImage::class);
    }

    public function additionalSections(): HasMany
    {
        return $this->hasMany(MemorialAdditionalSection::class);
    }
}
