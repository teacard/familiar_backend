<?php

namespace App\Repositories\Applications\Media\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class CreatedAtLt implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('created_at', '<', $value);
    }
}
