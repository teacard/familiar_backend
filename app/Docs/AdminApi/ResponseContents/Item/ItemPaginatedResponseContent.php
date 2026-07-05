<?php

namespace App\Docs\AdminApi\ResponseContents\Item;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Item.ItemPaginatedResponseContent', description: '道具分頁列表')]
class ItemPaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: ItemIndexResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 10)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 3)]
    public int $lastPage;
}
