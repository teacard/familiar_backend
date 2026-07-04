<?php

namespace App\Repositories\Applications\ProductType\Filters;

use App\Repositories\Support\Fuzzy;

class Keyword extends Fuzzy
{
    protected string $column = 'name';
}
