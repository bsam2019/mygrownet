<?php

namespace App\Domain\Platform\Contracts;

interface SearchService
{
    public function search(string $query, array $filters = [], int $perPage = 20, int $page = 1): array;
    public function index(string $model, string $id, array $data): void;
    public function reindex(?string $model = null): void;
}
