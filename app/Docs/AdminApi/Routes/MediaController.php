<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Media\UploadRequest;
use App\Docs\AdminApi\ResponseContents\Media\MediaResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnauthorizedResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use OpenApi\Attributes as OA;

class MediaController
{
    #[OA\Post(
        path: '/media',
        operationId: 'admin-api.media.store',
        summary: '上傳暫存媒體',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: UploadRequest::class),
            ),
        ),
        tags: [Tags::MEDIA],
        responses: [
            new OkResponse(contentRef: MediaResponseContent::class),
            new UnauthorizedResponse(),
            new UnprocessableResponse(),
        ],
    )]
    public function store(): void
    {
    }
}
