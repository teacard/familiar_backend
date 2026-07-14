<?php

namespace App\Docs\Default\Requests\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Default.Registration.ProfileRequest',
    required: ['name', 'mediaId'],
)]
class ProfileRequest
{
    #[OA\Property(description: '暱稱', maxLength: 50, example: '生物蒐集家')]
    public string $name;

    #[OA\Property(description: '大頭照媒體 ID（由上傳大頭照 API 回傳）', example: 1)]
    public int $mediaId;
}
