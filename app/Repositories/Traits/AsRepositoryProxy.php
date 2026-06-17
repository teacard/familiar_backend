<?php

namespace App\Repositories\Traits;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/** 將 Service 的標準 CRUD 呼叫透明代理至 Repository */
trait AsRepositoryProxy
{
    public function get(array $filters = []): Collection
    {
        return $this->getProxyRepository()->get($filters);
    }

    public function paginate(int $perPage, int $page, array $filters = []): LengthAwarePaginator
    {
        return $this->getProxyRepository()->paginate($perPage, $page, $filters);
    }

    public function findById(int $id): ?Model
    {
        return $this->getProxyRepository()->findById($id);
    }

    public function first(array $filters = []): ?Model
    {
        return $this->getProxyRepository()->first($filters);
    }

    public function create(array $attributes): Model
    {
        return $this->getProxyRepository()->create($attributes);
    }

    abstract protected function getProxyRepository(): RepositoryInterface;
}
