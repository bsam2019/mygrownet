<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

interface ScenarioRepositoryInterface
{
    public function findById(int $id): ?array;

    public function findByBusiness(int $businessId): array;

    public function save(int $businessId, string $name, array $parameters, array $results): array;

    public function delete(int $id): void;
}
