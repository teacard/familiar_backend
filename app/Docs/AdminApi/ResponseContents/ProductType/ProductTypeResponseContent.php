<?php

namespace App\Docs\AdminApi\ResponseContents\ProductType;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.ProductType.ProductTypeResponseContent', description: '商品類別')]
class ProductTypeResponseContent
{
    #[OA\Property(description: '商品類別 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '商品類別名稱', example: '道具')]
    public string $name;
}
