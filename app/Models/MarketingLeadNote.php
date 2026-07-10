<?php

namespace App\Models;

use Database\Factories\MarketingLeadNoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'marketing_lead_id',
    'staff_user_id',
    'body',
])]
class MarketingLeadNote extends Model
{
    /** @use HasFactory<MarketingLeadNoteFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public function marketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class);
    }

    public function staffUser(): BelongsTo
    {
        return $this->belongsTo(StaffUser::class);
    }
}
