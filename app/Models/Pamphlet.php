<?php

namespace App\Models;

use Database\Factories\PamphletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'background_id',
    'status',
    'heading',
    'person_full_name',
    'date_of_birth',
    'date_of_passing',
    'date_format',
    'image_shape',
    'image_crop_mode',
    'short_text',
    'uploaded_image_path',
    'public_slug',
    'paid_at',
    'guest_token_hash',
    'guest_token_expires_at',
])]
class Pamphlet extends Model
{
    /** @use HasFactory<PamphletFactory> */
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_passing' => 'date',
            'paid_at' => 'datetime',
            'guest_token_expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Pamphlet $pamphlet): void {
            if (blank($pamphlet->public_slug)) {
                $pamphlet->public_slug = Str::lower((string) Str::ulid());
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(Background::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function pamphletStyle(): HasOne
    {
        return $this->hasOne(PamphletStyle::class);
    }

    public function memorialPage(): HasOne
    {
        return $this->hasOne(MemorialPage::class);
    }

    public function pamphletQrCode(): HasOne
    {
        return $this->hasOne(PamphletQrCode::class);
    }
}
