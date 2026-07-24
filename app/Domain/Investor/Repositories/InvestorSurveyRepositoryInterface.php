<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorSurvey;

interface InvestorSurveyRepositoryInterface
{
    public function save(InvestorSurvey $survey): InvestorSurvey;

    public function findById(int $id): ?InvestorSurvey;

    public function findActive(): array;

    public function findAll(): array;
}
