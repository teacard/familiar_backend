<?php

namespace App\Data\AdminApi\Response\Item;

use App\Models\Item;
use Spatie\LaravelData\Data;

class ItemResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromModel(Item $item): self
    {
        return new self(
            id: $item->id,
            name: $item->name,
        );
    }
}
