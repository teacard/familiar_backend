<?php

namespace App\Repositories\Applications\TemporaryMedia;

use App\Models\TemporaryMedia;
use App\Repositories\Repository;

class TemporaryMediaRepository extends Repository
{
    public function __construct(TemporaryMedia $model)
    {
        $this->model = $model;
    }
}
