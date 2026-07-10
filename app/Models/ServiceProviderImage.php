<?php

namespace App\Models;

use App\Concerns\HasDomainTimestamps;
use Database\Factories\ServiceProviderImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_provider_id',
    'image_path',
    'caption',
    'sort_order',
])]
class ServiceProviderImage extends Model
{
    /** @use HasFactory<ServiceProviderImageFactory> */
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
