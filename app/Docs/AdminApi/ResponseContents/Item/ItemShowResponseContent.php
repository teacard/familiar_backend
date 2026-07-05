<?php

namespace App\Docs\AdminApi\ResponseContents\Item;

use App\Docs\AdminApi\ResponseContents\Media\MediaResponseContent;
use App\Enums\Item\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Item.ItemShowResponseContent', description: '道具詳情')]
class ItemShowResponseContent
{
    #[OA\Property(description: '道具名稱', example: '新手劍')]
    public string $name;

    #[OA\Property(description: '啟用狀態', enum: [Status::class], example: Status::ACTIVE->value)]
    public string $status;

    #[OA\Property(ref: MediaResponseContent::class, description: '道具圖片')]
    public object $image;
}
