<?php

namespace App\Docs\All\Properties\Role;

use App\Docs\All\Properties\Permission\Name as PermissionName;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Role.PermissionNames', description: '權限名稱清單', type: 'array', items: new OA\Items(ref: PermissionName::class))]
class PermissionNames {}
