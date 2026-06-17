<?php

namespace App\Docs\All\RequestBodies;

use OpenApi\Attributes as OA;

/**
 * 通用 JSON Request Body 包裝器。
 *
 * 用於需要傳入 JSON body 的 API，將 contentRef 指向對應的 Requests/ 類別。
 *
 * 使用範例：
 * ```php
 * requestBody: new JsonContentRequestBody(contentRef: LoginRequest::class),
 * ```
 */
class JsonContentRequestBody extends OA\RequestBody
{
    public function __construct(
        string|object|null $contentRef = null,
        ?array $contentRequired = null,
        ?array $contentProperties = null,
        ?string $description = null,
    ) {
        parent::__construct(
            description: $description,
            content: new OA\JsonContent(
                ref: $contentRef,
                required: $contentRequired,
                properties: $contentProperties,
            ),
        );
    }
}
