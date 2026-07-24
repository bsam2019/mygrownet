<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Repositories\InvestorQuestionRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorFeedbackRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorSurveyRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorSurveyResponseRepositoryInterface;
use App\Domain\Investor\Entities\InvestorQuestion;
use App\Domain\Investor\Entities\InvestorFeedback;
use App\Domain\Investor\Entities\InvestorSurveyResponse;

class InvestorCommunicationService
{
    public function __construct(
        private readonly InvestorQuestionRepositoryInterface $questionRepository,
        private readonly InvestorFeedbackRepositoryInterface $feedbackRepository,
        private readonly InvestorSurveyRepositoryInterface $surveyRepository,
        private readonly InvestorSurveyResponseRepositoryInterface $surveyResponseRepository
    ) {}

    public function submitQuestion(
        int $investorAccountId,
        string $subject,
        string $question,
        string $category,
        bool $isAnonymous = false
    ): InvestorQuestion {
        $entity = InvestorQuestion::create(
            investorAccountId: $investorAccountId,
            subject: $subject,
            question: $question,
            category: $category,
            isPublic: !$isAnonymous
        );

        return $this->questionRepository->save($entity);
    }

    public function getPublishedQuestions(int $limit = 20): array
    {
        return $this->questionRepository->findPublished($limit);
    }

    public function getFeaturedQuestions(): array
    {
        return $this->questionRepository->findFeatured();
    }

    public function getQuestionsByCategory(string $category): array
    {
        return $this->questionRepository->findByCategory($category);
    }

    public function getInvestorQuestions(int $investorAccountId): array
    {
        return $this->questionRepository->findByInvestor($investorAccountId);
    }

    public function upvoteQuestion(int $questionId, int $investorAccountId): bool
    {
        return $this->questionRepository->upvote($questionId, $investorAccountId);
    }

    public function removeUpvote(int $questionId, int $investorAccountId): bool
    {
        return $this->questionRepository->removeUpvote($questionId, $investorAccountId);
    }

    public function submitFeedback(
        int $investorAccountId,
        string $feedbackType,
        string $subject,
        string $message,
        ?int $rating = null
    ): InvestorFeedback {
        $entity = InvestorFeedback::create(
            investorAccountId: $investorAccountId,
            feedbackType: $feedbackType,
            category: 'general',
            subject: $subject,
            feedback: $message,
            satisfactionRating: $rating
        );

        return $this->feedbackRepository->save($entity);
    }

    public function getInvestorFeedback(int $investorAccountId): array
    {
        return $this->feedbackRepository->findByInvestor($investorAccountId);
    }

    public function getActiveSurveys(): array
    {
        return $this->surveyRepository->findActive();
    }

    public function getSurveyById(int $surveyId)
    {
        return $this->surveyRepository->findById($surveyId);
    }

    public function hasCompletedSurvey(int $surveyId, int $investorAccountId): bool
    {
        return $this->surveyResponseRepository->hasCompleted($surveyId, $investorAccountId);
    }

    public function submitSurveyResponse(
        int $surveyId,
        int $investorAccountId,
        array $answers
    ): InvestorSurveyResponse {
        $survey = $this->surveyRepository->findById($surveyId);

        if (!$survey || !$survey->isActive()) {
            throw new \Exception('This survey is no longer active.');
        }

        if ($this->hasCompletedSurvey($surveyId, $investorAccountId)) {
            throw new \Exception('You have already completed this survey.');
        }

        $response = InvestorSurveyResponse::create(
            surveyId: $surveyId,
            investorAccountId: $survey->isAnonymous() ? null : $investorAccountId,
            responses: $answers
        );

        return $this->surveyResponseRepository->save($response);
    }

    public function getInvestorSurveyResponses(int $investorAccountId): array
    {
        return $this->surveyResponseRepository->findByInvestor($investorAccountId);
    }
}
