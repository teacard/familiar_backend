<?php

namespace App\Data\Api\Request\Registration;

use App\Http\Requests\Api\Registration\ProfileRequest;

readonly class ProfileRequestData
{
    public function __construct(
        public string $name,
        public int $mediaId,
    ) {
    }

    public static function fromRequest(ProfileRequest $request): self
    {
        return new self(
            name: $request->name,
            mediaId: (int)$request->mediaId,
        );
    }
}
