<?php

namespace App\Docs\AdminApi\ResponseContents\Permission;

use App\Docs\All\Properties\Permission\Label;
use App\Docs\All\Properties\Permission\Name;
use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'AdminApi.Permission.PermissionResponseContent', description: '權限')]
class PermissionResponseContent
{
    #[OA\Property]
    public Name $name;

    #[OA\Property]
    public Label $label;
}
