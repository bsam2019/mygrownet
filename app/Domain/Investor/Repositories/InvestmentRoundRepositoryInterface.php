<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestmentRound;

/**
 * Investment Round Repository Interface
 */
interface InvestmentRoundRepositoryInterface
{
    public function save(InvestmentRound $round): InvestmentRound;
    
    public function findById(int $id): ?InvestmentRound;
    
    public function getFeaturedRound(): ?InvestmentRound;
    
    public function getActiveRounds(): array;
    
    public function getAll(): array;
    
    public function delete(int $id): void;
}
