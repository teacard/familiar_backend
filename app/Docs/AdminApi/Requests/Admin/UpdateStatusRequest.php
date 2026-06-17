<?php

namespace App\Docs\AdminApi\Requests\Admin;

use App\Enums\Admin\Status as StatusEnum;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AdminApi.Admin.UpdateStatusRequest',
    required: ['status'],
)]
class UpdateStatusRequest
{
    #[OA\Property(description: '狀態', enum: [StatusEnum::class], example: StatusEnum::ACTIVE->value)]
    public string $status;
}
