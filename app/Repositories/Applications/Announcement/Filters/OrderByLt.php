<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Lt;

class OrderByLt extends Lt
{
    protected string $column = 'order_by';
}
