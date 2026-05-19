<?php

namespace App\Models;

use Database\Factories\PamphletQrCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pamphlet_id', 'target_url', 'image_path', 'generated_at'])]
class PamphletQrCode extends Model
{
    /** @use HasFactory<PamphletQrCodeFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function pamphlet(): BelongsTo
    {
        return $this->belongsTo(Pamphlet::class);
    }
}
