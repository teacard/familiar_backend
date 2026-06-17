<?php

namespace App\Docs\AdminApi\ResponseContents\Role;

use App\Docs\AdminApi\ResponseContents\Permission\PermissionResponseContent;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Role.RoleWithPermissionsResponseContent', description: '角色（含權限）')]
class RoleWithPermissionsResponseContent
{
    #[OA\Property(description: 'ID', example: 1)]
    public int $id;

    #[OA\Property(description: '角色名稱', example: '超級管理員')]
    public string $name;

    #[OA\Property(items: new OA\Items(ref: PermissionResponseContent::class))]
    public array $permissions;
}
