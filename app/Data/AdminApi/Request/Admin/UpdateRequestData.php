<?php

namespace App\Data\AdminApi\Request\Admin;

use App\Enums\Admin\Status;
use App\Http\Requests\AdminApi\Admin\UpdateRequest;

readonly class UpdateRequestData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        public int $roleId,
        public Status $status,
        public ?int $mediaId,
    ) {
    }

    public static function fromRequest(UpdateRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            password: $request->password ?: null,
            roleId: $request->roleId,
            status: Status::from($request->status),
            mediaId: $request->mediaId,
        );
    }
}
