<?php

namespace App\Models;

use Database\Factories\PamphletStyleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pamphlet_id', 'font_family', 'is_bold', 'is_italic', 'date_format'])]
class PamphletStyle extends Model
{
    /** @use HasFactory<PamphletStyleFactory> */
    use HasFactory, HasUuids;

    public function pamphlet(): BelongsTo
    {
        return $this->belongsTo(Pamphlet::class);
    }
}
