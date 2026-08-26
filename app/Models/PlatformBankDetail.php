<?php

namespace App\Models;

use Database\Factories\PlatformBankDetailFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'bank_name',
    'account_name',
    'account_number',
    'branch_code',
    'reference_note',
    'is_active',
])]
class PlatformBankDetail extends Model
{
    /** @use HasFactory<PlatformBankDetailFactory> */
    use HasFactory, HasUuids;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->where('is_active', true)->latest()->first();
    }
}
