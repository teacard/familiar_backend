<?php

namespace App\Repositories\Applications\Product\Filters;

use App\Repositories\Support\Fuzzy;

class Keyword extends Fuzzy
{
    protected string $column = 'name';
}
