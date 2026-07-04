<?php

namespace App\Docs\AdminApi\ResponseContents\Product;

use App\Enums\Product\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Product.ProductListResponseContent', description: '商品列表項目')]
class ProductListResponseContent
{
    #[OA\Property(description: '商品 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '商品名稱', example: '新手禮包')]
    public string $name;

    #[OA\Property(description: '商品類別名稱', example: '禮包')]
    public string $productTypeName;

    #[OA\Property(description: '價格（新台幣，整數，單位：元）', minimum: 1, example: 300)]
    public int $amount;

    #[OA\Property(description: '上架狀態', enum: [Status::class], example: Status::PUBLISHED->value)]
    public string $status;

    #[OA\Property(description: '建立時間', example: '2026-07-01 12:00:00')]
    public string $createdAt;
}
