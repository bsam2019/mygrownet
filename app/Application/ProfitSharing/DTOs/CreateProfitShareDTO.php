<?php

namespace App\Application\ProfitSharing\DTOs;

class CreateProfitShareDTO
{
    public function __construct(
        public readonly int $year,
        public readonly int $quarter,
        public readonly float $totalProjectProfit,
        public readonly string $distributionMethod, // 'bp_based' or 'level_based'
        public readonly ?string $notes,
        public readonly int $createdBy
    ) {}
}
