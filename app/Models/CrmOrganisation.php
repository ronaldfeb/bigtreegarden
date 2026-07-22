<?php

namespace App\Models;

use App\Enums\CrmOrganisationType;
use App\Enums\CrmPartnerStage;
use App\Enums\CrmRelationshipKind;
use App\Enums\CrmRelationshipStatus;
use Database\Factories\CrmOrganisationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'name',
    'type',
    'relationship_kind',
    'partner_stage',
    'relationship_status',
    'relationship_owner_staff_user_id',
    'service_provider_id',
    'email',
    'phone',
    'website_url',
    'geographic_coverage',
    'services_offered',
    'notes',
    'last_contacted_at',
    'next_review_at',
])]
class CrmOrganisation extends Model
{
    /** @use HasFactory<CrmOrganisationFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CrmOrganisationType::class,
            'relationship_kind' => CrmRelationshipKind::class,
            'partner_stage' => CrmPartnerStage::class,
            'relationship_status' => CrmRelationshipStatus::class,
            'last_contacted_at' => 'datetime',
            'next_review_at' => 'datetime',
        ];
    }

    public function relationshipOwner(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'relationship_owner_staff_user_id');
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CrmContact::class);
    }

    public function interactions(): MorphMany
    {
        return $this->morphMany(CrmInteraction::class, 'subject');
    }

    public function followUps(): MorphMany
    {
        return $this->morphMany(CrmFollowUp::class, 'subject');
    }
}
