<?php

namespace App\Repositories\Applications\Role\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class WithPermissions implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->with('permissions');
    }
}
