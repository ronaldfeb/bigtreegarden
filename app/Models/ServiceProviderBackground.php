<?php

namespace App\Models;

use Database\Factories\ServiceProviderBackgroundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'service_provider_id',
    'name',
    'image_path',
    'sort_order',
])]
class ServiceProviderBackground extends Model
{
    /** @use HasFactory<ServiceProviderBackgroundFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function getAssetPathAttribute(): string
    {
        return $this->image_path;
    }
}
