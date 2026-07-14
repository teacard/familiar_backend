<?php

namespace App\Http\Controllers\Api;

use App\Data\Api\Request\Registration\PasswordRequestData;
use App\Data\Api\Request\Registration\ProfileRequestData;
use App\Data\Api\Request\Registration\StoreRequestData;
use App\Data\Api\Request\Registration\VerifyCodeRequestData;
use App\Data\Api\Response\Registration\MediaResponse;
use App\Data\Api\Response\Registration\RegistrationStatusResponse;
use App\Data\Api\Response\Registration\RegistrationTokenResponse;
use App\Enums\Media\CollectionName;
use App\Enums\Media\UploadFileType;
use App\Enums\TemporaryMedia\SystemName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Registration\PasswordRequest;
use App\Http\Requests\Api\Registration\ProfileRequest;
use App\Http\Requests\Api\Registration\StoreRequest;
use App\Http\Requests\Api\Registration\UploadAvatarRequest;
use App\Http\Requests\Api\Registration\VerifyCodeRequest;
use App\Models\DraftPlayer;
use App\Services\MediaService;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function __construct(
        protected RegistrationService $registrationService,
        protected MediaService $mediaService,
    ) {
    }

    /** 玩家自助註冊-Step1提交 email */
    public function store(StoreRequest $request): JsonResponse
    {
        $token = $this->registrationService->submitEmail(
            StoreRequestData::fromRequest($request)
        );

        return $this->success(new RegistrationTokenResponse(token: $token));
    }

    /** 玩家自助註冊-Step2-A寄送驗證碼 */
    public function sendVerificationCode(DraftPlayer $draft): JsonResponse
    {
        $this->registrationService->sendVerificationCode($draft);

        return $this->success([]);
    }

    /** 玩家自助註冊-Step2-B驗證驗證碼 */
    public function verifyCode(VerifyCodeRequest $request, DraftPlayer $draft): JsonResponse
    {
        $this->registrationService->verifyCode(
            $draft,
            VerifyCodeRequestData::fromRequest($request)
        );

        return $this->success([]);
    }

    /** 玩家自助註冊-Step3上傳大頭照（選圖後立即呼叫，存入暫存集合，回傳 mediaId 供送出時使用） */
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $file = $request->file('avatar');

        $media = $this->mediaService->store(
            file: $file,
            type: UploadFileType::detectType($file->getMimeType()),
            systemName: SystemName::PLAYER,
        );

        return $this->success(MediaResponse::fromMedia($media));
    }

    /** 玩家自助註冊-Step3送出暱稱與大頭照（帶入 uploadAvatar 回傳的 mediaId） */
    public function updateProfile(ProfileRequest $request, DraftPlayer $draft): JsonResponse
    {
        $data = ProfileRequestData::fromRequest($request);

        DB::transaction(function () use ($draft, $data): void {
            $this->registrationService->updateProfile($draft, $data);

            $this->mediaService->transferToModel($data->mediaId, $draft, CollectionName::PLAYER);
        });

        return $this->success([]);
    }

    /** 玩家自助註冊-Step4設定密碼並完成註冊 */
    public function complete(PasswordRequest $request, DraftPlayer $draft): JsonResponse
    {
        $this->registrationService->complete($draft, PasswordRequestData::fromRequest($request));

        return $this->success([]);
    }

    /** 玩家自助註冊-查詢草稿進度 */
    public function show(DraftPlayer $draft): JsonResponse
    {
        return $this->success(RegistrationStatusResponse::fromModel($draft));
    }
}
