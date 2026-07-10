<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\MemorialPagePamphletStyleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'pamphlet_id',
    'font_family',
    'is_bold',
    'is_italic',
    'date_format',
    'text_color',
])]
class MemorialPagePamphletStyle extends Model
{
    /** @use HasFactory<MemorialPagePamphletStyleFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'is_bold' => 'boolean',
            'is_italic' => 'boolean',
        ];
    }

    public function pamphlet(): BelongsTo
    {
        return $this->belongsTo(MemorialPagePamphlet::class, 'pamphlet_id');
    }
}
