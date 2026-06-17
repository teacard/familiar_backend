<?php

namespace App\Data\AdminApi\Response\Role;

use Spatie\LaravelData\Data;
use Spatie\Permission\Models\Permission;

class PermissionResponse extends Data
{
    public function __construct(
        public string $name,
        public string $label,
    ) {
    }

    public static function fromModel(Permission $permission): self
    {
        return new self(
            name: $permission->name,
            label: trans("permission.name.{$permission->name}"),
        );
    }
}
