<?php

namespace App\Data\AdminApi\Response\Role;

use App\Models\Role;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class RoleShowResponse extends Data
{
    public function __construct(
        public string $name,
        /** @var Collection<int, PermissionResponse> */
        public Collection $permissions,
    ) {}

    public static function fromModel(Role $role): self
    {
        return new self(
            name: $role->name,
            permissions: PermissionResponse::collect($role->permissions),
        );
    }
}
