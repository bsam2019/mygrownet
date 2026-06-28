<?php

namespace App\Domain\ProfitSharing\Repositories;

use App\Domain\ProfitSharing\Entities\QuarterlyProfitShare;
use App\Domain\ProfitSharing\ValueObjects\Quarter;

interface QuarterlyProfitShareRepository
{
    public function save(QuarterlyProfitShare $profitShare): QuarterlyProfitShare;
    
    public function findById(int $id): ?QuarterlyProfitShare;
    
    public function findByQuarter(Quarter $quarter): ?QuarterlyProfitShare;
    
    public function findAll(): array;
    
    public function findByStatus(string $status): array;
}
