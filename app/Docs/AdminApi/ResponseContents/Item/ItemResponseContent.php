<?php

namespace App\Docs\AdminApi\ResponseContents\Item;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Item.ItemResponseContent', description: '道具')]
class ItemResponseContent
{
    #[OA\Property(description: '道具 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '道具名稱', example: '新手劍')]
    public string $name;
}
