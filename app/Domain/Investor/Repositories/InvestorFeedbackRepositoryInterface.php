<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorFeedback;

interface InvestorFeedbackRepositoryInterface
{
    public function save(InvestorFeedback $feedback): InvestorFeedback;

    public function findById(int $id): ?InvestorFeedback;

    public function findByInvestor(int $investorAccountId, int $perPage = 10): array;

    public function findAll(): array;

    public function countByStatus(string $status): int;

    public function getAverageRating(): float;

    public function countByType(): array;
}
