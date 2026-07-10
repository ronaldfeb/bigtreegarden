<?php

namespace App\Models;

use App\Enums\StaffRole;
use Database\Factories\StaffUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'role',
    'job_title',
    'is_active',
    'invited_by_staff_user_id',
    'last_active_at',
])]
class StaffUser extends Model
{
    /** @use HasFactory<StaffUserFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => StaffRole::class,
            'is_active' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'invited_by_staff_user_id');
    }

    public function marketingAdverts(): HasMany
    {
        return $this->hasMany(MarketingAdvert::class);
    }

    public function marketingLeads(): HasMany
    {
        return $this->hasMany(MarketingLead::class);
    }
}
