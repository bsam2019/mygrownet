<?php

namespace App\Infrastructure\Persistence\Repositories\Investor;

use App\Domain\Investor\Entities\InvestorEmailLog;
use App\Domain\Investor\Repositories\InvestorEmailLogRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Investor\InvestorEmailLogModel;

class EloquentInvestorEmailLogRepository implements InvestorEmailLogRepositoryInterface
{
    public function findById(int $id): ?InvestorEmailLog
    {
        $model = InvestorEmailLogModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(InvestorEmailLog $log): InvestorEmailLog
    {
        $data = [
            'investor_account_id' => $log->getInvestorAccountId(),
            'email_type' => $log->getEmailType(),
            'subject' => $log->getSubject(),
            'reference_id' => $log->getReferenceId(),
            'reference_type' => $log->getReferenceType(),
            'status' => $log->getStatus(),
            'sent_at' => $log->getSentAt()?->format('Y-m-d H:i:s'),
            'opened_at' => $log->getOpenedAt()?->format('Y-m-d H:i:s'),
            'clicked_at' => $log->getClickedAt()?->format('Y-m-d H:i:s'),
            'error_message' => $log->getErrorMessage(),
        ];

        if ($log->getId()) {
            $model = InvestorEmailLogModel::find($log->getId());
            $model->update($data);
        } else {
            $model = InvestorEmailLogModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function findByInvestorAccountId(int $investorAccountId, int $limit = 50): array
    {
        return InvestorEmailLogModel::where('investor_account_id', $investorAccountId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByReference(string $referenceType, int $referenceId): array
    {
        return InvestorEmailLogModel::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findPending(int $limit = 100): array
    {
        return InvestorEmailLogModel::pending()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function getStatistics(?\DateTimeImmutable $from = null, ?\DateTimeImmutable $to = null): array
    {
        $query = InvestorEmailLogModel::query();

        if ($from) {
            $query->where('created_at', '>=', $from->format('Y-m-d H:i:s'));
        }
        if ($to) {
            $query->where('created_at', '<=', $to->format('Y-m-d H:i:s'));
        }

        $total = $query->count();
        $sent = (clone $query)->sent()->count();
        $failed = (clone $query)->failed()->count();
        $opened = (clone $query)->opened()->count();
        $clicked = (clone $query)->clicked()->count();

        return [
            'total' => $total,
            'sent' => $sent,
            'failed' => $failed,
            'pending' => $total - $sent - $failed,
            'opened' => $opened,
            'clicked' => $clicked,
            'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 2) : 0,
            'click_rate' => $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0,
            'delivery_rate' => $total > 0 ? round(($sent / $total) * 100, 2) : 0,
        ];
    }

    public function getOpenRateByType(string $emailType): float
    {
        $sent = InvestorEmailLogModel::byType($emailType)->sent()->count();
        $opened = InvestorEmailLogModel::byType($emailType)->opened()->count();

        return $sent > 0 ? round(($opened / $sent) * 100, 2) : 0;
    }

    public function getClickRateByType(string $emailType): float
    {
        $sent = InvestorEmailLogModel::byType($emailType)->sent()->count();
        $clicked = InvestorEmailLogModel::byType($emailType)->clicked()->count();

        return $sent > 0 ? round(($clicked / $sent) * 100, 2) : 0;
    }

    private function toDomainEntity(InvestorEmailLogModel $model): InvestorEmailLog
    {
        return new InvestorEmailLog(
            id: $model->id,
            investorAccountId: $model->investor_account_id,
            emailType: $model->email_type,
            subject: $model->subject,
            referenceId: $model->reference_id,
            referenceType: $model->reference_type,
            status: $model->status,
            sentAt: $model->sent_at ? new \DateTimeImmutable($model->sent_at) : null,
            openedAt: $model->opened_at ? new \DateTimeImmutable($model->opened_at) : null,
            clickedAt: $model->clicked_at ? new \DateTimeImmutable($model->clicked_at) : null,
            errorMessage: $model->error_message,
            createdAt: $model->created_at ? new \DateTimeImmutable($model->created_at) : null,
            updatedAt: $model->updated_at ? new \DateTimeImmutable($model->updated_at) : null
        );
    }
}
