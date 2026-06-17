<?php

namespace App\Data\AdminApi\Request\Admin;

use App\Http\Requests\AdminApi\Admin\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int    $roleId,
    ) {}

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            password: $request->password,
            roleId: $request->roleId,
        );
    }
}
