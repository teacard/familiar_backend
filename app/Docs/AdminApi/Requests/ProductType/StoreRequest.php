<?php

namespace App\Docs\AdminApi\Requests\ProductType;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.ProductType.StoreRequest',
    required: ['name'],
)]
class StoreRequest
{
    #[OA\Property(description: '類別名稱', maxLength: 10, example: '道具')]
    public string $name;
}
