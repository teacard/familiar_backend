<?php

namespace App\Docs\Default\ResponseContents\Registration;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'Default.Registration.RegistrationTokenResponseContent')]
class RegistrationTokenResponseContent
{
    #[OA\Property(description: '註冊流程一次性 token（格式 {draftId}|{secret}），後續步驟需以 Authorization: Bearer 帶入', example: '1|abc123secret')]
    public string $token;
}
