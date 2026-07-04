<?php

namespace App\Docs\AdminApi\Requests\Product;

use App\Enums\Product\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Product.UpdateRequest',
    required: ['name', 'productTypeId', 'amount', 'status', 'mediaId', 'productRewards'],
)]
class UpdateRequest
{
    #[OA\Property(description: '商品名稱', maxLength: 50, example: '新手禮包')]
    public string $name;

    #[OA\Property(description: '商品類別 ID（對應 product_types.id，可透過 GET /product-types/select 取得選項）', example: 1)]
    public int $productTypeId;

    #[OA\Property(description: '價格（新台幣，整數，單位：元）', minimum: 1, example: 300)]
    public int $amount;

    #[OA\Property(description: '上架狀態', enum: [Status::class], example: Status::PUBLISHED->value)]
    public string $status;

    #[OA\Property(description: '商品主圖媒體 ID（來自暫存上傳，必填）', example: 1)]
    public int $mediaId;

    #[OA\Property(
        description: '獎勵內容明細（必填，至少一筆；完整覆蓋，非增量更新；未包含在此陣列中的既有明細會被刪除）',
        minItems: 1,
        items: new OA\Items(ref: ProductRewardRequest::class),
    )]
    public array $productRewards;
}
