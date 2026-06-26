<?php

namespace App\Repositories\Support;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

abstract class Lte implements FilterInterface
{
    protected string $column;

    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where($this->column, '<=', $value);
    }
}
