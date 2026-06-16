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

    public function create(array $attributes): Model;
}
