<?php

namespace App\Docs\AdminApi\ResponseContents\Admin;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Admin.AdminPaginatedResponseContent', description: '後台人員分頁列表')]
class AdminPaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: AdminResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 15)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 7)]
    public int $lastPage;
}
