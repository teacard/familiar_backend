<?php

namespace App\Repositories\Applications\Role\Filters;

use App\Repositories\Support\Eq;

class IsSystem extends Eq
{
    protected string $column = 'is_system';
}
