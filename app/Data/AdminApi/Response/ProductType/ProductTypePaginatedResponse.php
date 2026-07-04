<?php

namespace App\Data\AdminApi\Response\ProductType;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ProductTypePaginatedResponse extends Data
{
    public function __construct(
        /** @var Collection<int, ProductTypeIndexResponse> */
        public Collection $items,
        public int $currentPage,
        public int $perPage,
        public int $lastPage,
    ) {
    }

    public static function fromPaginator(LengthAwarePaginator $paginator): self
    {
        return new self(
            items: ProductTypeIndexResponse::collect($paginator->getCollection()),
            currentPage: $paginator->currentPage(),
            perPage: $paginator->perPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
