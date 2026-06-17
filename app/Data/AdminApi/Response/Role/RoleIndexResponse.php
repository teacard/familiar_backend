<?php

namespace App\Data\AdminApi\Response\Role;

use App\Models\Role;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class RoleIndexResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        /** @var Collection<int, PermissionResponse> */
        public Collection $permissions,
    ) {
    }

    public static function fromModel(Role $role): self
    {
        return new self(
            id: $role->id,
            name: $role->name,
            permissions: PermissionResponse::collect($role->permissions),
        );
    }
}
