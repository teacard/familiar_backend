<?php

namespace App\Http\Controllers\AdminApi;

use App\Data\AdminApi\Response\Media\MediaResponse;
use App\Enums\Media\UploadFileType;
use App\Enums\TemporaryMedia\SystemName;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminApi\Media\UploadRequest;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function __construct(
        protected MediaService $mediaService,
    ) {
    }

    /** 媒體管理-上傳暫存檔案 */
    public function store(UploadRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $media = $this->mediaService->store(
            file: $file,
            type: UploadFileType::detectType($file->getMimeType()),
            systemName: SystemName::ADMIN,
        );

        return $this->success(MediaResponse::fromMedia($media));
    }
}
