<?php

namespace App\Models;

use App\Enums\Auth\Guard;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected function casts(): array
    {
        return [
            ...parent::casts(),
            'guard_name' => Guard::class,
        ];
    }
}
