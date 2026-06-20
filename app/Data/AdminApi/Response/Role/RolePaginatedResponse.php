<?php

namespace App\Data\AdminApi\Response\Role;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class RolePaginatedResponse extends Data
{
    public function __construct(
        /** @var Collection<int, RoleIndexResponse> */
        public Collection $items,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            items: RoleIndexResponse::collect($paginator->getCollection()),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
