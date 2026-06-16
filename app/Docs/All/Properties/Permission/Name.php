<?php

namespace App\Docs\All\Properties\Permission;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Permission.Name', description: '權限名稱', type: 'string', example: 'view_users')]
class Name {}
