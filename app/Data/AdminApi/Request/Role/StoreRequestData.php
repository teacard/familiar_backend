<?php

namespace App\Data\AdminApi\Request\Role;

use App\Http\Requests\AdminApi\Role\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $name,
        public array  $permissions,
    ) {}

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            name: $request->name,
            permissions: $request->permissions,
        );
    }
}
