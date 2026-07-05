<?php

namespace App\Docs\AdminApi\Requests\Item;

use App\Enums\Item\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Item.StoreRequest',
    required: ['name', 'status', 'mediaId'],
)]
class StoreRequest
{
    #[OA\Property(description: '道具名稱', maxLength: 10, example: '新手劍')]
    public string $name;

    #[OA\Property(description: '啟用狀態', enum: [Status::class], example: Status::ACTIVE->value)]
    public string $status;

    #[OA\Property(description: '道具圖片媒體 ID（來自暫存上傳，必填）', example: 1)]
    public int $mediaId;
}
