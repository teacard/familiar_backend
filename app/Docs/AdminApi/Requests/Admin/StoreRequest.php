<?php

namespace App\Docs\AdminApi\Requests\Admin;

use App\Enums\Admin\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Admin.StoreRequest',
    required: ['name', 'email', 'password', 'passwordConfirmation', 'roleId', 'status'],
)]
class StoreRequest
{
    #[OA\Property(description: '名稱（不可重複）', maxLength: 15, example: '王小明')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件（不可重複）', example: 'admin@example.com')]
    public string $email;

    #[OA\Property(description: '密碼（須與確認密碼相同）', minLength: 8, example: 'P@ssw0rd')]
    public string $password;

    #[OA\Property(description: '確認密碼（須與密碼相同）', minLength: 8, example: 'P@ssw0rd')]
    public string $passwordConfirmation;

    #[OA\Property(description: '角色 ID（須為存在的角色）', example: 1)]
    public int $roleId;

    #[OA\Property(description: '帳號狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;

    #[OA\Property(description: '頭像媒體 ID（來自暫存上傳，選填）', example: 1)]
    public ?int $mediaId;
}
