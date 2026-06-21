<?php

namespace App\Data\AdminApi\Request\Admin;

use App\Enums\Admin\Status;
use App\Http\Requests\AdminApi\Admin\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public int $roleId,
        public Status $status,
        public ?int $mediaId,
    ) {
    }

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            password: $request->password,
            roleId: $request->roleId,
            status: Status::from($request->status),
            mediaId: $request->mediaId,
        );
    }
}
