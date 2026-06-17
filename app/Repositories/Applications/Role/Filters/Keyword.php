<?php

namespace App\Repositories\Applications\Role\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Keyword implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        $keyword = Str::replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return $query->where('name', 'like', "%{$keyword}%");
    }
}
