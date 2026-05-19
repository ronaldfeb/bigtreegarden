<?php

namespace App\Models;

use Database\Factories\BackgroundCollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'description'])]
class BackgroundCollection extends Model
{
    /** @use HasFactory<BackgroundCollectionFactory> */
    use HasFactory, HasUuids;

    public function backgrounds(): HasMany
    {
        return $this->hasMany(Background::class);
    }
}
