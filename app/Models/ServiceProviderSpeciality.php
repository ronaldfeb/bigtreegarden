<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\ServiceProviderSpecialityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_provider_id',
    'name',
    'sort_order',
])]
class ServiceProviderSpeciality extends Model
{
    /** @use HasFactory<ServiceProviderSpecialityFactory> */
    use HasDomainTimestamps, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }
}
