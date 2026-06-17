<?php

namespace App\Docs\All\Responses;

use App\Docs\All\Responses\Traits\HasApiCodes;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class UnprocessableResponse extends OA\Response
{
    use HasApiCodes;

    public function __construct(
        string $description = '驗證失敗',
        array $apiCodeEnums = [],
    ) {
        parent::__construct(
            response: Response::HTTP_UNPROCESSABLE_ENTITY,
            description: $this->suffixApiCodeAndMessages(
                description: $description,
                apiCodeEnums: $apiCodeEnums,
            ),
        );
    }
}
