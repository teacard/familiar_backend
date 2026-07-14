<?php

namespace App\Docs\Default\ResponseContents\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Default.Registration.RegistrationStatusResponseContent')]
class RegistrationStatusResponseContent
{
    #[OA\Property(description: '目前註冊進度（1：已提交email／2：驗證碼已驗證／3：暱稱與大頭照已填寫完成）', example: 2)]
    public int $registrationStep;

    #[OA\Property(format: 'email', description: '電子郵件', example: 'player@example.com')]
    public string $email;

    #[OA\Property(description: '暱稱（尚未填寫則為 null）', example: '生物蒐集家')]
    public ?string $name;

    #[OA\Property(description: '大頭照完整 URL（尚未上傳則為 null）', example: 'http://localhost/media/1/avatar_abc123.png')]
    public ?string $avatarUrl;
}
