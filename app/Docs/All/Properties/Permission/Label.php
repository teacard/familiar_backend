<?php

namespace App\Docs\All\Properties\Permission;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Permission.Label', description: '權限顯示名稱', type: 'string', example: '查看後台人員')]
class Label {}
