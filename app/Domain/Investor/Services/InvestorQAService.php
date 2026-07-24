<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorQuestion;
use App\Domain\Investor\Entities\InvestorQuestionAnswer;
use App\Domain\Investor\Repositories\InvestorQuestionRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorQuestionAnswerRepositoryInterface;

class InvestorQAService
{
    public function __construct(
        private readonly InvestorQuestionRepositoryInterface $questionRepository,
        private readonly InvestorQuestionAnswerRepositoryInterface $answerRepository
    ) {}

    public function getInvestorQuestions(int $investorAccountId, ?string $status = null): array
    {
        return $this->questionRepository->findByInvestor($investorAccountId);
    }

    public function getPublicQuestions(?string $category = null, int $perPage = 15): array
    {
        return $this->questionRepository->findPublicByCategory($category, $perPage);
    }

    public function submitQuestion(int $investorAccountId, array $data): InvestorQuestion
    {
        $entity = InvestorQuestion::create(
            investorAccountId: $investorAccountId,
            subject: $data['subject'],
            question: $data['question'],
            category: $data['category'],
            isPublic: false
        );

        return $this->questionRepository->save($entity);
    }

    public function answerQuestion(int $questionId, int $userId, string $answer, ?array $attachments = null): InvestorQuestionAnswer
    {
        $question = $this->questionRepository->findById($questionId);

        if (!$question) {
            throw new \Exception('Question not found');
        }

        $answerEntity = InvestorQuestionAnswer::create(
            questionId: $questionId,
            answeredByUserId: $userId,
            answer: $answer,
            attachments: $attachments
        );

        $saved = $this->answerRepository->save($answerEntity);

        $question->answer();
        $this->questionRepository->save($question);

        return $saved;
    }

    public function upvoteQuestion(int $questionId): void
    {
        $this->questionRepository->incrementUpvotes($questionId);
    }

    public function makePublic(int $questionId): void
    {
        $question = $this->questionRepository->findById($questionId);

        if ($question) {
            $question->makePublic();
            $this->questionRepository->save($question);
        }
    }

    public function getCategoryCounts(): array
    {
        return $this->questionRepository->getCategoryCounts();
    }

    public function getPendingCount(): int
    {
        return $this->questionRepository->getPendingCount();
    }
}
