<?php

namespace App\Docs\Default\Requests\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Default.Registration.VerifyCodeRequest',
    required: ['code'],
)]
class VerifyCodeRequest
{
    #[OA\Property(description: '驗證碼（6 碼數字）', minLength: 6, maxLength: 6, example: '123456')]
    public string $code;
}
