<?php

namespace App\Docs\AdminApi\Requests\Admin;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Admin.StoreRequest',
    required: ['name', 'email', 'password', 'passwordConfirmation', 'roleId'],
)]
class StoreRequest
{
    #[OA\Property(description: '名稱', example: '王小明')]
    public string $name;

    #[OA\Property(format: 'email', description: '電子郵件', example: 'admin@example.com')]
    public string $email;

    #[OA\Property(description: '密碼', minLength: 8, example: 'P@ssw0rd')]
    public string $password;

    #[OA\Property(description: '確認密碼', minLength: 8, example: 'P@ssw0rd')]
    public string $passwordConfirmation;

    #[OA\Property(description: '角色 ID', example: 1)]
    public int $roleId;
}
