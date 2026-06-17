<?php

namespace App\Repositories\Applications\Admin\Filters;

use App\Repositories\Support\Eq;

class Status extends Eq
{
    protected string $column = 'status';
}
