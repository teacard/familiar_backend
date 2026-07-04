<?php

namespace App\Repositories\Applications\ProductType;

use App\Models\ProductType;
use App\Repositories\Repository;

class ProductTypeRepository extends Repository
{
    public function __construct(ProductType $model)
    {
        $this->model = $model;
    }
}
