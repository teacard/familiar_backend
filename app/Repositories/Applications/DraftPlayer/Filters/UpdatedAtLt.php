<?php

namespace App\Repositories\Applications\DraftPlayer\Filters;

use App\Repositories\Support\Lt;

class UpdatedAtLt extends Lt
{
    protected string $column = 'updated_at';
}
