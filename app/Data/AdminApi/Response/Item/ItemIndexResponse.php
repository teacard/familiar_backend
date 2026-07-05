<?php

namespace App\Data\AdminApi\Response\Item;

use App\Data\AdminApi\Response\Media\MediaResponse;
use App\Enums\Media\CollectionName;
use App\Models\Item;
use Spatie\LaravelData\Data;

class ItemIndexResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $status,
        public MediaResponse $image,
        public bool $isDeletable,
    ) {
    }

    public static function fromModel(Item $item): self
    {
        return new self(
            id: $item->id,
            name: $item->name,
            status: $item->status->value,
            image: MediaResponse::fromMedia($item->getFirstMedia(CollectionName::ITEM->value)),
            // 沒有任何商品獎勵明細使用此道具時才可刪除
            isDeletable: !$item->product_rewards_exists,
        );
    }
}
