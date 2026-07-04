<?php

namespace App\Repositories\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class OrderByDesc implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        foreach ((array)$value as $column) {
            $query->orderByDesc($column);
        }

        return $query;
    }
}
