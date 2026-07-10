<?php

namespace App\Models;

use Database\Factories\ServiceProviderUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'service_provider_id',
    'user_id',
    'role',
])]
class ServiceProviderUser extends Pivot
{
    /** @use HasFactory<ServiceProviderUserFactory> */
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'service_provider_users';

    /**
     * The table uses a composite (service_provider_id, user_id) primary key
     * with no id column, so no UUID should be generated on create.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return [];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
