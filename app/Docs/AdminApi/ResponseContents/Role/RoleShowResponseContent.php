<?php

namespace App\Docs\AdminApi\ResponseContents\Role;

use App\Docs\All\Properties\Role\Name;
use App\Docs\All\Properties\Role\Permissions;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Role.RoleShowResponseContent', description: '角色詳情（含權限）')]
class RoleShowResponseContent
{
    #[OA\Property]
    public Name $name;

    #[OA\Property]
    public Permissions $permissions;
}
