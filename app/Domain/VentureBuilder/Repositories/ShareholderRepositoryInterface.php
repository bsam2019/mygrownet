<?php

declare(strict_types=1);

namespace App\Domain\VentureBuilder\Repositories;

use App\Domain\VentureBuilder\Entities\Shareholder;

interface ShareholderRepositoryInterface
{
    public function findById(int $id): ?Shareholder;
    public function findByVenture(int $ventureId): array;
    public function findByUserAndVenture(int $userId, int $ventureId): ?Shareholder;
    public function findActiveByVenture(int $ventureId): array;
    public function findActiveByUserAndVenture(int $userId, int $ventureId): ?Shareholder;
    public function save(Shareholder $shareholder): Shareholder;
    public function decrementShares(int $id, float $shares): void;
    public function decrementInvestment(int $id, float $amount): void;
    public function incrementShares(int $id, float $shares): void;
    public function incrementInvestment(int $id, float $amount): void;
    public function updateEquity(int $id, float $percentage): void;
    public function getTotalSharesByVenture(int $ventureId): float;
}
