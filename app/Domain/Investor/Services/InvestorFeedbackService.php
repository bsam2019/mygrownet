<?php

namespace App\Domain\Investor\Services;

use App\Models\InvestorFeedback;
use App\Models\InvestorSurvey;
use App\Models\InvestorSurveyResponse;
use App\Models\InvestorPoll;
use App\Models\InvestorPollVote;
use Illuminate\Pagination\LengthAwarePaginator;

class InvestorFeedbackService
{
    /**
     * Submit feedback
     */
    public function submitFeedback(int $investorAccountId, array $data): InvestorFeedback
    {
        return InvestorFeedback::create([
            'investor_account_id' => $investorAccountId,
            'feedback_type' => $data['feedback_type'],
            'category' => $data['category'],
            'subject' => $data['subject'],
            'feedback' => $data['feedback'],
            'satisfaction_rating' => $data['satisfaction_rating'] ?? null,
            'status' => 'submitted',
        ]);
    }

    /**
     * Get investor's feedback history
     */
    public function getInvestorFeedback(int $investorAccountId): LengthAwarePaginator
    {
        return InvestorFeedback::where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Respond to feedback (admin)
     */
    public function respondToFeedback(int $feedbackId, string $response): InvestorFeedback
    {
        $feedback = InvestorFeedback::findOrFail($feedbackId);
        
        $feedback->update([
            'admin_response' => $response,
            'status' => 'addressed',
            'responded_at' => now(),
        ]);

        return $feedback->fresh();
    }

    /**
     * Get active surveys for investor
     */
    public function getActiveSurveys(int $investorAccountId): array
    {
        $surveys = InvestorSurvey::active()->get();
        
        // Filter out already completed surveys
        $completedSurveyIds = InvestorSurveyResponse::where('investor_account_id', $investorAccountId)
            ->pluck('survey_id')
            ->toArray();

        return $surveys->filter(fn($s) => !in_array($s->id, $completedSurveyIds))
            ->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'description' => $s->description,
                'survey_type' => $s->survey_type,
                'questions' => $s->questions,
                'end_date' => $s->end_date->format('Y-m-d'),
                'is_anonymous' => $s->is_anonymous,
            ])
            ->values()
            ->toArray();
    }

    /**
     * Submit survey response
     */
    public function submitSurveyResponse(int $surveyId, int $investorAccountId, array $responses): InvestorSurveyResponse
    {
        // Check if already responded
        $existing = InvestorSurveyResponse::where('survey_id', $surveyId)
            ->where('investor_account_id', $investorAccountId)
            ->first();

        if ($existing) {
            throw new \Exception('You have already completed this survey.');
        }

        return InvestorSurveyResponse::create([
            'survey_id' => $surveyId,
            'investor_account_id' => $investorAccountId,
            'responses' => $responses,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Get active polls for investor
     */
    public function getActivePolls(int $investorAccountId): array
    {
        $polls = InvestorPoll::active()->get();
        
        // Get investor's votes
        $votedPollIds = InvestorPollVote::where('investor_account_id', $investorAccountId)
            ->pluck('poll_id')
            ->toArray();

        return $polls->map(fn($p) => [
            'id' => $p->id,
            'question' => $p->question,
            'options' => $p->options,
            'poll_type' => $p->poll_type,
            'end_date' => $p->end_date->format('Y-m-d'),
            'allow_multiple' => $p->allow_multiple,
            'has_voted' => in_array($p->id, $votedPollIds),
            'results' => in_array($p->id, $votedPollIds) ? $p->results : null,
            'total_votes' => $p->vote_count,
        ])->toArray();
    }

    /**
     * Submit poll vote
     */
    public function submitPollVote(int $pollId, int $investorAccountId, array $selectedOptions): InvestorPollVote
    {
        // Check if already voted
        $existing = InvestorPollVote::where('poll_id', $pollId)
            ->where('investor_account_id', $investorAccountId)
            ->first();

        if ($existing) {
            throw new \Exception('You have already voted in this poll.');
        }

        $poll = InvestorPoll::findOrFail($pollId);
        
        // Validate options
        if (!$poll->allow_multiple && count($selectedOptions) > 1) {
            throw new \Exception('This poll only allows one selection.');
        }

        return InvestorPollVote::create([
            'poll_id' => $pollId,
            'investor_account_id' => $investorAccountId,
            'selected_options' => $selectedOptions,
            'voted_at' => now(),
        ]);
    }

    /**
     * Get feedback statistics (admin)
     */
    public function getFeedbackStats(): array
    {
        $total = InvestorFeedback::count();
        $pending = InvestorFeedback::where('status', 'submitted')->count();
        $avgRating = InvestorFeedback::whereNotNull('satisfaction_rating')->avg('satisfaction_rating');

        $byType = InvestorFeedback::selectRaw('feedback_type, COUNT(*) as count')
            ->groupBy('feedback_type')
            ->pluck('count', 'feedback_type')
            ->toArray();

        return [
            'total' => $total,
            'pending' => $pending,
            'average_rating' => round($avgRating ?? 0, 1),
            'by_type' => $byType,
        ];
    }
}
