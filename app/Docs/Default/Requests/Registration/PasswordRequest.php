<?php

namespace App\Docs\Default\Requests\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Default.Registration.PasswordRequest',
    required: ['password', 'passwordConfirmation'],
)]
class PasswordRequest
{
    #[OA\Property(description: '密碼', minLength: 8, example: 'P@ssw0rd')]
    public string $password;

    #[OA\Property(description: '確認密碼', minLength: 8, example: 'P@ssw0rd')]
    public string $passwordConfirmation;
}
