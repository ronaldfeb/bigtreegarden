<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\ServiceProviderServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_provider_id',
    'name',
    'description',
    'price_from_cents',
    'sort_order',
])]
class ServiceProviderService extends Model
{
    /** @use HasFactory<ServiceProviderServiceFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'price_from_cents' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
