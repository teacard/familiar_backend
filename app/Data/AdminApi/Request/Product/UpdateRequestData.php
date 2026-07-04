<?php

namespace App\Data\AdminApi\Request\Product;

use App\Enums\Product\Status;
use App\Http\Requests\AdminApi\Product\UpdateRequest;
use Illuminate\Support\Collection;

readonly class UpdateRequestData
{
    public function __construct(
        public string $name,
        public int $productTypeId,
        public int $amount,
        public Status $status,
        public int $mediaId,
        /** @var Collection<int, ProductRewardData> */
        public Collection $productRewards,
    ) {
    }

    public static function fromRequest(UpdateRequest $request): self
    {
        return new self(
            name: $request->name,
            productTypeId: (int)$request->productTypeId,
            amount: (int)$request->amount,
            status: Status::from($request->status),
            mediaId: (int)$request->mediaId,
            productRewards: ProductRewardData::collect($request->productRewards),
        );
    }
}
