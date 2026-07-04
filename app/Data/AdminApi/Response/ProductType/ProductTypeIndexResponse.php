<?php

namespace App\Data\AdminApi\Response\ProductType;

use App\Models\ProductType;
use Spatie\LaravelData\Data;

class ProductTypeIndexResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isDeletable,
    ) {
    }

    public static function fromModel(ProductType $productType): self
    {
        return new self(
            id: $productType->id,
            name: $productType->name,
            // 沒有任何商品使用此類別時才可刪除
            isDeletable: !$productType->products_exists,
        );
    }
}
