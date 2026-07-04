<?php

namespace App\Docs\AdminApi\ResponseContents\Enum;

use App\Docs\All\Properties\EnumProperty;
use App\Enums\Announcement\TargetAudience;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Enum.AnnouncementTargetAudienceResponseContent')]
class AnnouncementTargetAudienceResponseContent
{
    #[EnumProperty(
        property: TargetAudience::SWAGGER_API_ENUM_PROPERTY,
        schemaEnumOptions: TargetAudience::SWAGGER_API_ENUM_OPTIONS,
    )]
    public object $targetAudience;
}
