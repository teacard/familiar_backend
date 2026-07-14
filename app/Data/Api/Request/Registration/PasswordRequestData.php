<?php

namespace App\Data\Api\Request\Registration;

use App\Http\Requests\Api\Registration\PasswordRequest;

readonly class PasswordRequestData
{
    public function __construct(
        public string $password,
    ) {
    }

    public static function fromRequest(PasswordRequest $request): self
    {
        return new self(
            password: $request->password,
        );
    }
}
