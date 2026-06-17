<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\Requests\Auth\LoginRequest;
use App\Docs\AdminApi\ResponseContents\Auth\LoginResponseContent;
use App\Docs\AdminApi\Tags;
use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Enums\Auth\ApiCode;
use OpenApi\Attributes as OA;

class AuthController
{
    #[OA\Post(
        path: '/auth/login',
        operationId: 'admin-api.auth.login',
        summary: '後台登入',
        tags: [Tags::AUTH],
        requestBody: new JsonContentRequestBody(contentRef: LoginRequest::class),
        responses: [
            new OkResponse(contentRef: LoginResponseContent::class),
            new UnprocessableResponse(apiCodeEnums: [ApiCode::INVALID_CREDENTIALS]),
        ],
    )]
    public function login(): void {}
}
