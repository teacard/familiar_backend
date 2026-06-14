<?php

namespace App\Docs\All\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP 403 無訪問權限回應。
 *
 * 用於已登入但不具備對應權限的 API（例如非管理員存取後台）。
 *
 * 使用範例：
 * ```php
 * responses: [new ForbiddenResponse(), ...]
 * ```
 */
class ForbiddenResponse extends OA\Response
{
    public function __construct(string $description = '無訪問權限')
    {
        parent::__construct(
            response: Response::HTTP_FORBIDDEN,
            description: $description,
        );
    }
}
