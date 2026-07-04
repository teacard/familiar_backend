<?php

namespace App\Repositories\Applications\ProductType\Filters;

use App\Repositories\Support\Eq;

class Code extends Eq
{
    protected string $column = 'code';
}
