<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Role\StoreRequest;
use App\Docs\AdminApi\Requests\Role\UpdateRequest;
use App\Docs\AdminApi\ResponseContents\Role\RolePaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\Role\RoleShowResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Enums\ApiCode;
use OpenApi\Attributes as OA;

class RoleController
{
    #[OA\Get(
        path: '/roles',
        operationId: 'admin-api.role.index',
        summary: '角色管理-列表',
        security: [['sanctum' => []]],
        tags: [Tags::ROLE],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字（模糊搜尋角色名稱）',
                schema: new OA\Schema(type: 'string', maxLength: 50, nullable: true, example: '管理員'),
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
            new OkResponse(contentRef: RolePaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void
    {
    }

    #[OA\Post(
        path: '/roles',
        operationId: 'admin-api.role.store',
        summary: '角色管理-新增',
        security: [['sanctum' => []]],
        tags: [Tags::ROLE],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
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
        path: '/roles/{id}',
        operationId: 'admin-api.role.show',
        summary: '角色管理-詳情',
        security: [['sanctum' => []]],
        tags: [Tags::ROLE],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OkResponse(contentRef: RoleShowResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function show(): void
    {
    }

    #[OA\Put(
        path: '/roles/{id}',
        operationId: 'admin-api.role.update',
        summary: '角色管理-編輯',
        security: [['sanctum' => []]],
        tags: [Tags::ROLE],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
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
        path: '/roles/{id}',
        operationId: 'admin-api.role.destroy',
        summary: '角色管理-刪除',
        security: [['sanctum' => []]],
        tags: [Tags::ROLE],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
            new UnprocessableResponse(apiCodeEnums: [ApiCode::ROLE_IN_USE]),
        ],
    )]
    public function destroy(): void
    {
    }
}
