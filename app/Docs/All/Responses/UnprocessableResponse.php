<?php

namespace App\Docs\All\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP 422 驗證失敗回應。
 *
 * 用於 Request 欄位驗證不通過的 API（Laravel FormRequest 拋出時）。
 *
 * 使用範例：
 * ```php
 * responses: [new UnprocessableResponse(), ...]
 * ```
 */
class UnprocessableResponse extends OA\Response
{
    public function __construct(string $description = '驗證失敗')
    {
        parent::__construct(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: $description,
        );
    }
}
