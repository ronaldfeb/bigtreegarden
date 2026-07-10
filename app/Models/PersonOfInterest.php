<?php

namespace App\Models;

use App\Enums\PersonOfInterestStatus;
use Database\Factories\PersonOfInterestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'created_by_user_id',
    'guest_token_hash',
    'guest_token_expires_at',
    'first_name',
    'last_name',
    'display_name',
    'date_of_birth',
    'date_of_passing',
    'place_of_birth',
    'place_of_passing',
    'profile_image_path',
    'public_slug',
    'qr_code_path',
    'qr_generated_at',
    'status',
])]
class PersonOfInterest extends Model
{
    /** @use HasFactory<PersonOfInterestFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'persons_of_interest';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PersonOfInterestStatus::class,
            'date_of_birth' => 'date',
            'date_of_passing' => 'date',
            'guest_token_expires_at' => 'datetime',
            'qr_generated_at' => 'datetime',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_persons_of_interest')
            ->using(UserPersonOfInterest::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function memorialPages(): HasMany
    {
        return $this->hasMany(MemorialPage::class);
    }
}
