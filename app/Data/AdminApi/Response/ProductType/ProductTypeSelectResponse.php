<?php

namespace App\Data\AdminApi\Response\ProductType;

use App\Models\ProductType;
use Spatie\LaravelData\Data;

class ProductTypeSelectResponse extends Data
{
    public function __construct(
        public string $label,
        public int $value,
    ) {
    }

    public static function fromModel(ProductType $productType): self
    {
        return new self(
            label: $productType->name,
            value: $productType->id,
        );
    }
}
