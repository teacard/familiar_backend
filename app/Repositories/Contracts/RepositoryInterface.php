<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function get(array $filters = []): Collection;

    public function paginate(int $perPage, int $page, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Model;

    public function first(array $filters = []): ?Model;

    public function count(array $filters = []): int;

    public function exists(array $filters = []): bool;

    public function create(array $attributes): Model;

    public function update(array $filters, array $attributes): int;

    public function decrement(array $filters, string $column, int $amount = 1): int;

    public function delete(array $filters = []): int;
}
