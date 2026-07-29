<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BuilderDouble extends Builder
{
    private array $returnValues = [];

    public function __construct()
    {
    }

    public function __clone()
    {
    }

    public function setReturn(string $method, mixed $value): void
    {
        $this->returnValues[$method] = $value;
    }

    private function getReturn(string $method, mixed $default = null): mixed
    {
        return array_key_exists($method, $this->returnValues) ? $this->returnValues[$method] : $default;
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and'): static
    {
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null, $boolean = 'or'): static
    {
        return $this;
    }

    public function latest($column = null): static
    {
        return $this;
    }

    public function whereHas($relation, $callback = null, $operator = '>=', $count = 1): static
    {
        return $this;
    }

    public function orWhereHas($relation, $callback = null, $operator = '>=', $count = 1): static
    {
        return $this;
    }

    public function get($columns = ['*']): Collection
    {
        return $this->getReturn('get', new Collection());
    }

    public function first($columns = ['*']): mixed
    {
        return $this->getReturn('first');
    }

    public function count($columns = '*'): int
    {
        return $this->getReturn('count', 0);
    }

    public function sum($column): int|float
    {
        return $this->getReturn('sum', 0);
    }

    public function avg($column): int|float
    {
        return $this->getReturn('avg', 0);
    }

    public function pluck($column, $key = null): Collection
    {
        return $this->getReturn('pluck', collect([]));
    }

    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null): LengthAwarePaginator
    {
        return $this->getReturn('paginate', new LengthAwarePaginator(collect(), 0, 15));
    }

    public function __call($method, $parameters): mixed
    {
        if (array_key_exists($method, $this->returnValues)) {
            return $this->returnValues[$method];
        }
        return $this;
    }
}
