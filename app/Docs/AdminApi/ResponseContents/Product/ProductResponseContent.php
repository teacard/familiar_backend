<?php

namespace App\Docs\AdminApi\ResponseContents\Product;

use App\Docs\AdminApi\ResponseContents\Media\MediaResponseContent;
use App\Docs\AdminApi\ResponseContents\ProductType\ProductTypeResponseContent;
use App\Enums\Product\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Product.ProductResponseContent', description: '商品詳情')]
class ProductResponseContent
{
    #[OA\Property(description: '商品名稱', example: '新手禮包')]
    public string $name;

    #[OA\Property(ref: ProductTypeResponseContent::class, description: '商品類別')]
    public object $productType;

    #[OA\Property(description: '價格（新台幣，整數，單位：元）', minimum: 1, example: 300)]
    public int $amount;

    #[OA\Property(description: '上架狀態', enum: [Status::class], example: Status::PUBLISHED->value)]
    public string $status;

    #[OA\Property(ref: MediaResponseContent::class, description: '商品主圖')]
    public object $image;

    #[OA\Property(
        description: '獎勵內容明細',
        items: new OA\Items(ref: ProductRewardResponseContent::class),
    )]
    public array $productRewards;
}
