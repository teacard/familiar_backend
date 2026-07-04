<?php

namespace App\Docs\AdminApi\ResponseContents\Media;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Media.MediaResponseContent', description: '媒體')]
class MediaResponseContent
{
    #[OA\Property(description: '媒體 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '媒體完整 URL', example: 'http://localhost/media/1/avatar_abc123.png')]
    public string $url;
}
