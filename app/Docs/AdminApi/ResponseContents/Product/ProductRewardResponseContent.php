<?php

namespace App\Docs\AdminApi\ResponseContents\Product;

use App\Docs\AdminApi\ResponseContents\Item\ItemResponseContent;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Product.ProductRewardResponseContent', description: '商品獎勵內容明細')]
class ProductRewardResponseContent
{
    #[OA\Property(ref: ItemResponseContent::class, description: '對應的道具')]
    public object $item;

    #[OA\Property(description: '數量', minimum: 1, example: 3)]
    public int $quantity;
}
