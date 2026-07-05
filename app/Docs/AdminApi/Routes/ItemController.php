<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Item\StoreRequest;
use App\Docs\AdminApi\Requests\Item\UpdateRequest;
use App\Docs\AdminApi\ResponseContents\Item\ItemPaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\Item\ItemSelectResponseContent;
use App\Docs\AdminApi\ResponseContents\Item\ItemShowResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Enums\ApiCode;
use App\Enums\Item\Status;
use OpenApi\Attributes as OA;

class ItemController
{
    #[OA\Get(
        path: '/items',
        operationId: 'admin-api.item.index',
        summary: '道具管理-列表',
        security: [['sanctum' => []]],
        tags: [Tags::ITEM],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字（模糊搜尋道具名稱）',
                schema: new OA\Schema(type: 'string', maxLength: 10, nullable: true, example: '新手'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: '啟用狀態（不帶則回傳所有狀態）',
                schema: new OA\Schema(type: 'string', nullable: true, enum: [Status::class], example: Status::ACTIVE->value),
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
            new OkResponse(contentRef: ItemPaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void
    {
    }

    #[OA\Get(
        path: '/items/select',
        operationId: 'admin-api.item.select',
        summary: '道具管理-下拉選單',
        security: [['sanctum' => []]],
        tags: [Tags::ITEM],
        parameters: [
            new OA\Parameter(
                name: 'isActive',
                in: 'query',
                required: false,
                description: '是否僅回傳啟用中道具（true：僅啟用中，供建立/編輯商品獎勵時使用；不帶或 false：回傳所有狀態，供依道具篩選商品時使用）',
                schema: new OA\Schema(type: 'boolean', nullable: true, example: true),
            ),
        ],
        responses: [
            new OkResponse(contentItemsRef: ItemSelectResponseContent::class),
            new UnauthorizedResponse(),
        ],
    )]
    public function select(): void
    {
    }

    #[OA\Get(
        path: '/items/{id}',
        operationId: 'admin-api.item.show',
        summary: '道具管理-詳情',
        security: [['sanctum' => []]],
        tags: [Tags::ITEM],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(contentRef: ItemShowResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function show(): void
    {
    }

    #[OA\Post(
        path: '/items',
        operationId: 'admin-api.item.store',
        summary: '道具管理-新增',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        tags: [Tags::ITEM],
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
        path: '/items/{id}',
        operationId: 'admin-api.item.update',
        summary: '道具管理-編輯',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
        tags: [Tags::ITEM],
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
        path: '/items/{id}',
        operationId: 'admin-api.item.destroy',
        summary: '道具管理-刪除',
        security: [['sanctum' => []]],
        tags: [Tags::ITEM],
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
            new UnprocessableResponse(apiCodeEnums: [ApiCode::ITEM_IN_USE]),
        ],
    )]
    public function destroy(): void
    {
    }
}
