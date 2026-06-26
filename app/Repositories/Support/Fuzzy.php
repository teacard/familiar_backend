<?php

namespace App\Repositories\Support;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class Fuzzy implements FilterInterface
{
    protected string $column;

    public function apply(Builder $query, mixed $value): Builder
    {
        $keyword = Str::replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return $query->where($this->column, 'like', "%{$keyword}%");
    }
}
