<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorSurveyResponse;

interface InvestorSurveyResponseRepositoryInterface
{
    public function save(InvestorSurveyResponse $response): InvestorSurveyResponse;

    public function findById(int $id): ?InvestorSurveyResponse;

    public function findBySurvey(int $surveyId): array;

    public function findByInvestor(int $investorAccountId): array;

    public function hasCompleted(int $surveyId, int $investorAccountId): bool;

    public function findCompletedSurveyIds(int $investorAccountId): array;
}
