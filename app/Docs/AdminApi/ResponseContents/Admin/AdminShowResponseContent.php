<?php

namespace App\Docs\AdminApi\ResponseContents\Admin;

use App\Enums\Admin\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Admin.AdminShowResponseContent', description: '後台人員詳情')]
class AdminShowResponseContent
{
    #[OA\Property(description: '名稱', example: '王小明')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件', example: 'admin@example.com')]
    public string $email;

    #[OA\Property(description: '角色名稱', example: '超級管理員')]
    public string $role;

    #[OA\Property(description: '狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;

    #[OA\Property(
        description: '頭像（無頭像時為 null）',
        nullable: true,
        properties: [
            new OA\Property(property: 'id', type: 'integer', description: '媒體 ID', example: 1),
            new OA\Property(property: 'url', type: 'string', description: '媒體完整 URL', example: 'http://localhost/media/1/avatar_abc123.png'),
        ],
        type: 'object',
    )]
    public ?object $avatar;
}
