<?php

namespace App\Repositories\Applications\Player\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Keyword implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        $keyword = Str::replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);

        return $query->where(function (Builder $q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('player_number', 'like', "%{$keyword}%")
                ->orWhere('email', 'like', "%{$keyword}%");
        });
    }
}
