<?php

namespace App\Repositories\Applications\Admin\Filters;

use App\Repositories\Support\Eq;

class Email extends Eq
{
    protected string $column = 'email';
}
