<?php

namespace App\Docs\Default\ResponseContents\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Default.Registration.MediaResponseContent', description: '媒體')]
class MediaResponseContent
{
    #[OA\Property(description: '媒體 ID', example: 1)]
    public int $id;

    #[OA\Property(description: '媒體完整 URL', example: 'http://localhost/media/1/avatar_abc123.png')]
    public string $url;
}
