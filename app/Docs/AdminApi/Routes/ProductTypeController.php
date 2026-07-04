<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\ProductType\StoreRequest;
use App\Docs\AdminApi\Requests\ProductType\UpdateRequest;
use App\Docs\AdminApi\ResponseContents\ProductType\ProductTypePaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\ProductType\ProductTypeSelectResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Enums\ApiCode;
use OpenApi\Attributes as OA;

class ProductTypeController
{
    #[OA\Get(
        path: '/product-types',
        operationId: 'admin-api.product-type.index',
        summary: '商品類別管理-列表',
        security: [['sanctum' => []]],
        tags: [Tags::PRODUCT_TYPE],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字（模糊搜尋類別名稱）',
                schema: new OA\Schema(type: 'string', maxLength: 10, nullable: true, example: '道具'),
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
            new OkResponse(contentRef: ProductTypePaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/product-types/select',
        operationId: 'admin-api.product-type.select',
        summary: '商品類別管理-下拉選單',
        security: [['sanctum' => []]],
        tags: [Tags::PRODUCT_TYPE],
        responses: [
            new OkResponse(contentItemsRef: ProductTypeSelectResponseContent::class),
            new UnauthorizedResponse(),
        ],
    )]
    public function select(): void
    {
    }

    #[OA\Post(
        path: '/product-types',
        operationId: 'admin-api.product-type.store',
        summary: '商品類別管理-新增',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        tags: [Tags::PRODUCT_TYPE],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function store(): void
    {
    }

    #[OA\Put(
        path: '/product-types/{id}',
        operationId: 'admin-api.product-type.update',
        summary: '商品類別管理-編輯',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
        tags: [Tags::PRODUCT_TYPE],
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
        path: '/product-types/{id}',
        operationId: 'admin-api.product-type.destroy',
        summary: '商品類別管理-刪除',
        security: [['sanctum' => []]],
        tags: [Tags::PRODUCT_TYPE],
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
            new UnprocessableResponse(apiCodeEnums: [ApiCode::PRODUCT_TYPE_IN_USE]),
        ],
    )]
    public function destroy(): void
    {
    }
}
