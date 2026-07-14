<?php

namespace App\Repositories\Applications\DraftPlayer\Filters;

use App\Repositories\Support\Eq;

class Email extends Eq
{
    protected string $column = 'email';
}
