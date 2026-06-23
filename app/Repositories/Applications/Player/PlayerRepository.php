<?php

namespace App\Repositories\Applications\Player;

use App\Models\Player;
use App\Repositories\Repository;

class PlayerRepository extends Repository
{
    public function __construct(Player $model)
    {
        $this->model = $model;
    }
}
