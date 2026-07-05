<?php

namespace App\Repositories\Applications\Product\Filters;

use App\Repositories\Contracts\FilterInterface;
use App\Repositories\Filters\Id;
use App\Repositories\Traits\HasRelatedFilters;
use Illuminate\Database\Eloquent\Builder;

class HasItem implements FilterInterface
{
    use HasRelatedFilters;

    public function __construct(
        protected Id $idFilter,
    ) {
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->whereHas(
            'item',
            fn (Builder $query) => $this->applyRelatedFilters(
                relatedFilters: [
                    'id' => $this->idFilter,
                ],
                query: $query,
                value: $value,
            ),
        );
    }
}
