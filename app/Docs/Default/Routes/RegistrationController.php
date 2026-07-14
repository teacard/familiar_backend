<?php

namespace App\Docs\Default\Routes;

use App\Docs\All\RequestBodies\JsonContentRequestBody;
use App\Docs\All\Responses\OkResponse;
use App\Docs\All\Responses\UnprocessableResponse;
use App\Docs\Default\Requests\Registration\PasswordRequest;
use App\Docs\Default\Requests\Registration\ProfileRequest;
use App\Docs\Default\Requests\Registration\StoreRequest;
use App\Docs\Default\Requests\Registration\UploadAvatarRequest;
use App\Docs\Default\Requests\Registration\VerifyCodeRequest;
use App\Docs\Default\ResponseContents\Registration\MediaResponseContent;
use App\Docs\Default\ResponseContents\Registration\RegistrationStatusResponseContent;
use App\Docs\Default\ResponseContents\Registration\RegistrationTokenResponseContent;
use App\Docs\Default\Tags;
use App\Enums\ApiCode;
use OpenApi\Attributes as OA;

class RegistrationController
{
    #[OA\Post(
        path: '/registrations',
        operationId: 'default.registration.store',
        summary: 'Step1-提交 email',
        description: '建立或延續草稿並換發一次性 token，不寄送驗證碼、不需帶入 token',
        tags: [Tags::REGISTRATION],
        requestBody: new JsonContentRequestBody(contentRef: StoreRequest::class),
        responses: [
            new OkResponse(contentRef: RegistrationTokenResponseContent::class),
            new UnprocessableResponse(apiCodeEnums: [ApiCode::EMAIL_ALREADY_REGISTERED]),
        ],
    )]
    public function store(): void
    {
    }

    #[OA\Post(
        path: '/registrations/verification-code',
        operationId: 'default.registration.send-verification-code',
        summary: 'Step2-A-寄送/重寄驗證碼',
        description: '首次寄送與重寄共用同一支 API，60 秒內重複呼叫會被拒絕；同 IP 另受每分鐘 6 次的頻率限制',
        security: [['registrationToken' => []]],
        tags: [Tags::REGISTRATION],
        responses: [
            new OkResponse(withoutContent: true),
            new UnprocessableResponse(apiCodeEnums: [
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
                ApiCode::VERIFICATION_CODE_RESEND_TOO_SOON,
            ]),
        ],
    )]
    public function sendVerificationCode(): void
    {
    }

    #[OA\Post(
        path: '/registrations/verification-code/verify',
        operationId: 'default.registration.verify-code',
        summary: 'Step2-B-驗證驗證碼',
        security: [['registrationToken' => []]],
        tags: [Tags::REGISTRATION],
        requestBody: new JsonContentRequestBody(contentRef: VerifyCodeRequest::class),
        responses: [
            new OkResponse(withoutContent: true),
            new UnprocessableResponse(apiCodeEnums: [
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
                ApiCode::VERIFICATION_CODE_NOT_SENT,
                ApiCode::VERIFICATION_CODE_LOCKED,
                ApiCode::VERIFICATION_CODE_EXPIRED,
                ApiCode::VERIFICATION_CODE_INVALID,
            ]),
        ],
    )]
    public function verifyCode(): void
    {
    }

    #[OA\Post(
        path: '/registrations/profile/avatar',
        operationId: 'default.registration.upload-avatar',
        summary: 'Step3-A-上傳大頭照',
        description: '選定圖片後立即呼叫，回傳的 mediaId 供 Step3-B 送出表單時使用',
        security: [['registrationToken' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: UploadAvatarRequest::class),
            ),
        ),
        tags: [Tags::REGISTRATION],
        responses: [
            new OkResponse(contentRef: MediaResponseContent::class),
            new UnprocessableResponse(apiCodeEnums: [ApiCode::REGISTRATION_DRAFT_NOT_FOUND]),
        ],
    )]
    public function uploadAvatar(): void
    {
    }

    #[OA\Post(
        path: '/registrations/profile',
        operationId: 'default.registration.update-profile',
        summary: 'Step3-B-送出暱稱與大頭照',
        description: 'mediaId 需為 Step3-A 回傳且仍存在的媒體 id',
        security: [['registrationToken' => []]],
        requestBody: new JsonContentRequestBody(contentRef: ProfileRequest::class),
        tags: [Tags::REGISTRATION],
        responses: [
            new OkResponse(withoutContent: true),
            new UnprocessableResponse(apiCodeEnums: [
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
                ApiCode::REGISTRATION_STEP_SKIPPED,
            ]),
        ],
    )]
    public function updateProfile(): void
    {
    }

    #[OA\Post(
        path: '/registrations/password',
        operationId: 'default.registration.complete',
        summary: 'Step4-設定密碼並完成註冊',
        security: [['registrationToken' => []]],
        requestBody: new JsonContentRequestBody(contentRef: PasswordRequest::class),
        tags: [Tags::REGISTRATION],
        responses: [
            new OkResponse(withoutContent: true),
            new UnprocessableResponse(apiCodeEnums: [
                ApiCode::REGISTRATION_DRAFT_NOT_FOUND,
                ApiCode::REGISTRATION_STEP_SKIPPED,
            ]),
        ],
    )]
    public function complete(): void
    {
    }

    #[OA\Get(
        path: '/registrations',
        operationId: 'default.registration.show',
        summary: '查詢草稿進度',
        security: [['registrationToken' => []]],
        tags: [Tags::REGISTRATION],
        responses: [
            new OkResponse(contentRef: RegistrationStatusResponseContent::class),
            new UnprocessableResponse(apiCodeEnums: [ApiCode::REGISTRATION_DRAFT_NOT_FOUND]),
        ],
    )]
    public function show(): void
    {
    }
}
