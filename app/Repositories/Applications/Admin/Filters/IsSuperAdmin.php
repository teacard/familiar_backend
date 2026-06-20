<?php

namespace App\Repositories\Applications\Admin\Filters;

use App\Repositories\Support\Eq;

class IsSuperAdmin extends Eq
{
    protected string $column = 'is_super_admin';
}
