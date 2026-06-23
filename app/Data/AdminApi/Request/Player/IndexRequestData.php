<?php

namespace App\Data\AdminApi\Request\Player;

use App\Enums\Player\Status;
use App\Http\Requests\AdminApi\Player\IndexRequest;

readonly class IndexRequestData
{
    public function __construct(
        public ?string $keyword,
        public ?Status $status,
        public int $perPage,
        public int $page,
    ) {
    }

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            keyword: $request->keyword,
            status: is_null($request->status) ? null : Status::from($request->status),
            perPage: (int)($request->perPage ?? config('pagination.perPage.default')),
            page: (int)($request->page ?? config('pagination.page.default')),
        );
    }
}
