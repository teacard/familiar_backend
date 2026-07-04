<?php

namespace App\Data\AdminApi\Response\Product;

use App\Data\AdminApi\Response\Item\ItemResponse;
use App\Models\ProductReward;
use Spatie\LaravelData\Data;

class ProductRewardResponse extends Data
{
    public function __construct(
        public ItemResponse $item,
        public int $quantity,
    ) {
    }

    public static function fromModel(ProductReward $reward): self
    {
        return new self(
            item: ItemResponse::fromModel($reward->item),
            quantity: $reward->quantity,
        );
    }
}
