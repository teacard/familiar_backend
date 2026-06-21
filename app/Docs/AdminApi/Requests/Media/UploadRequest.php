<?php

namespace App\Docs\AdminApi\Requests\Media;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Media.UploadRequest',
    required: ['file', 'type'],
)]
class UploadRequest
{
    #[OA\Property(format: 'binary', description: '上傳檔案（jpg / png，上限 10MB）')]
    public string $file;
}
