<?php

namespace App\Data\AdminApi\Response\Auth;

readonly class LoginResponse
{
    public function __construct(
        public string $token,
    ) {
    }
}
