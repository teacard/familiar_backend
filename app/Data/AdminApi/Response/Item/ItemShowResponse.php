<?php

namespace App\Data\AdminApi\Response\Item;

use App\Data\AdminApi\Response\Media\MediaResponse;
use App\Enums\Media\CollectionName;
use App\Models\Item;
use Spatie\LaravelData\Data;

class ItemShowResponse extends Data
{
    public function __construct(
        public string $name,
        public string $status,
        public MediaResponse $image,
    ) {
    }

    public static function fromModel(Item $item): self
    {
        return new self(
            name: $item->name,
            status: $item->status->value,
            image: MediaResponse::fromMedia($item->getFirstMedia(CollectionName::ITEM->value)),
        );
    }
}
