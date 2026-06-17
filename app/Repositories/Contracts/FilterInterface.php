<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder;
}
