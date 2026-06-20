<?php

namespace App\Data\AdminApi\Request\Admin;

use App\Enums\Admin\Status;
use App\Http\Requests\AdminApi\Admin\IndexRequest;

readonly class IndexRequestData
{
    public function __construct(
        public ?string $keyword,
        public ?Status $status,
        public ?int $roleId,
        public int $perPage,
        public int $page,
    ) {
    }

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            keyword: $request->keyword,
            status: is_null($request->status) ? null : Status::from($request->status),
            roleId: is_null($request->roleId) ? null : (int)$request->roleId,
            perPage: (int)($request->perPage ?? config('pagination.perPage.default')),
            page: (int)($request->page ?? config('pagination.page.default')),
        );
    }
}
