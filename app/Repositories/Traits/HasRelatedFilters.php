<?php

namespace App\Repositories\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasRelatedFilters
{
    protected function applyRelatedFilters(
        array $relatedFilters,
        Builder $query,
        mixed $value,
    ): void {
        foreach ($relatedFilters as $filterName => $filter) {
            isset($value[$filterName]) && $filter->apply($query, $value[$filterName]);
        }
    }
}
