<?php

namespace App\Data\AdminApi\Request\Product;

use Illuminate\Support\Collection;

readonly class ProductRewardData
{
    public function __construct(
        public int $itemId,
        public int $quantity,
    ) {
    }

    /** @return Collection<int, self> */
    public static function collect(?array $rewards): Collection
    {
        return collect($rewards)->map(fn (array $reward): self => new self(
            itemId: (int)$reward['itemId'],
            quantity: (int)$reward['quantity'],
        ));
    }
}
