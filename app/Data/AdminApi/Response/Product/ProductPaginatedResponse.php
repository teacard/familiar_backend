<?php

namespace App\Data\AdminApi\Response\Product;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ProductPaginatedResponse extends Data
{
    public function __construct(
        /** @var Collection<int, ProductListResponse> */
        public Collection $items,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            items: ProductListResponse::collect($paginator->getCollection()),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
