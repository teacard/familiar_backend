<?php

namespace App\Repositories\Applications\Media\Filters;

use App\Repositories\Support\Eq;

class CollectionName extends Eq
{
    protected string $column = 'collection_name';
}
