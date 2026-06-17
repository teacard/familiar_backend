<?php

namespace App\Data\AdminApi\Request\Role;

use App\Http\Requests\AdminApi\Role\UpdateRequest;

readonly class UpdateRequestData
{
    public function __construct(
        public string $name,
        public array $permissions,
    ) {
    }

    public static function fromRequest(UpdateRequest $request): self
    {
        return new self(
            name: $request->name,
            permissions: $request->permissions,
        );
    }
}
