<?php

namespace App\Data\Api\Request\Registration;

use App\Http\Requests\Api\Registration\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $email,
    ) {
    }

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            email: $request->email,
        );
    }
}
