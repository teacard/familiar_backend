<?php

namespace App\Docs\AdminApi\ResponseContents\Product;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Product.ProductPaginatedResponseContent', description: '商品分頁列表')]
class ProductPaginatedResponseContent
{
    #[OA\Property(items: new OA\Items(ref: ProductListResponseContent::class))]
    public array $items;

    #[OA\Property(description: '當前頁碼', example: 1)]
    public int $currentPage;

    #[OA\Property(description: '每頁筆數', example: 10)]
    public int $perPage;

    #[OA\Property(description: '最後頁碼', example: 5)]
    public int $lastPage;
}
