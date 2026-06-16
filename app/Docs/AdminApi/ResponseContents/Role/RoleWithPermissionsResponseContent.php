<?php

namespace App\Docs\AdminApi\ResponseContents\Role;

use App\Docs\All\Properties\Role\Id;
use App\Docs\All\Properties\Role\Name;
use App\Docs\All\Properties\Role\Permissions;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Role.RoleWithPermissionsResponseContent', description: '角色（含權限）')]
class RoleWithPermissionsResponseContent
{
    #[OA\Property]
    public Id $id;

    #[OA\Property]
    public Name $name;

    #[OA\Property]
    public Permissions $permissions;
}
