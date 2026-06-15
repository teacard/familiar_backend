<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class BaseException extends \Exception
{
    public function __construct(
        string $message = '',
        protected int $httpCode = 500,
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->message], $this->httpCode);
    }
}
