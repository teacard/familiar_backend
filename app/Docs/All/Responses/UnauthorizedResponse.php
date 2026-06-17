<?php

namespace App\Docs\All\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP 401 未授權回應。
 *
 * 用於需要登入才能存取的 API（未帶 Token 或 Token 無效時）。
 *
 * 使用範例：
 * ```php
 * responses: [new UnauthorizedResponse(), ...]
 * ```
 */
class UnauthorizedResponse extends OA\Response
{
    public function __construct(string $description = '未授權，請先登入')
    {
        parent::__construct(
            response: Response::HTTP_UNAUTHORIZED,
            description: $description,
        );
    }
}
