<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Admin\StoreRequest;
use App\Docs\AdminApi\Requests\Admin\UpdateRequest;
use App\Docs\AdminApi\Requests\Admin\UpdateStatusRequest;
use App\Docs\AdminApi\ResponseContents\Admin\AdminPaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\Admin\AdminResponseContent;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\AdminApi\Tags;
use App\Enums\Admin\Status;
use OpenApi\Attributes as OA;

class AdminController
{
    #[OA\Get(
        path: '/admin',
        operationId: 'admin-api.admin.index',
        summary: '取得後台人員列表',
        security: [['sanctum' => []]],
        tags: [Tags::ADMIN],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字',
                schema: new OA\Schema(type: 'string', maxLength: 30, nullable: true, example: '王小明'),
            ),
            new OA\Parameter(
                name: 'status',
                in: 'query',
                required: false,
                description: '狀態',
                schema: new OA\Schema(
                    type: 'string',
                    nullable: true,
                    enum: [
                        Status::ACTIVE->value,
                        Status::SUSPENDED->value,
                    ],
                    example: Status::ACTIVE->value,
                ),
            ),
            new OA\Parameter(
                name: 'roleId',
                in: 'query',
                required: false,
                description: '角色 ID',
                schema: new OA\Schema(type: 'integer', nullable: true, example: 1),
            ),
            new OA\Parameter(
                name: 'per_page',
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
            new OkResponse(contentRef: AdminPaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/admin',
        operationId: 'admin-api.admin.store',
        summary: '新增後台人員',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        tags: [Tags::ADMIN],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/admin/{id}',
        operationId: 'admin-api.admin.show',
        summary: '取得後台人員詳情',
        security: [['sanctum' => []]],
        tags: [Tags::ADMIN],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(contentRef: AdminResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function show(): void {}

    #[OA\Put(
        path: '/admin/{id}',
        operationId: 'admin-api.admin.update',
        summary: '更新後台人員',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
        tags: [Tags::ADMIN],
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
    public function update(): void {}

    #[OA\Patch(
        path: '/admin/{id}/status',
        operationId: 'admin-api.admin.update-status',
        summary: '更新後台人員狀態',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateStatusRequest::class),
        tags: [Tags::ADMIN],
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
    public function updateStatus(): void {}

    #[OA\Delete(
        path: '/admin/{id}',
        operationId: 'admin-api.admin.destroy',
        summary: '刪除後台人員',
        security: [['sanctum' => []]],
        tags: [Tags::ADMIN],
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
    public function destroy(): void {}
}
