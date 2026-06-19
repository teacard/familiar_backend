<?php

namespace App\Docs\AdminApi\Requests\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Role.UpdateRequest',
    required: ['name', 'permissions'],
)]
class UpdateRequest
{
    #[OA\Property(description: '角色名稱（不可重複，但可與自己原本的名稱相同）', maxLength: 50, example: '超級管理員')]
    public string $name;

    #[OA\Property(description: '權限名稱清單', items: new OA\Items(type: 'string', example: 'view_users'))]
    public array $permissions;
}
