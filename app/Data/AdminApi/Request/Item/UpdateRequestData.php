<?php

namespace App\Data\AdminApi\Request\Item;

use App\Enums\Item\Status;
use App\Http\Requests\AdminApi\Item\UpdateRequest;

readonly class UpdateRequestData
{
    public function __construct(
        public string $name,
        public Status $status,
        public int $mediaId,
    ) {
    }

    public static function fromRequest(UpdateRequest $request): self
    {
        return new self(
            name: $request->name,
            status: Status::from($request->status),
            mediaId: (int)$request->mediaId,
        );
    }
}
