<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\MemorialPageImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'memorial_page_id',
    'image_path',
    'caption',
    'sort_order',
])]
class MemorialPageImage extends Model
{
    /** @use HasFactory<MemorialPageImageFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function memorialPage(): BelongsTo
    {
        return $this->belongsTo(MemorialPage::class);
    }
}
