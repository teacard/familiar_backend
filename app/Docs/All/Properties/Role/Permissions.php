<?php

namespace App\Docs\All\Properties\Role;

use App\Docs\AdminApi\ResponseContents\Permission\PermissionResponseContent;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Role.Permissions', type: 'array', items: new OA\Items(ref: PermissionResponseContent::class))]
class Permissions
{
}
