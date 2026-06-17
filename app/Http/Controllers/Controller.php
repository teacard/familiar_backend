<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /** 成功回應 */
    public function success(
        mixed $data = null,
        ?int $httpCode = null,
        array $headers = [],
    ): JsonResponse {
        return response()->json(
            ['data' => $data],
            $httpCode ?? 200,
            $headers,
        );
    }

    /** 錯誤回應 */
    public function error(
        string $message,
        ?int $httpCode = null,
        array $headers = [],
    ): JsonResponse {
        return response()->json(
            ['message' => $message],
            $httpCode ?? 400,
            $headers,
        );
    }
}
