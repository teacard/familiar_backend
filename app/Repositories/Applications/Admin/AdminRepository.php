<?php

namespace App\Repositories\Applications\Admin;

use App\Models\Admin;
use App\Repositories\Repository;

class AdminRepository extends Repository
{
    public function __construct(Admin $model)
    {
        $this->model = $model;
    }
}
