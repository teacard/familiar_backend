<?php

namespace App\Data\AdminApi\Response\Product;

use App\Data\AdminApi\Response\Media\MediaResponse;
use App\Data\AdminApi\Response\ProductType\ProductTypeResponse;
use App\Enums\Media\CollectionName;
use App\Models\Product;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ProductResponse extends Data
{
    public function __construct(
        public string $name,
        public ProductTypeResponse $productType,
        public int $amount,
        public string $status,
        public MediaResponse $image,
        /** @var Collection<int, ProductRewardResponse> */
        public Collection $productRewards,
    ) {
    }

    public static function fromModel(Product $product): self
    {
        return new self(
            name: $product->name,
            productType: ProductTypeResponse::fromModel($product->productType),
            amount: $product->amount,
            status: $product->status->value,
            image: MediaResponse::fromMedia($product->getFirstMedia(CollectionName::PRODUCT->value)),
            productRewards: ProductRewardResponse::collect($product->productRewards),
        );
    }
}
