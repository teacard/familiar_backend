<?php

namespace App\Docs\Default\Requests\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Default.Registration.UploadAvatarRequest',
    required: ['avatar'],
)]
class UploadAvatarRequest
{
    #[OA\Property(format: 'binary', description: '大頭照檔案（jpg / png，上限 10MB）')]
    public string $avatar;
}
