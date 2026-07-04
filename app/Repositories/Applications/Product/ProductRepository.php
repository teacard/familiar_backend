<?php

namespace App\Repositories\Applications\Product;

use App\Models\Product;
use App\Repositories\Repository;

class ProductRepository extends Repository
{
    public function __construct(Product $model)
    {
        $this->model = $model;
    }
}
