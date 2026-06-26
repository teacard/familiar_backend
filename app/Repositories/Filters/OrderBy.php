<?php

namespace App\Repositories\Filters;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class OrderBy implements FilterInterface
{
    /**
     * 通用升冪排序 filter。
     *
     * 值可為欄位字串 `'order_by'` 或欄位陣列 `['order_by', 'id']`。
     */
    public function apply(Builder $query, mixed $value): Builder
    {
        foreach ((array)$value as $column) {
            $query->orderBy($column);
        }

        return $query;
    }
}
