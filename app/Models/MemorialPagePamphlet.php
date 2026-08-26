<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use App\Enums\PamphletStatus;
use Database\Factories\MemorialPagePamphletFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'memorial_page_id',
    'background_id',
    'service_provider_background_id',
    'heading',
    'short_text',
    'date_format',
    'image_shape',
    'image_crop_mode',
    'uploaded_image_path',
    'preview_image_path',
    'status',
    'paid_at',
])]
class MemorialPagePamphlet extends Model
{
    /** @use HasFactory<MemorialPagePamphletFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'status' => PamphletStatus::class,
        ];
    }

    public function memorialPage(): BelongsTo
    {
        return $this->belongsTo(MemorialPage::class);
    }

    public function background(): BelongsTo
    {
        return $this->belongsTo(MemorialPagePamphletBackground::class, 'background_id');
    }

    public function serviceProviderBackground(): BelongsTo
    {
        return $this->belongsTo(ServiceProviderBackground::class, 'service_provider_background_id');
    }

    public function style(): HasOne
    {
        return $this->hasOne(MemorialPagePamphletStyle::class, 'pamphlet_id');
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'payable');
    }

    public function owner(): ?User
    {
        $this->loadMissing('memorialPage.personOfInterest.users', 'memorialPage.personOfInterest.createdBy');

        $personOfInterest = $this->memorialPage?->personOfInterest;

        if ($personOfInterest === null) {
            return null;
        }

        return $personOfInterest->users->firstWhere('pivot.role', 'owner')
            ?? $personOfInterest->createdBy;
    }

    public function isOwnedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        $owner = $this->owner();

        return $owner !== null && $owner->is($user);
    }

    public function canBeManagedBy(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($this->isOwnedBy($user)) {
            return true;
        }

        $this->loadMissing('memorialPage.personOfInterest.serviceProvider.memberships');

        $personOfInterest = $this->memorialPage?->personOfInterest;
        $serviceProvider = $personOfInterest?->serviceProvider;

        if ($serviceProvider === null) {
            return false;
        }

        return $serviceProvider->memberships
            ->contains(fn (ServiceProviderUser $membership): bool => $membership->user_id === $user->id);
    }

    public function getPersonFullNameAttribute(): ?string
    {
        return $this->memorialPage?->personOfInterest?->display_name;
    }

    public function getPublicSlugAttribute(): ?string
    {
        return $this->memorialPage?->public_slug;
    }

    public function getDateOfBirthAttribute(): ?\DateTimeInterface
    {
        return $this->memorialPage?->personOfInterest?->date_of_birth;
    }

    public function getDateOfPassingAttribute(): ?\DateTimeInterface
    {
        return $this->memorialPage?->personOfInterest?->date_of_passing;
    }

    public function getUserAttribute(): ?User
    {
        return $this->owner();
    }
}
