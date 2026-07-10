<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable([
    'user_id',
    'person_of_interest_id',
    'role',
])]
class UserPersonOfInterest extends Pivot
{
    public $incrementing = false;

    protected $table = 'user_persons_of_interest';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function personOfInterest(): BelongsTo
    {
        return $this->belongsTo(PersonOfInterest::class);
    }
}
