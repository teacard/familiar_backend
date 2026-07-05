<?php

namespace App\Data\AdminApi\Request\Item;

use App\Http\Requests\AdminApi\Item\SelectRequest;

readonly class SelectRequestData
{
    public function __construct(
        public bool $isActive,
    ) {
    }

    public static function fromRequest(SelectRequest $request): self
    {
        return new self(
            isActive: $request->boolean('isActive'),
        );
    }
}
