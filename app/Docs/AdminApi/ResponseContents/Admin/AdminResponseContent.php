<?php

namespace App\Docs\AdminApi\ResponseContents\Admin;

use App\Enums\Admin\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Admin.AdminResponseContent', description: '後台人員')]
class AdminResponseContent
{
    #[OA\Property(description: '後台人員 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '名稱', example: '王小明')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件', example: 'admin@example.com')]
    public string $email;

    #[OA\Property(description: '角色名稱', example: '超級管理員')]
    public string $role;

    #[OA\Property(description: '狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;

    #[OA\Property(description: '最後登入日期', example: '2026-06-15')]
    public ?string $lastLoginDate;
}
