<?php

namespace App\Repositories\Applications\TemporaryMedia\Filters;

use App\Repositories\Support\Eq;

class SystemName extends Eq
{
    protected string $column = 'system_name';
}
