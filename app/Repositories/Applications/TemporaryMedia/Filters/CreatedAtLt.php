<?php

namespace App\Repositories\Applications\TemporaryMedia\Filters;

use App\Repositories\Support\Lt;

class CreatedAtLt extends Lt
{
    protected string $column = 'created_at';
}
