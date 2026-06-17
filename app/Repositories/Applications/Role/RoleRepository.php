<?php

namespace App\Repositories\Applications\Role;

use App\Models\Role;
use App\Repositories\Repository;

class RoleRepository extends Repository
{
    public function __construct(Role $model)
    {
        $this->model = $model;
    }
}
