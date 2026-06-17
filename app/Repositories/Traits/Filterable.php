<?php

namespace App\Repositories\Traits;

use App\Repositories\Contracts\FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait Filterable
{
    protected function applyFilter(Builder $query, array $filters = []): Builder
    {
        foreach ($filters as $filterName => $value) {
            if (is_null($value)) {
                continue;
            }

            $filterClass = $this->resolveFilterClass(static::class, $filterName);
            /** @var FilterInterface */
            app($filterClass)->apply($query, $value);
        }

        return $query;
    }

    protected function resolveFilterClass(string $repositoryClass, string $filterName): string
    {
        $namespace = (new \ReflectionClass($repositoryClass))->getNamespaceName();
        $filterClass = "{$namespace}\\Filters\\" . Str::studly($filterName);

        if (class_exists($filterClass) && is_subclass_of($filterClass, FilterInterface::class)) {
            return $filterClass;
        }

        $parent = get_parent_class($repositoryClass);
        if (false === $parent) {
            throw new \InvalidArgumentException("No filter found for [{$filterName}].");
        }

        return $this->resolveFilterClass($parent, $filterName);
    }

    protected function applyFilterQuery(array $filters = []): Builder
    {
        return $this->applyFilter($this->query(), $filters);
    }
}
