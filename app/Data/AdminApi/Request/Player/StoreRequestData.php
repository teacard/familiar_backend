<?php

namespace App\Data\AdminApi\Request\Player;

use App\Enums\Player\Status;
use App\Http\Requests\AdminApi\Player\StoreRequest;

readonly class StoreRequestData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone,
        public Status $status,
        public string $password,
    ) {
    }

    public static function fromRequest(StoreRequest $request): self
    {
        return new self(
            name: $request->name,
            email: $request->email,
            phone: $request->phone ?: null,
            status: Status::from($request->status),
            password: $request->password,
        );
    }
}
