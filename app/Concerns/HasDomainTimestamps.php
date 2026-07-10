<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;

trait HasDomainTimestamps
{
    use SoftDeletes;
}
