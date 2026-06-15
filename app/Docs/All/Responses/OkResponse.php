<?php

namespace App\Docs\All\Responses;

use OpenApi\Attributes as OA;
use OpenApi\Attributes\Items;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP 200 成功回應，內容統一包在 data 欄位內。
 *
 * - 無回傳內容：`new OkResponse(withoutContent: true)`
 * - 單筆物件：`new OkResponse(contentRef: FooResponseContent::class)`
 * - 陣列清單：`new OkResponse(contentItemsRef: FooResponseContent::class)`
 * - 自訂屬性：`new OkResponse(contentProperties: [new OA\Property(...)])`
 */
class OkResponse extends OA\Response
{
    public function __construct(
        string $description = '成功',
        bool $withoutContent = false,
        string|object|null $contentRef = null,
        ?array $contentProperties = null,
        string|object|null $contentItemsRef = null,
        ?array $contentItemsProperties = null,
    ) {
        $hasItems = !is_null($contentItemsRef) || !is_null($contentItemsProperties);

        parent::__construct(
            response: Response::HTTP_OK,
            description: $description,
            content: $withoutContent ? null : new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'data',
                        ref: (!$hasItems && !is_null($contentRef)) ? $contentRef : null,
                        type: $hasItems ? 'array' : (!is_null($contentProperties) ? 'object' : null),
                        properties: $contentProperties,
                        items: $hasItems ? new Items(
                            ref: $contentItemsRef,
                            properties: $contentItemsProperties,
                        ) : null,
                    ),
                ],
            ),
        );
    }
}
