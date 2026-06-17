<?php

namespace App\Docs\AdminApi\Routes;

use App\Docs\AdminApi\ResponseContents\Enum\EnumResponseContent;
use App\Docs\All\Responses\OkResponse;
use OpenApi\Attributes as OA;

class EnumController
{
    #[OA\Get(
        path: '/enums/permission',
        operationId: 'admin-api.enums.permission',
        summary: '取得權限 Enum 選項對應表',
        tags: ['Enum'],
        responses: [
            new OkResponse(contentRef: EnumResponseContent::class),
        ],
    )]
    /** 取得權限 Enum 選項對應表 */
    public function permission(): void
    {
    }
}
