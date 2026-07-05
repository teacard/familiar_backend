<?php

namespace App\Docs\AdminApi\ResponseContents\Item;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Item.ItemSelectResponseContent', description: '道具下拉選項（isActive=true 時僅回傳啟用中的道具，否則回傳所有狀態）')]
class ItemSelectResponseContent
{
    #[OA\Property(description: '顯示名稱', example: '新手劍')]
    public string $label;

    #[OA\Property(description: '道具 ID（建立/編輯商品獎勵時 itemId 欄位帶入此值）', example: 1)]
    public int $value;
}
