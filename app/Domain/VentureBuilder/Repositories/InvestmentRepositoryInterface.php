<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Investment;

interface InvestmentRepositoryInterface
{
    public function findById(int $id): ?Investment;
    public function findByVenture(int $ventureId, array $filters = [], int $perPage = 20): array;
    public function findByUser(int $userId, array $filters = [], int $perPage = 20): array;
    public function findPending(): array;
    public function findConfirmedByUserAndVenture(int $userId, int $ventureId): array;
    public function save(Investment $investment): Investment;
    public function updateStatus(int $id, string $status, ?array $extra = null): void;
    public function getTotalInvestedByUser(int $userId, int $ventureId): float;
}
