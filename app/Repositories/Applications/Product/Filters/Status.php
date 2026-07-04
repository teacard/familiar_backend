<?php

namespace App\Repositories\Applications\Product\Filters;

use App\Repositories\Support\Eq;

class Status extends Eq
{
    protected string $column = 'status';
}
