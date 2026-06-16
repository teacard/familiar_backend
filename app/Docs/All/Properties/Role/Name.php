<?php

namespace App\Docs\All\Properties\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Role.Name', description: '角色名稱', type: 'string', example: '超級管理員')]
class Name {}
