<?php

namespace App\Docs\AdminApi\ResponseContents\Permission;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Permission.PermissionResponseContent', description: '權限')]
class PermissionResponseContent
{
    #[OA\Property(description: '權限名稱', example: 'view_users')]
    public string $name;

    #[OA\Property(description: '權限顯示名稱', example: '查看後台人員')]
    public string $label;
}
