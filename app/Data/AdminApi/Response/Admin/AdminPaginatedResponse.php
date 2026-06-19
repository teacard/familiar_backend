<?php

namespace App\Data\AdminApi\Response\Admin;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class AdminPaginatedResponse extends Data
{
    public function __construct(
        /** @var Collection<int, AdminResponse> */
        public Collection $items,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            items: AdminResponse::collect($paginator->getCollection()),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
