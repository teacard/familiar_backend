<?php

namespace App\Data\AdminApi\Response\ProductType;

use App\Models\ProductType;
use Spatie\LaravelData\Data;

class ProductTypeResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromModel(ProductType $productType): self
    {
        return new self(
            id: $productType->id,
            name: $productType->name,
        );
    }
}
