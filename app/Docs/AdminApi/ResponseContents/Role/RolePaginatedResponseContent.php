<?php

namespace App\Docs\AdminApi\ResponseContents\Role;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Role.RolePaginatedResponseContent', description: '角色分頁列表')]
class RolePaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: RoleIndexResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 10)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 7)]
    public int $lastPage;
}
