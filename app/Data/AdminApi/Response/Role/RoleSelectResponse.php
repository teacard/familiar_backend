<?php

namespace App\Data\AdminApi\Response\Role;

use App\Models\Role;
use Spatie\LaravelData\Data;

class RoleSelectResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }

    public static function fromModel(Role $role): self
    {
        return new self(
            id: $role->id,
            name: $role->name,
        );
    }
}
