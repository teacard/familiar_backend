<?php

namespace App\Docs\AdminApi\ResponseContents\Enum;

use App\Docs\All\Properties\EnumProperty;
use App\Enums\Announcement\Status;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Enum.AnnouncementStatusResponseContent')]
class AnnouncementStatusResponseContent
{
    #[EnumProperty(
        property: Status::SWAGGER_API_ENUM_PROPERTY,
        schemaEnumOptions: Status::SWAGGER_API_ENUM_OPTIONS,
    )]
    public object $status;
}
