<?php

namespace App\Models;

use Database\Factories\AmbassadorImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['ambassador_id', 'image_path', 'caption', 'sort_order'])]
class AmbassadorImage extends Model
{
    /** @use HasFactory<AmbassadorImageFactory> */
    use HasFactory, HasUuids;

    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(Ambassador::class);
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'assets/')) {
            return asset($this->image_path);
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
