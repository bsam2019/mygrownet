<?php

namespace App\Domain\ProfitSharing\Repositories;

use App\Domain\ProfitSharing\Entities\MemberProfitShare;

interface MemberProfitShareRepository
{
    public function save(MemberProfitShare $memberShare): MemberProfitShare;
    
    public function saveBatch(array $memberShares): void;
    
    public function findByQuarterlyProfitShareId(int $quarterlyProfitShareId): array;
    
    public function findByUserId(int $userId): array;
    
    public function findByUserIdAndQuarter(int $userId, int $quarterlyProfitShareId): ?MemberProfitShare;
}
