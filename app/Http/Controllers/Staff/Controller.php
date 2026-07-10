<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller as BaseController;
use App\Models\StaffUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class Controller extends BaseController
{
    protected function staffUser(Request $request): StaffUser
    {
        /** @var StaffUser $staffUser */
        $staffUser = $request->user()->staffUser;

        return $staffUser;
    }

    protected function uniqueSlug(string $value, string $modelClass, ?string $ignoreId = null): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $counter = 1;

        while ($modelClass::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
