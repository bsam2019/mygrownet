<?php

namespace App\Domain\Investor\Repositories;

use App\Domain\Investor\Entities\InvestorQuestionAnswer;

interface InvestorQuestionAnswerRepositoryInterface
{
    public function save(InvestorQuestionAnswer $answer): InvestorQuestionAnswer;

    public function findById(int $id): ?InvestorQuestionAnswer;

    public function findByQuestion(int $questionId): array;

    public function delete(int $id): bool;
}
