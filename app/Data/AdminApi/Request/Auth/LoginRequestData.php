<?php

namespace App\Data\AdminApi\Request\Auth;

use App\Http\Requests\AdminApi\Auth\LoginRequest;

readonly class LoginRequestData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }

    public static function fromRequest(LoginRequest $request): self
    {
        return new self(
            email: $request->email,
            password: $request->password,
        );
    }
}
