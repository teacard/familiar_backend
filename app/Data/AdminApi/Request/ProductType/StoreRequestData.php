<?php

namespace App\Data\AdminApi\Request\ProductType;

use App\Http\Requests\AdminApi\ProductType\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $name,
    ) {
    }

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            name: $request->name,
        );
    }
}
