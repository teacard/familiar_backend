<?php

namespace App\Docs\AdminApi\Requests\ProductType;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.ProductType.UpdateRequest',
    required: ['name'],
)]
class UpdateRequest
{
    #[OA\Property(description: '類別名稱', maxLength: 10, example: '道具')]
    public string $name;
}
