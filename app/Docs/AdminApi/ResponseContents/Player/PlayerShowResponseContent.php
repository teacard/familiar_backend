<?php

namespace App\Docs\AdminApi\ResponseContents\Player;

use App\Enums\Player\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Player.PlayerShowResponseContent', description: '遊戲會員詳情（供編輯表單）')]
class PlayerShowResponseContent
{
    #[OA\Property(description: '名稱', example: '生物蒐集家')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件', example: 'player@example.com')]
    public string $email;

    #[OA\Property(description: '手機號碼（選填）', nullable: true, example: '0912345678')]
    public ?string $phone;

    #[OA\Property(description: '狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;
}
