<?php

namespace App\Repositories\Applications\Product\Filters;

use App\Repositories\Support\Eq;

class ProductTypeId extends Eq
{
    protected string $column = 'product_type_id';
}
