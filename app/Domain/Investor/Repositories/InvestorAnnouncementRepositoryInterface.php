<?php

namespace App\Domain\Investor\Repositories;

interface InvestorAnnouncementRepositoryInterface
{
    public function getRecentAnnouncements(int $limit = 10): array;
}