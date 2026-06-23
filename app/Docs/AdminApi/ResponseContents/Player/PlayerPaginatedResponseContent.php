<?php

namespace App\Docs\AdminApi\ResponseContents\Player;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Player.PlayerPaginatedResponseContent', description: '遊戲會員分頁列表')]
class PlayerPaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: PlayerResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 15)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 7)]
    public int $lastPage;
}
