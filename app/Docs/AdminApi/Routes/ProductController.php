<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Product\StoreRequest;
use App\Docs\AdminApi\Requests\Product\UpdateRequest;
use App\Docs\AdminApi\ResponseContents\Product\ProductPaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\Product\ProductResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Enums\Product\Status;
use OpenApi\Attributes as OA;

class ProductController
{
    #[OA\Get(
        path: '/products',
        operationId: 'admin-api.product.index',
        summary: '取得商品列表',
        security: [['sanctum' => []]],
        tags: [Tags::PRODUCT],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字（比對商品名稱）',
                schema: new OA\Schema(type: 'string', maxLength: 50, nullable: true, example: '禮包'),
            ),
            new OA\Parameter(
                name: 'productTypeId',
                in: 'query',
                required: false,
                description: '商品類別 ID（對應 product_types.id）',
                schema: new OA\Schema(type: 'integer', nullable: true, example: 1),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: '上架狀態（不帶則回傳所有狀態）',
                schema: new OA\Schema(type: 'string', nullable: true, enum: [Status::class], example: Status::PUBLISHED->value),
            ),
            new OA\Parameter(
                name: 'perPage',
                in: 'query',
                required: false,
                description: '分頁筆數',
                schema: new OA\Schema(type: 'integer', nullable: true, enum: [10, 25, 50], example: 10),
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: '頁數',
                schema: new OA\Schema(type: 'integer', nullable: true, minimum: 1, example: 1),
            ),
        ],
        responses: [
            new OkResponse(contentRef: ProductPaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void
    {
    }

    #[OA\Post(
        path: '/products',
        operationId: 'admin-api.product.store',
        summary: '新增商品',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        tags: [Tags::PRODUCT],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function store(): void
    {
    }

    #[OA\Get(
        path: '/products/{id}',
        operationId: 'admin-api.product.show',
        summary: '取得商品詳情',
        security: [['sanctum' => []]],
        tags: [Tags::PRODUCT],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(contentRef: ProductResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function show(): void
    {
    }

    #[OA\Put(
        path: '/products/{id}',
        operationId: 'admin-api.product.update',
        summary: '更新商品',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
        tags: [Tags::PRODUCT],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/products/{id}',
        operationId: 'admin-api.product.destroy',
        summary: '刪除商品',
        security: [['sanctum' => []]],
        tags: [Tags::PRODUCT],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function destroy(): void
    {
    }
}
