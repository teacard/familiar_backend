<?php

namespace App\Repositories\Applications\Item;

use App\Models\Item;
use App\Repositories\Repository;

class ItemRepository extends Repository
{
    public function __construct(Item $model)
    {
        $this->model = $model;
    }
}
