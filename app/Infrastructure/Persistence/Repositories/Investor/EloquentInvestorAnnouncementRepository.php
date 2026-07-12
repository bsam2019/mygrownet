<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Repositories\InvestorAnnouncementRepositoryInterface;

class EloquentInvestorAnnouncementRepository implements InvestorAnnouncementRepositoryInterface
{
    public function getRecentAnnouncements(int $limit = 10): array
    {
        return [];
    }
}