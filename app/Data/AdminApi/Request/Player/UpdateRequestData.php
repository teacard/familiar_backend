<?php

namespace App\Data\AdminApi\Request\Player;

use App\Enums\Player\Status;
use App\Http\Requests\AdminApi\Player\UpdateRequest;

readonly class UpdateRequestData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public Status $status,
        public ?string $password,
    ) {
    }

    public static function fromRequest(UpdateRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            phone: $request->phone ?: null,
            status: Status::from($request->status),
            password: $request->password ?: null,
        );
    }
}
