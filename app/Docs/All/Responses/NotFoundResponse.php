<?php

namespace App\Docs\All\Responses;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP 404 資源不存在回應。
 *
 * 用於查詢單筆資源時找不到對應紀錄的 API。
 *
 * 使用範例：
 * ```php
 * responses: [new NotFoundResponse(), ...]
 * ```
 */
class NotFoundResponse extends OA\Response
{
    public function __construct(string $description = '資源不存在')
    {
        parent::__construct(
            response: Response::HTTP_NOT_FOUND,
            description: $description,
        );
    }
}
