<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorPaymentMethod;

interface InvestorPaymentMethodRepositoryInterface
{
    public function save(InvestorPaymentMethod $method): InvestorPaymentMethod;

    public function findById(int $id): ?InvestorPaymentMethod;

    public function findPrimaryByInvestor(int $investorAccountId): ?InvestorPaymentMethod;

    public function findByInvestor(int $investorAccountId): array;

    public function setAllNonPrimary(int $investorAccountId): void;

    public function updateOrCreate(int $investorAccountId, string $methodType, array $data): InvestorPaymentMethod;
}
