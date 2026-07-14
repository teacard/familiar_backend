<?php

namespace App\Data\Api\Request\Registration;

use App\Http\Requests\Api\Registration\VerifyCodeRequest;

readonly class VerifyCodeRequestData
{
    public function __construct(
        public string $code,
    ) {
    }

    public static function fromRequest(VerifyCodeRequest $request): self
    {
        return new self(
            code: $request->code,
        );
    }
}
