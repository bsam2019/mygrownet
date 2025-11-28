<?php

namespace App\Domain\Investor\Services;

use App\Models\InvestorQuestion;
use App\Models\InvestorQuestionAnswer;
use Illuminate\Pagination\LengthAwarePaginator;

class InvestorQAService
{
    /**
     * Get questions for an investor
     */
    public function getInvestorQuestions(int $investorAccountId, ?string $status = null): LengthAwarePaginator
    {
        $query = InvestorQuestion::where('investor_account_id', $investorAccountId)
            ->with(['answers.answeredBy', 'latestAnswer'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(10);
    }

    /**
     * Get public Q&A (FAQ)
     */
    public function getPublicQuestions(?string $category = null): LengthAwarePaginator
    {
        $query = InvestorQuestion::where('is_public', true)
            ->where('status', 'answered')
            ->with(['latestAnswer'])
            ->orderBy('upvotes', 'desc')
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->paginate(15);
    }

    /**
     * Submit a new question
     */
    public function submitQuestion(int $investorAccountId, array $data): InvestorQuestion
    {
        return InvestorQuestion::create([
            'investor_account_id' => $investorAccountId,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'question' => $data['question'],
            'status' => 'pending',
            'is_public' => false,
            'upvotes' => 0,
        ]);
    }

    /**
     * Answer a question (admin)
     */
    public function answerQuestion(int $questionId, int $userId, string $answer, ?array $attachments = null): InvestorQuestionAnswer
    {
        $question = InvestorQuestion::findOrFail($questionId);
        
        $questionAnswer = InvestorQuestionAnswer::create([
            'question_id' => $questionId,
            'answered_by_user_id' => $userId,
            'answer' => $answer,
            'attachments' => $attachments,
            'answered_at' => now(),
        ]);

        $question->update(['status' => 'answered']);

        return $questionAnswer;
    }

    /**
     * Upvote a public question
     */
    public function upvoteQuestion(int $questionId): void
    {
        InvestorQuestion::where('id', $questionId)
            ->where('is_public', true)
            ->increment('upvotes');
    }

    /**
     * Make question public (admin)
     */
    public function makePublic(int $questionId): void
    {
        InvestorQuestion::where('id', $questionId)
            ->update(['is_public' => true]);
    }

    /**
     * Get question categories with counts
     */
    public function getCategoryCounts(): array
    {
        return InvestorQuestion::where('is_public', true)
            ->where('status', 'answered')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Get pending questions count (admin)
     */
    public function getPendingCount(): int
    {
        return InvestorQuestion::where('status', 'pending')->count();
    }
}
