<?php

namespace App\Repositories\Applications\Player\Filters;

use App\Repositories\Support\Eq;

class PlayerNumber extends Eq
{
    protected string $column = 'player_number';
}
