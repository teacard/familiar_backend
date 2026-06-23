<?php

namespace App\Docs\AdminApi\Requests\Player;

use App\Enums\Player\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Player.UpdateRequest',
    required: ['name', 'email', 'status'],
)]
class UpdateRequest
{
    #[OA\Property(description: '名稱', maxLength: 50, example: '生物蒐集家')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件（登入帳號）', example: 'player@example.com')]
    public string $email;

    #[OA\Property(description: '手機號碼（選填，填寫則唯一）', maxLength: 20, example: '0912345678')]
    public ?string $phone;

    #[OA\Property(description: '狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;

    #[OA\Property(description: '密碼（留空則不變更）', minLength: 8, example: 'P@ssw0rd')]
    public ?string $password;

    #[OA\Property(description: '確認密碼', minLength: 8, example: 'P@ssw0rd')]
    public ?string $passwordConfirmation;
}
