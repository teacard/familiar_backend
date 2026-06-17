<?php

namespace App\Data\AdminApi\Request\Admin;

use App\Enums\Admin\Status;
use App\Http\Requests\AdminApi\Admin\UpdateStatusRequest;

readonly class UpdateStatusRequestData
{
    public function __construct(
        public Status $status,
    ) {
    }

    public static function fromRequest(UpdateStatusRequest $request): self
    {
        return new self(
            status: Status::from($request->status),
        );
    }
}
