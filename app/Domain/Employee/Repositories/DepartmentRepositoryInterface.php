<?php

declare(strict_types=1);

namespace App\Domain\Employee\Repositories;

use Illuminate\Support\Collection;

interface DepartmentRepositoryInterface
{
    public function findById(int $id): ?object;

    public function findByName(string $name): ?object;

    public function getAllActive(): Collection;

    public function getAll(): Collection;

    public function query();

    public function save(array $data): object;

    public function update(int $id, array $data): object;

    public function delete(int $id): void;
}
