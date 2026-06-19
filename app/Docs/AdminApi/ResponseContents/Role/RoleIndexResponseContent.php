<?php

namespace App\Docs\AdminApi\ResponseContents\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Role.RoleIndexResponseContent', description: '角色列表項目')]
class RoleIndexResponseContent
{
    #[OA\Property(description: 'ID', example: 1)]
    public int $id;

    #[OA\Property(description: '角色名稱', example: '超級管理員')]
    public string $name;

    #[OA\Property(description: '是否可刪除（沒有任何 admin 帳號使用此角色時為 true）', example: false)]
    public bool $isDeletable;
}
