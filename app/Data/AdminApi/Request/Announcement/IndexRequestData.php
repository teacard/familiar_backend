<?php

namespace App\Data\AdminApi\Request\Announcement;

use App\Enums\Announcement\Status;
use App\Http\Requests\AdminApi\Announcement\IndexRequest;

readonly class IndexRequestData
{
    public function __construct(
        public ?string $keyword,
        public ?Status $status,
        public ?string $publishAtStart,
        public ?string $publishAtEnd,
        public ?bool $pinned,
        public int $perPage,
        public int $page,
    ) {
    }

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            keyword: $request->keyword,
            status: is_null($request->status) ? null : Status::from($request->status),
            publishAtStart: $request->publishAtStart,
            publishAtEnd: $request->publishAtEnd,
            pinned: is_null($request->pinned) ? null : $request->pinned,
            perPage: (int)($request->per_page ?? config('pagination.per_page.default')),
            page: (int)($request->page ?? config('pagination.page.default')),
        );
    }
}
