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

    /** 產生唯一的 player_number：固定前綴 PL + 8 位純數字，碰撞重試 */
    public function generateUniquePlayerNumber(): string
    {
        do {
            $candidate = 'PL' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while ($this->exists(['playerNumber' => $candidate]));

        return $candidate;
    }
}
