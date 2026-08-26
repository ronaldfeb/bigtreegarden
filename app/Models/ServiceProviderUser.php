<?php

namespace App\Models;

use App\Enums\ServiceProviderRole;
use Database\Factories\ServiceProviderUserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
    use HasFactory;

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

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ServiceProviderRole::class,
        ];
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOwner(): bool
    {
        return $this->role === ServiceProviderRole::Owner;
    }
}
