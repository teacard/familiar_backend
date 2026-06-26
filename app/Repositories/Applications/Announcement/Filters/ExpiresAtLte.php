<?php

namespace App\Repositories\Applications\Announcement\Filters;

use App\Repositories\Support\Lte;

class ExpiresAtLte extends Lte
{
    protected string $column = 'expires_at';
}
