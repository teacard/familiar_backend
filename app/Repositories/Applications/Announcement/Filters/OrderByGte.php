<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Gte;

class OrderByGte extends Gte
{
    protected string $column = 'order_by';
}
