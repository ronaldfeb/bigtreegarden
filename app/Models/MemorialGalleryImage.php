<?php

namespace App\Models;

use Database\Factories\MemorialGalleryImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['memorial_page_id', 'image_path', 'caption', 'sort_order'])]
class MemorialGalleryImage extends Model
{
    /** @use HasFactory<MemorialGalleryImageFactory> */
    use HasFactory, HasUuids;

    public function memorialPage(): BelongsTo
    {
        return $this->belongsTo(MemorialPage::class);
    }
}
