<?php

namespace App\Data\AdminApi\Response\Item;

use App\Models\Item;
use Spatie\LaravelData\Data;

class ItemSelectResponse extends Data
{
    public function __construct(
        public string $label,
        public int $value,
    ) {
    }

    public static function fromModel(Item $item): self
    {
        return new self(
            label: $item->name,
            value: $item->id,
        );
    }
}
