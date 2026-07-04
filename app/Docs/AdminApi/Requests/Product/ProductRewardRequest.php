<?php

namespace App\Docs\AdminApi\Requests\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Product.ProductRewardRequest',
    required: ['itemId', 'quantity'],
)]
class ProductRewardRequest
{
    #[OA\Property(description: '對應道具表（items.id）的目標 ID；金幣/鑽石/通行證等皆統一視為一種道具，一律必填；同一次請求的 productRewards 陣列中不可重複', example: 42)]
    public int $itemId;

    #[OA\Property(description: '數量', minimum: 1, example: 3)]
    public int $quantity;
}
