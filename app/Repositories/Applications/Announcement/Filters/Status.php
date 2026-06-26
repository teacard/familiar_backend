<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Eq;

class Status extends Eq
{
    protected string $column = 'status';
}
