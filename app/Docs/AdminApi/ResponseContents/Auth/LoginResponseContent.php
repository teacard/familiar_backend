<?php

namespace App\Docs\AdminApi\ResponseContents\Auth;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminAuth.LoginResponseContent')]
class LoginResponseContent
{
    #[OA\Property(example: '1|abc123', description: '登入用token')]
    public string $token;
}
