<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Announcement\StoreRequest;
use App\Docs\AdminApi\Requests\Announcement\UpdateRequest;
use App\Docs\AdminApi\ResponseContents\Announcement\AnnouncementPaginatedResponseContent;
use App\Docs\AdminApi\ResponseContents\Announcement\AnnouncementResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\ForbiddenResponse;
use App\Docs\All\Responses\NotFoundResponse;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Enums\Announcement\Status;
use App\Enums\ApiCode;
use OpenApi\Attributes as OA;

class AnnouncementController
{
    #[OA\Get(
        path: '/announcements',
        operationId: 'admin-api.announcement.index',
        summary: '取得公告列表',
        security: [['sanctum' => []]],
        tags: [Tags::ANNOUNCEMENT],
        parameters: [
            new OA\Parameter(
                name: 'keyword',
                in: 'query',
                required: false,
                description: '關鍵字（比對標題）',
                schema: new OA\Schema(type: 'string', maxLength: 100, nullable: true, example: '維護'),
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
                        Status::DRAFT->value,
                        Status::SCHEDULED->value,
                        Status::PUBLISHED->value,
                        Status::EXPIRED->value,
                    ],
                    example: Status::PUBLISHED->value,
                ),
            ),
            new OA\Parameter(
                name: 'publishAtStart',
                in: 'query',
                required: false,
                description: '發布時間起（含）',
                schema: new OA\Schema(type: 'string', nullable: true, example: '2026-07-01 00:00:00'),
            ),
            new OA\Parameter(
                name: 'publishAtEnd',
                in: 'query',
                required: false,
                description: '發布時間迄（含）',
                schema: new OA\Schema(type: 'string', nullable: true, example: '2026-07-31 23:59:59'),
            ),
            new OA\Parameter(
                name: 'pinned',
                in: 'query',
                required: false,
                description: '是否置頂（true 只回置頂、false 只回未置頂）',
                schema: new OA\Schema(type: 'boolean', nullable: true, example: true),
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
            new OkResponse(contentRef: AnnouncementPaginatedResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
        ],
    )]
    public function index(): void
    {
    }

    #[OA\Post(
        path: '/announcements',
        operationId: 'admin-api.announcement.store',
        summary: '新增公告',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        tags: [Tags::ANNOUNCEMENT],
        responses: [
            new OkResponse(withoutContent: true),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new UnprocessableResponse(apiCodeEnums: [ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED]),
        ],
    )]
    public function store(): void
    {
    }

    #[OA\Get(
        path: '/announcements/{id}',
        operationId: 'admin-api.announcement.show',
        summary: '取得公告詳情',
        security: [['sanctum' => []]],
        tags: [Tags::ANNOUNCEMENT],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OkResponse(contentRef: AnnouncementResponseContent::class),
            new UnauthorizedResponse(),
            new ForbiddenResponse(),
            new NotFoundResponse(),
        ],
    )]
    public function show(): void
    {
    }

    #[OA\Put(
        path: '/announcements/{id}',
        operationId: 'admin-api.announcement.update',
        summary: '更新公告',
        security: [['sanctum' => []]],
        requestBody: new JsonContentRequestBody(contentRef: UpdateRequest::class),
        tags: [Tags::ANNOUNCEMENT],
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
            new UnprocessableResponse(apiCodeEnums: [
                ApiCode::ANNOUNCEMENT_PUBLISHED_IMMUTABLE,
                ApiCode::ANNOUNCEMENT_PIN_LIMIT_REACHED,
            ]),
        ],
    )]
    public function update(): void
    {
    }

    #[OA\Delete(
        path: '/announcements/{id}',
        operationId: 'admin-api.announcement.destroy',
        summary: '刪除公告',
        security: [['sanctum' => []]],
        tags: [Tags::ANNOUNCEMENT],
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
            new UnprocessableResponse(apiCodeEnums: [ApiCode::ANNOUNCEMENT_PUBLISHED_UNDELETABLE]),
        ],
    )]
    public function destroy(): void
    {
    }
}
