<?php

namespace App\Models;

use App\Enums\CrmFollowUpStatus;
use App\Enums\CrmFollowUpType;
use Database\Factories\CrmFollowUpFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'subject_type',
    'subject_id',
    'assigned_staff_user_id',
    'type',
    'status',
    'title',
    'notes',
    'due_at',
    'completed_at',
])]
class CrmFollowUp extends Model
{
    /** @use HasFactory<CrmFollowUpFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CrmFollowUpType::class,
            'status' => CrmFollowUpStatus::class,
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignedStaffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class, 'assigned_staff_user_id');
    }

    /**
     * @param  Builder<CrmFollowUp>  $query
     * @return Builder<CrmFollowUp>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CrmFollowUpStatus::Pending);
    }

    /**
     * @param  Builder<CrmFollowUp>  $query
     * @return Builder<CrmFollowUp>
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->pending()->where('due_at', '<=', now());
    }
}
