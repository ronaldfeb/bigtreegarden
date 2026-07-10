<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\MemorialPageMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'memorial_page_id',
    'author_user_id',
    'memorial_site_id',
    'transaction_id',
    'type',
    'body',
    'image_path',
    'context',
    'posted_latitude',
    'posted_longitude',
    'is_gps_verified',
    'status',
    'approved_by_user_id',
    'approved_at',
])]
class MemorialPageMessage extends Model
{
    /** @use HasFactory<MemorialPageMessageFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'posted_latitude' => 'decimal:7',
            'posted_longitude' => 'decimal:7',
            'is_gps_verified' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function memorialPage(): BelongsTo
    {
        return $this->belongsTo(MemorialPage::class);
    }

    public function authorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function memorialSite(): BelongsTo
    {
        return $this->belongsTo(MemorialSite::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
