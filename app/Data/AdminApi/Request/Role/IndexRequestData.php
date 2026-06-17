<?php

namespace App\Data\AdminApi\Request\Role;

use App\Http\Requests\AdminApi\Role\IndexRequest;

readonly class IndexRequestData
{
    public function __construct(
        public ?string $keyword,
    ) {
    }

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            keyword: $request->keyword,
        );
    }
}
