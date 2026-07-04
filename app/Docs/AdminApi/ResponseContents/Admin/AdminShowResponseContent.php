<?php

namespace App\Docs\AdminApi\ResponseContents\Admin;

use App\Docs\AdminApi\ResponseContents\Media\MediaResponseContent;
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

    #[OA\Property(ref: MediaResponseContent::class, description: '頭像（無頭像時為 null）', nullable: true)]
    public ?object $avatar;
}
