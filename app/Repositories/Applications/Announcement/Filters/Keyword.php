<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Fuzzy;

class Keyword extends Fuzzy
{
    protected string $column = 'title';
}
