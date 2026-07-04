<?php

namespace App\Data\AdminApi\Response\Product;

use App\Models\Product;
use Spatie\LaravelData\Data;

class ProductListResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $productTypeName,
        public int $amount,
        public string $status,
        public string $createdAt,
    ) {
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            productTypeName: $product->productType->name,
            amount: $product->amount,
            status: $product->status->value,
            createdAt: $product->created_at->toDateTimeString(),
        );
    }
}
