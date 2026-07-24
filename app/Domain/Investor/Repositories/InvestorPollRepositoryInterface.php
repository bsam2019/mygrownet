<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorPoll;

interface InvestorPollRepositoryInterface
{
    public function save(InvestorPoll $poll): InvestorPoll;

    public function findById(int $id): ?InvestorPoll;

    public function findActive(): array;
}
