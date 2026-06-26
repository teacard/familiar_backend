<?php

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Repository implements RepositoryInterface
{
    use Filterable;

    protected Model $model;

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function get(array $filters = []): Collection
    {
        return $this->applyFilterQuery($filters)->get();
    }

    public function paginate(int $perPage, int $page, array $filters = []): LengthAwarePaginator
    {
        return $this->applyFilterQuery($filters)->paginate($perPage, ['*'], 'page', $page);
    }

    public function findById(int $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function first(array $filters = []): ?Model
    {
        return $this->applyFilterQuery($filters)->first();
    }

    public function count(array $filters = []): int
    {
        return $this->applyFilterQuery($filters)->count();
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(array $filters, array $attributes): int
    {
        if (empty($filters)) {
            return 0;
        }

        return $this->applyFilterQuery($filters)->update($attributes);
    }

    public function decrement(array $filters, string $column, int $amount = 1): int
    {
        if (empty($filters)) {
            return 0;
        }

        return $this->applyFilterQuery($filters)->decrement($column, $amount);
    }

    public function delete(array $filters = []): int
    {
        return $this->applyFilterQuery($filters)->delete();
    }
}
