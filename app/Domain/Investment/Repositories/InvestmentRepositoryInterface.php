<?php

declare(strict_types=1);

namespace App\Domain\Investment\Repositories;

use App\Domain\Investment\Entities\Investment;

interface InvestmentRepositoryInterface
{
    public function findById(int $id): ?Investment;

    public function save(Investment $investment): Investment;

    public function delete(int $id): bool;

    public function findByUser(int $userId): array;

    public function findActiveByUser(int $userId): array;

    public function findByStatus(string $status): array;

    public function findByOpportunity(int $opportunityId): array;
}
