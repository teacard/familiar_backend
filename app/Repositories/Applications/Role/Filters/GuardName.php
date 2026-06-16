<?php

namespace App\Repositories\Applications\Role\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class GuardName implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('guard_name', $value);
    }
}
