<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class UnprocessableException extends BaseException
{
    public function __construct(
        string $message,
        private readonly \BackedEnum $apiCode,
    ) {
        parent::__construct($message, 422);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'apiCode' => $this->apiCode->value,
        ], 422);
    }
}
