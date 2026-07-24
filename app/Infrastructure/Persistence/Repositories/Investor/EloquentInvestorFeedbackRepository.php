<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorFeedback;
use App\Domain\Investor\Repositories\InvestorFeedbackRepositoryInterface;
use App\Models\Investor\InvestorFeedback as InvestorFeedbackModel;
use DateTimeImmutable;

class EloquentInvestorFeedbackRepository implements InvestorFeedbackRepositoryInterface
{
    public function save(InvestorFeedback $feedback): InvestorFeedback
    {
        $data = [
            'investor_account_id' => $feedback->getInvestorAccountId(),
            'feedback_type' => $feedback->getFeedbackType(),
            'category' => $feedback->getCategory(),
            'subject' => $feedback->getSubject(),
            'feedback' => $feedback->getFeedback(),
            'satisfaction_rating' => $feedback->getSatisfactionRating(),
            'status' => $feedback->getStatus(),
        ];

        if ($feedback->getId() > 0) {
            $model = InvestorFeedbackModel::findOrFail($feedback->getId());
            $data['admin_response'] = $feedback->getAdminResponse();
            $data['responded_at'] = $feedback->getRespondedAt()?->format('Y-m-d H:i:s');
            $model->update($data);
        } else {
            $model = InvestorFeedbackModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?InvestorFeedback
    {
        $model = InvestorFeedbackModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByInvestor(int $investorAccountId, int $perPage = 10): array
    {
        return InvestorFeedbackModel::where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->items();
    }

    public function findAll(): array
    {
        return InvestorFeedbackModel::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->toArray();
    }

    public function countByStatus(string $status): int
    {
        return InvestorFeedbackModel::where('status', $status)->count();
    }

    public function getAverageRating(): float
    {
        return (float) InvestorFeedbackModel::whereNotNull('satisfaction_rating')->avg('satisfaction_rating');
    }

    public function countByType(): array
    {
        return InvestorFeedbackModel::selectRaw('feedback_type, COUNT(*) as count')
            ->groupBy('feedback_type')
            ->pluck('count', 'feedback_type')
            ->toArray();
    }

    private function toDomainEntity(InvestorFeedbackModel $model): InvestorFeedback
    {
        return InvestorFeedback::fromPersistence(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            feedbackType: $model->feedback_type,
            category: $model->category,
            subject: $model->subject,
            feedback: $model->feedback,
            satisfactionRating: $model->satisfaction_rating,
            status: $model->status,
            adminResponse: $model->admin_response,
            respondedAt: $model->responded_at ? new DateTimeImmutable($model->responded_at) : null,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
