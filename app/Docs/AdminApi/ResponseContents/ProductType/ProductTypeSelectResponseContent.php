<?php

namespace App\Docs\AdminApi\ResponseContents\ProductType;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.ProductType.ProductTypeSelectResponseContent', description: '商品類別下拉選項')]
class ProductTypeSelectResponseContent
{
    #[OA\Property(description: '顯示名稱', example: '道具')]
    public string $label;

    #[OA\Property(description: '商品類別 ID（建立/編輯商品時 productTypeId 欄位帶入此值）', example: 1)]
    public int $value;
}
