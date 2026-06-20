<?php

namespace App\Data\AdminApi\Request\Role;

use App\Http\Requests\AdminApi\Role\IndexRequest;

readonly class IndexRequestData
{
    public function __construct(
        public ?string $keyword,
        public int $perPage,
        public int $page,
    ) {
    }

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            keyword: $request->keyword,
            perPage: (int)($request->perPage ?? config('pagination.perPage.default')),
            page: (int)($request->page ?? config('pagination.page.default')),
        );
    }
}
