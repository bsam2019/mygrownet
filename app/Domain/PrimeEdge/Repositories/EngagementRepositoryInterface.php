<?php

namespace App\Domain\PrimeEdge\Repositories;

use App\Domain\PrimeEdge\Entities\Engagement;
use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;

interface EngagementRepositoryInterface
{
    public function save(Engagement $engagement): void;
    public function findById(EngagementId $id): ?Engagement;
    public function findByClientId(ClientId $clientId): array;
    public function findAll(): array;
    public function findByStatus(string $status): array;
    public function findActiveByClientId(ClientId $clientId): array;
    public function nextId(): EngagementId;
}
