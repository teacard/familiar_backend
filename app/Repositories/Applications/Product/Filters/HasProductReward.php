<?php

namespace App\Repositories\Applications\Product\Filters;

use App\Repositories\Contracts\FilterInterface;
use App\Repositories\Traits\HasRelatedFilters;
use Illuminate\Database\Eloquent\Builder;

class HasProductReward implements FilterInterface
{
    use HasRelatedFilters;

    public function __construct(
        protected HasItem $hasItemFilter,
    ) {
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->whereHas(
            'productRewards',
            fn (Builder $query) => $this->applyRelatedFilters(
                relatedFilters: [
                    'hasItem' => $this->hasItemFilter,
                ],
                query: $query,
                value: $value,
            ),
        );
    }
}
