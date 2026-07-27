<?php

namespace App\Domain\PlatformPayments\Repositories;

use App\Domain\PlatformPayments\Entities\Settlement;

interface SettlementRepositoryInterface
{
    public function findById(int $id): ?Settlement;
    public function findByOrganization(int $organizationId): array;
    public function findByProvider(string $provider, \DateTimeImmutable $from, \DateTimeImmutable $to): array;
    public function findUnreconciled(): array;
    public function save(Settlement $settlement): Settlement;
}
