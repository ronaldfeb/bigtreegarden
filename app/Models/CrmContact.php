<?php

namespace App\Models;

use App\Enums\CrmJourney;
use App\Enums\CrmLifecycleStage;
use Database\Factories\CrmContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'crm_organisation_id',
    'relationship_owner_staff_user_id',
    'user_id',
    'marketing_lead_id',
    'name',
    'email',
    'phone',
    'role_title',
    'journey',
    'lifecycle_stage',
    'notes',
    'last_contacted_at',
])]
class CrmContact extends Model
{
    /** @use HasFactory<CrmContactFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'journey' => CrmJourney::class,
            'lifecycle_stage' => CrmLifecycleStage::class,
            'last_contacted_at' => 'datetime',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(CrmOrganisation::class, 'crm_organisation_id');
    }

    public function relationshipOwner(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'relationship_owner_staff_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
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
