<?php

namespace App\Repositories\Applications\DraftPlayer;

use App\Models\DraftPlayer;
use App\Repositories\Repository;

class DraftPlayerRepository extends Repository
{
    public function __construct(DraftPlayer $model)
    {
        $this->model = $model;
    }
}
