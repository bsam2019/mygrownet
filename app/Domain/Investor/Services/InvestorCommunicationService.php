<?php

namespace App\Domain\Investor\Services;

use App\Models\InvestorQuestion;
use App\Models\InvestorQuestionAnswer;
use App\Models\InvestorQuestionUpvote;
use App\Models\InvestorFeedback;
use App\Models\InvestorSurvey;
use App\Models\InvestorSurveyResponse;
use Illuminate\Support\Collection;

class InvestorCommunicationService
{
    // =====================================================
    // Q&A PORTAL
    // =====================================================

    public function submitQuestion(
        int $investorAccountId,
        string $subject,
        string $question,
        string $category,
        bool $isAnonymous = false
    ): InvestorQuestion {
        return InvestorQuestion::create([
            'investor_account_id' => $investorAccountId,
            'subject' => $subject,
            'question' => $question,
            'category' => $category,
            'status' => 'pending',
            'is_public' => !$isAnonymous,
        ]);
    }

    public function getPublishedQuestions(int $limit = 20): Collection
    {
        return InvestorQuestion::with(['latestAnswer', 'investorAccount'])
            ->where('status', 'published')
            ->orderBy('upvotes', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getFeaturedQuestions(): Collection
    {
        // Return top upvoted questions as "featured"
        return InvestorQuestion::with(['latestAnswer'])
            ->where('status', 'published')
            ->where('upvotes', '>', 0)
            ->orderBy('upvotes', 'desc')
            ->limit(5)
            ->get();
    }

    public function getQuestionsByCategory(string $category): Collection
    {
        return InvestorQuestion::with(['latestAnswer'])
            ->where('status', 'published')
            ->where('category', $category)
            ->orderBy('upvotes', 'desc')
            ->get();
    }

    public function getInvestorQuestions(int $investorAccountId): Collection
    {
        return InvestorQuestion::with(['latestAnswer'])
            ->where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function upvoteQuestion(int $questionId, int $investorAccountId): bool
    {
        $existing = InvestorQuestionUpvote::where('question_id', $questionId)
            ->where('investor_account_id', $investorAccountId)
            ->first();

        if ($existing) {
            return false;
        }

        InvestorQuestionUpvote::create([
            'question_id' => $questionId,
            'investor_account_id' => $investorAccountId,
            'upvoted_at' => now(),
        ]);

        InvestorQuestion::where('id', $questionId)->increment('upvotes');
        return true;
    }

    public function removeUpvote(int $questionId, int $investorAccountId): bool
    {
        $deleted = InvestorQuestionUpvote::where('question_id', $questionId)
            ->where('investor_account_id', $investorAccountId)
            ->delete();

        if ($deleted) {
            InvestorQuestion::where('id', $questionId)->decrement('upvotes');
            return true;
        }

        return false;
    }

    // =====================================================
    // FEEDBACK SYSTEM
    // =====================================================

    public function submitFeedback(
        int $investorAccountId,
        string $feedbackType,
        string $subject,
        string $message,
        ?int $rating = null
    ): InvestorFeedback {
        return InvestorFeedback::create([
            'investor_account_id' => $investorAccountId,
            'feedback_type' => $feedbackType,
            'category' => 'general',
            'subject' => $subject,
            'feedback' => $message,
            'satisfaction_rating' => $rating,
            'status' => 'submitted',
        ]);
    }

    public function getInvestorFeedback(int $investorAccountId): Collection
    {
        return InvestorFeedback::where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    // =====================================================
    // SURVEYS
    // =====================================================

    public function getActiveSurveys(): Collection
    {
        return InvestorSurvey::where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }

    public function getSurveyById(int $surveyId): ?InvestorSurvey
    {
        return InvestorSurvey::find($surveyId);
    }

    public function hasCompletedSurvey(int $surveyId, int $investorAccountId): bool
    {
        return InvestorSurveyResponse::where('survey_id', $surveyId)
            ->where('investor_account_id', $investorAccountId)
            ->exists();
    }

    public function submitSurveyResponse(
        int $surveyId,
        int $investorAccountId,
        array $answers
    ): InvestorSurveyResponse {
        $survey = InvestorSurvey::findOrFail($surveyId);

        if (!$survey->isActive()) {
            throw new \Exception('This survey is no longer active.');
        }

        if ($this->hasCompletedSurvey($surveyId, $investorAccountId)) {
            throw new \Exception('You have already completed this survey.');
        }

        return InvestorSurveyResponse::create([
            'survey_id' => $surveyId,
            'investor_account_id' => $survey->is_anonymous ? null : $investorAccountId,
            'answers' => $answers,
            'submitted_at' => now(),
        ]);
    }

    public function getInvestorSurveyResponses(int $investorAccountId): Collection
    {
        return InvestorSurveyResponse::with('survey')
            ->where('investor_account_id', $investorAccountId)
            ->orderBy('submitted_at', 'desc')
            ->get();
    }
}
