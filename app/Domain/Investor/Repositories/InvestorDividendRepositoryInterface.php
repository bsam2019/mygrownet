<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorDividend;

interface InvestorDividendRepositoryInterface
{
    public function save(InvestorDividend $dividend): InvestorDividend;

    public function findById(int $id): ?InvestorDividend;

    public function findByInvestor(int $investorAccountId): array;

    public function findByStatus(string $status): array;

    public function create(array $data): InvestorDividend;
}
