<?php

namespace App\Docs\AdminApi\Requests\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminAuth.LoginRequest',
    required: ['email', 'password', 'recaptcha_token'],
)]
class LoginRequest
{
    #[OA\Property(format: 'email', example: 'admin@example.com', description: '電子郵件')]
    public string $email;

    #[OA\Property(example: 'secret123', description: '密碼')]
    public string $password;

    #[OA\Property(example: '03AGdBq27...', description: '前端 reCAPTCHA v3 產生的驗證 token')]
    public string $recaptcha_token;
}
