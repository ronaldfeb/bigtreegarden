<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\ServiceProviderSocialMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_provider_id',
    'platform',
    'url',
])]
class ServiceProviderSocialMedia extends Model
{
    /** @use HasFactory<ServiceProviderSocialMediaFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
