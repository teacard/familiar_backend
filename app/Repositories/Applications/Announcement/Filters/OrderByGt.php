<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Gt;

class OrderByGt extends Gt
{
    protected string $column = 'order_by';
}
