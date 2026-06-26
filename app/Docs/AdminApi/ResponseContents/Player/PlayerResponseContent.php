<?php

namespace App\Docs\AdminApi\ResponseContents\Player;

use App\Enums\Player\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Player.PlayerResponseContent', description: '遊戲會員')]
class PlayerResponseContent
{
    #[OA\Property(description: '會員 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '玩家 ID（PlayerID）', example: 'PL48230917')]
    public string $playerNumber;

    #[OA\Property(description: '名稱', example: '生物蒐集家')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件', example: 'player@example.com')]
    public string $email;

    #[OA\Property(description: '狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;

    #[OA\Property(description: '註冊日期', example: '2026-06-15')]
    public ?string $createdDate;
}
