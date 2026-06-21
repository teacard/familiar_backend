<?php

namespace App\Docs\AdminApi\ResponseContents\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Role.RoleSelectResponseContent', description: '角色下拉選項')]
class RoleSelectResponseContent
{
    #[OA\Property(description: 'ID', example: 1)]
    public int $id;

    #[OA\Property(description: '角色名稱', example: '超級管理員')]
    public string $name;
}
