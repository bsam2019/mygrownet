<?php

namespace App\Domain\Investor\Services;

use App\Domain\Investor\Entities\InvestorFeedback;
use App\Domain\Investor\Entities\InvestorSurveyResponse;
use App\Domain\Investor\Entities\InvestorPollVote;
use App\Domain\Investor\Repositories\InvestorFeedbackRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorSurveyRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorSurveyResponseRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorPollRepositoryInterface;
use App\Domain\Investor\Repositories\InvestorPollVoteRepositoryInterface;

class InvestorFeedbackService
{
    public function __construct(
        private readonly InvestorFeedbackRepositoryInterface $feedbackRepository,
        private readonly InvestorSurveyRepositoryInterface $surveyRepository,
        private readonly InvestorSurveyResponseRepositoryInterface $surveyResponseRepository,
        private readonly InvestorPollRepositoryInterface $pollRepository,
        private readonly InvestorPollVoteRepositoryInterface $pollVoteRepository
    ) {}

    public function submitFeedback(int $investorAccountId, array $data): InvestorFeedback
    {
        $entity = InvestorFeedback::create(
            investorAccountId: $investorAccountId,
            feedbackType: $data['feedback_type'],
            category: $data['category'],
            subject: $data['subject'],
            feedback: $data['feedback'],
            satisfactionRating: $data['satisfaction_rating'] ?? null
        );

        return $this->feedbackRepository->save($entity);
    }

    public function getInvestorFeedback(int $investorAccountId): array
    {
        return $this->feedbackRepository->findByInvestor($investorAccountId);
    }

    public function respondToFeedback(int $feedbackId, string $response): InvestorFeedback
    {
        $feedback = $this->feedbackRepository->findById($feedbackId);

        if (!$feedback) {
            throw new \Exception('Feedback not found');
        }

        $feedback->respond($response);
        return $this->feedbackRepository->save($feedback);
    }

    public function getActiveSurveys(int $investorAccountId): array
    {
        $surveys = $this->surveyRepository->findActive();
        $completedIds = $this->surveyResponseRepository->findCompletedSurveyIds($investorAccountId);

        return array_values(array_filter(array_map(function ($s) use ($completedIds) {
            if (in_array($s->getId(), $completedIds)) {
                return null;
            }

            return [
                'id' => $s->getId(),
                'title' => $s->getTitle(),
                'description' => $s->getDescription(),
                'survey_type' => $s->getSurveyType(),
                'questions' => $s->getQuestions(),
                'end_date' => $s->getEndDate()->format('Y-m-d'),
                'is_anonymous' => $s->isAnonymous(),
            ];
        }, $surveys)));
    }

    public function submitSurveyResponse(int $surveyId, int $investorAccountId, array $responses): InvestorSurveyResponse
    {
        $existing = $this->surveyResponseRepository->hasCompleted($surveyId, $investorAccountId);

        if ($existing) {
            throw new \Exception('You have already completed this survey.');
        }

        $response = InvestorSurveyResponse::create(
            surveyId: $surveyId,
            investorAccountId: $investorAccountId,
            responses: $responses
        );

        return $this->surveyResponseRepository->save($response);
    }

    public function getActivePolls(int $investorAccountId): array
    {
        $polls = $this->pollRepository->findActive();
        $votedPollIds = $this->pollVoteRepository->findVotedPollIds($investorAccountId);

        return array_map(function ($p) use ($votedPollIds) {
            $hasVoted = in_array($p->getId(), $votedPollIds);
            return [
                'id' => $p->getId(),
                'question' => $p->getQuestion(),
                'options' => $p->getOptions(),
                'poll_type' => $p->getPollType(),
                'end_date' => $p->getEndDate()->format('Y-m-d'),
                'allow_multiple' => $p->allowMultiple(),
                'has_voted' => $hasVoted,
                'results' => $hasVoted ? $p->getResults() : null,
                'total_votes' => $p->getVoteCount(),
            ];
        }, $polls);
    }

    public function submitPollVote(int $pollId, int $investorAccountId, array $selectedOptions): InvestorPollVote
    {
        $existing = $this->pollVoteRepository->findByPollAndInvestor($pollId, $investorAccountId);

        if ($existing) {
            throw new \Exception('You have already voted in this poll.');
        }

        $poll = $this->pollRepository->findById($pollId);

        if (!$poll) {
            throw new \Exception('Poll not found.');
        }

        if (!$poll->allowMultiple() && count($selectedOptions) > 1) {
            throw new \Exception('This poll only allows one selection.');
        }

        $vote = InvestorPollVote::create(
            pollId: $pollId,
            investorAccountId: $investorAccountId,
            selectedOptions: $selectedOptions
        );

        return $this->pollVoteRepository->save($vote);
    }

    public function getFeedbackStats(): array
    {
        $allFeedback = $this->feedbackRepository->findAll();
        $total = count($allFeedback);
        $pending = $this->feedbackRepository->countByStatus('submitted');
        $avgRating = $this->feedbackRepository->getAverageRating();
        $byType = $this->feedbackRepository->countByType();

        return [
            'total' => $total,
            'pending' => $pending,
            'average_rating' => round($avgRating, 1),
            'by_type' => $byType,
        ];
    }
}
