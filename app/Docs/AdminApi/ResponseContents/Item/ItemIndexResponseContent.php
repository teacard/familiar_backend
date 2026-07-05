<?php

namespace App\Docs\AdminApi\ResponseContents\Item;

use App\Docs\AdminApi\ResponseContents\Media\MediaResponseContent;
use App\Enums\Item\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Item.ItemIndexResponseContent', description: '道具列表項目')]
class ItemIndexResponseContent
{
    #[OA\Property(description: 'ID', example: 1)]
    public int $id;

    #[OA\Property(description: '道具名稱', example: '新手劍')]
    public string $name;

    #[OA\Property(description: '啟用狀態', enum: [Status::class], example: Status::ACTIVE->value)]
    public string $status;

    #[OA\Property(ref: MediaResponseContent::class, description: '道具圖片')]
    public object $image;

    #[OA\Property(description: '是否可刪除（沒有任何商品獎勵明細使用此道具時為 true）', example: false)]
    public bool $isDeletable;
}
