<?php

namespace App\Docs\Default\Requests\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Default.Registration.StoreRequest',
    required: ['email'],
)]
class StoreRequest
{
    #[OA\Property(format: 'email', description: '電子郵件', example: 'player@example.com')]
    public string $email;
}
