<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorQuestion;
use App\Domain\Investor\Repositories\InvestorQuestionRepositoryInterface;
use App\Models\Investor\InvestorQuestion as InvestorQuestionModel;
use App\Models\Investor\InvestorQuestionUpvote as InvestorQuestionUpvoteModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentInvestorQuestionRepository implements InvestorQuestionRepositoryInterface
{
    public function save(InvestorQuestion $question): InvestorQuestion
    {
        $data = [
            'investor_account_id' => $question->getInvestorAccountId(),
            'subject' => $question->getSubject(),
            'question' => $question->getQuestion(),
            'category' => $question->getCategory(),
            'status' => $question->getStatus(),
            'is_public' => $question->isPublic(),
            'upvotes' => $question->getUpvotes(),
        ];

        if ($question->getId() > 0) {
            $model = InvestorQuestionModel::findOrFail($question->getId());
            $model->update($data);
        } else {
            $model = InvestorQuestionModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorQuestion
    {
        $model = InvestorQuestionModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestor(int $investorAccountId): array
    {
        return InvestorQuestionModel::where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findPublished(int $limit = 20): array
    {
        return InvestorQuestionModel::with(['latestAnswer', 'investorAccount'])
            ->where('status', 'published')
            ->orderBy('upvotes', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findFeatured(int $limit = 5): array
    {
        return InvestorQuestionModel::with(['latestAnswer'])
            ->where('status', 'published')
            ->where('upvotes', '>', 0)
            ->orderBy('upvotes', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findByCategory(string $category): array
    {
        return InvestorQuestionModel::with(['latestAnswer'])
            ->where('status', 'published')
            ->where('category', $category)
            ->orderBy('upvotes', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function findPublicByCategory(?string $category = null, int $perPage = 15): array
    {
        $query = InvestorQuestionModel::where('is_public', true)
            ->where('status', 'answered')
            ->with(['latestAnswer'])
            ->orderBy('upvotes', 'desc')
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->paginate($perPage)->items();
    }

    public function delete(int $id): bool
    {
        return InvestorQuestionModel::destroy($id) > 0;
    }

    public function upvote(int $questionId, int $investorAccountId): bool
    {
        $existing = InvestorQuestionUpvoteModel::where('question_id', $questionId)
            ->where('investor_account_id', $investorAccountId)
            ->first();

        if ($existing) {
            return false;
        }

        InvestorQuestionUpvoteModel::create([
            'question_id' => $questionId,
            'investor_account_id' => $investorAccountId,
            'upvoted_at' => now(),
        ]);

        InvestorQuestionModel::where('id', $questionId)->increment('upvotes');
        return true;
    }

    public function removeUpvote(int $questionId, int $investorAccountId): bool
    {
        $deleted = InvestorQuestionUpvoteModel::where('question_id', $questionId)
            ->where('investor_account_id', $investorAccountId)
            ->delete();

        if ($deleted) {
            InvestorQuestionModel::where('id', $questionId)->decrement('upvotes');
            return true;
        }

        return false;
    }

    public function incrementUpvotes(int $questionId): void
    {
        InvestorQuestionModel::where('id', $questionId)
            ->where('is_public', true)
            ->increment('upvotes');
    }

    public function getCategoryCounts(): array
    {
        return InvestorQuestionModel::where('is_public', true)
            ->where('status', 'answered')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function getPendingCount(): int
    {
        return InvestorQuestionModel::where('status', 'pending')->count();
    }

    private function toDomainEntity(InvestorQuestionModel $model): InvestorQuestion
    {
        return InvestorQuestion::fromPersistence(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            subject: $model->subject,
            question: $model->question,
            category: $model->category,
            status: $model->status,
            isPublic: $model->is_public,
            upvotes: $model->upvotes,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
