<?php

namespace App\Docs\AdminApi\ResponseContents\ProductType;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.ProductType.ProductTypeIndexResponseContent', description: '商品類別列表項目')]
class ProductTypeIndexResponseContent
{
    #[OA\Property(description: 'ID', example: 1)]
    public int $id;

    #[OA\Property(description: '類別名稱', example: '道具')]
    public string $name;

    #[OA\Property(description: '是否可刪除（沒有任何商品使用此類別時為 true）', example: false)]
    public bool $isDeletable;
}
