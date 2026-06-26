<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Player\StoreRequest;
use App\Docs\AdminApi\Requests\Player\UpdateRequest;
use App\Docs\AdminApi\ResponseContents\Player\PlayerPaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\Player\PlayerShowResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Enums\ApiCode;
use App\Enums\Player\Status;
use OpenApi\Attributes as OA;

class PlayerController
{
    #[OA\Get(
        path: '/players',
        operationId: 'admin-api.player.index',
        summary: '取得遊戲會員列表',
        security: [['sanctum' => []]],
        tags: [Tags::PLAYER],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字（名稱／玩家 ID／Email）',
                schema: new OA\Schema(type: 'string', maxLength: 30, nullable: true, example: '生物蒐集家'),
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
            new OkResponse(contentRef: PlayerPaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void
    {
    }

    #[OA\Post(
        path: '/players',
        operationId: 'admin-api.player.store',
        summary: '新增遊戲會員',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        tags: [Tags::PLAYER],
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
        path: '/players/{id}',
        operationId: 'admin-api.player.show',
        summary: '取得遊戲會員詳情',
        security: [['sanctum' => []]],
        tags: [Tags::PLAYER],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(contentRef: PlayerShowResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function show(): void
    {
    }

    #[OA\Put(
        path: '/players/{id}',
        operationId: 'admin-api.player.update',
        summary: '更新遊戲會員',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
        tags: [Tags::PLAYER],
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
        path: '/players/{id}',
        operationId: 'admin-api.player.destroy',
        summary: '刪除遊戲會員（僅停用狀態可刪）',
        security: [['sanctum' => []]],
        tags: [Tags::PLAYER],
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
            new UnprocessableResponse(apiCodeEnums: [ApiCode::PLAYER_NOT_SUSPENDED]),
        ],
    )]
    public function destroy(): void
    {
    }
}
