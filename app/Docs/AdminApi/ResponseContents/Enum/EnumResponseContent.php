<?php

namespace App\Docs\AdminApi\ResponseContents\Enum;

use App\Docs\All\Properties\EnumProperty;
use App\Enums\Permission\Name as PermissionName;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Enum.PermissionResponseContent')]
class EnumResponseContent
{
    #[EnumProperty(
        property: PermissionName::SWAGGER_API_ENUM_PROPERTY,
        schemaEnumOptions: PermissionName::SWAGGER_API_ENUM_OPTIONS,
    )]
    public object $permissionName;
}
