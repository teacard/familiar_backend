<?php

namespace App\Data\Api\Response\Registration;

readonly class RegistrationTokenResponse
{
    public function __construct(
        public string $token,
    ) {
    }
}
