<?php

namespace App\Data\AdminApi\Request\Product;

use App\Enums\Product\Status;
use App\Http\Requests\AdminApi\Product\IndexRequest;

readonly class IndexRequestData
{
    public function __construct(
        public ?string $keyword,
        public ?int $productTypeId,
        public ?Status $status,
        public int $perPage,
        public int $page,
    ) {
    }

    public static function fromRequest(IndexRequest $request): self
    {
        return new self(
            keyword: $request->keyword,
            productTypeId: is_null($request->productTypeId) ? null : (int)$request->productTypeId,
            status: is_null($request->status) ? null : Status::from($request->status),
            perPage: (int)($request->perPage ?? config('pagination.perPage.default')),
            page: (int)($request->page ?? config('pagination.page.default')),
        );
    }
}
