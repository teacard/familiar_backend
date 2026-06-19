<?php

namespace App\Data\AdminApi\Response\Role;

use App\Models\Role;
use Spatie\LaravelData\Data;

class RoleIndexResponse extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $isDeletable,
    ) {}

    public static function fromModel(Role $role): self
    {
        return new self(
            id: $role->id,
            name: $role->name,
            // 沒有任何 admin 帳號使用此角色時才可刪除
            isDeletable: $role->admins->isEmpty(),
        );
    }
}
