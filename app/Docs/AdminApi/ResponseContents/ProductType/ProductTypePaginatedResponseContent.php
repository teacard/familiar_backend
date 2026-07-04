<?php

namespace App\Docs\AdminApi\ResponseContents\ProductType;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.ProductType.ProductTypePaginatedResponseContent', description: '商品類別分頁列表')]
class ProductTypePaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: ProductTypeIndexResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 10)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 3)]
    public int $lastPage;
}
