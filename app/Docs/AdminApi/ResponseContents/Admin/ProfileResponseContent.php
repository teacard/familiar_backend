<?php

namespace App\Docs\AdminApi\ResponseContents\Admin;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Admin.ProfileResponseContent', description: '後台個人資料')]
class ProfileResponseContent
{
    #[OA\Property(description: '名稱', example: '王小明')]
    public string $name;

    #[OA\Property(description: '頭像完整 URL（有頭像回該圖，無頭像時回預設頭像）', example: 'http://localhost/storage/profile_icon.svg')]
    public string $photo;

    #[OA\Property(description: '權限名稱清單', items: new OA\Items(type: 'string', example: 'view_users'))]
    public array $permissions;
}
