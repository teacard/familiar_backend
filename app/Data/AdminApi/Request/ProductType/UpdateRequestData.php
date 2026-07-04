<?php

namespace App\Data\AdminApi\Request\ProductType;

use App\Http\Requests\AdminApi\ProductType\UpdateRequest;

readonly class UpdateRequestData
{
    public function __construct(
        public string $name,
    ) {
    }

    public static function fromRequest(UpdateRequest $request): self
    {
        return new self(
            name: $request->name,
        );
    }
}
