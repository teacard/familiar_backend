<?php

namespace App\Repositories\Applications\Item\Filters;

use App\Repositories\Support\Fuzzy;

class Keyword extends Fuzzy
{
    protected string $column = 'name';
}
