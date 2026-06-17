<?php

namespace App\Repositories\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class Id implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('id', $value);
    }
}
