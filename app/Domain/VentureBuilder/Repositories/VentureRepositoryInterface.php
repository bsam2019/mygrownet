<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Venture;

interface VentureRepositoryInterface
{
    public function findById(int $id): ?Venture;
    public function findBySlug(string $slug): ?Venture;
    public function findFunding(int $perPage): array;
    public function findFeatured(int $limit): array;
    public function findAll(array $filters = [], int $perPage = 20): array;
    public function save(Venture $venture): Venture;
    public function updateStatus(int $id, string $status): void;
    public function incrementTotalRaised(int $id, float $amount): void;
    public function incrementInvestorCount(int $id): void;
    public function decrementTotalRaised(int $id, float $amount): void;
    public function decrementInvestorCount(int $id): void;
    public function getStats(): array;
}
