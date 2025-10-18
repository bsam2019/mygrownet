<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\MLM\Entities\Commission;
use App\Domain\MLM\Repositories\CommissionRepository;
use App\Domain\MLM\ValueObjects\CommissionId;
use App\Domain\MLM\ValueObjects\UserId;
use App\Domain\MLM\ValueObjects\CommissionLevel;
use App\Domain\MLM\ValueObjects\CommissionType;
use App\Domain\MLM\ValueObjects\CommissionStatus;
use App\Domain\MLM\ValueObjects\CommissionAmount;
use App\Models\ReferralCommission;
use App\Models\UserNetwork;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentCommissionRepository implements CommissionRepository
{
    public function findById(CommissionId $id): ?Commission
    {
        $model = ReferralCommission::find($id->value());
        
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function save(Commission $commission): void
    {
        $data = [
            'referrer_id' => $commission->getEarnerId()->value(),
            'referred_id' => $commission->getSourceId()->value(),
            'level' => $commission->getLevel()->value(),
            'amount' => $commission->getAmount()->value(),
            'commission_type' => $commission->getType()->value(),
            'status' => $commission->getStatus()->value(),
            'created_at' => $commission->getEarnedAt(),
            'paid_at' => $commission->getPaidAt(),
        ];

        if ($commission->getId()->value()) {
            ReferralCommission::where('id', $commission->getId()->value())->update($data);
        } else {
            ReferralCommission::create($data);
        }
    }

    public function findByEarnerId(UserId $earnerId): array
    {
        $models = ReferralCommission::where('referrer_id', $earnerId->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findBySourceId(UserId $sourceId): array
    {
        $models = ReferralCommission::where('referred_id', $sourceId->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByLevel(CommissionLevel $level): array
    {
        $models = ReferralCommission::where('level', $level->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByType(CommissionType $type): array
    {
        $models = ReferralCommission::where('commission_type', $type->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByStatus(CommissionStatus $status): array
    {
        $models = ReferralCommission::where('status', $status->value())
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findPendingCommissions(): array
    {
        $models = ReferralCommission::where('status', 'pending')
            ->with(['referrer', 'referee'])
            ->orderBy('created_at', 'asc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByDateRange(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $models = ReferralCommission::whereBetween('created_at', [
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s')
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function findByEarnerAndDateRange(
        UserId $earnerId, 
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): array {
        $models = ReferralCommission::where('referrer_id', $earnerId->value())
            ->whereBetween('created_at', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function calculateTotalCommissions(
        UserId $earnerId, 
        DateTimeImmutable $startDate, 
        DateTimeImmutable $endDate
    ): float {
        return (float) ReferralCommission::where('referrer_id', $earnerId->value())
            ->whereBetween('created_at', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ])
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function getCommissionStatsByLevel(UserId $earnerId): array
    {
        $stats = ReferralCommission::where('referrer_id', $earnerId->value())
            ->selectRaw('
                level,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                AVG(amount) as avg_amount,
                SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount
            ')
            ->groupBy('level')
            ->orderBy('level')
            ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->level => [
                'count' => $stat->count,
                'total_amount' => (float) $stat->total_amount,
                'avg_amount' => (float) $stat->avg_amount,
                'paid_amount' => (float) $stat->paid_amount,
            ]];
        })->toArray();
    }

    public function getCommissionStatsByType(UserId $earnerId): array
    {
        $stats = ReferralCommission::where('referrer_id', $earnerId->value())
            ->selectRaw('
                commission_type,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                AVG(amount) as avg_amount,
                SUM(CASE WHEN status = "paid" THEN amount ELSE 0 END) as paid_amount
            ')
            ->groupBy('commission_type')
            ->get();

        return $stats->mapWithKeys(function ($stat) {
            return [$stat->commission_type => [
                'count' => $stat->count,
                'total_amount' => (float) $stat->total_amount,
                'avg_amount' => (float) $stat->avg_amount,
                'paid_amount' => (float) $stat->paid_amount,
            ]];
        })->toArray();
    }

    public function findNetworkCommissions(UserId $userId, int $maxDepth = 5): array
    {
        // Use efficient network query with materialized path
        $networkMembers = UserNetwork::where('referrer_id', $userId->value())
            ->where('level', '<=', $maxDepth)
            ->pluck('user_id')
            ->toArray();

        if (empty($networkMembers)) {
            return [];
        }

        $models = ReferralCommission::whereIn('referred_id', $networkMembers)
            ->where('referrer_id', $userId->value())
            ->with(['referrer', 'referee'])
            ->orderBy('level')
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->toArray();
    }

    public function bulkUpdateStatus(array $commissionIds, CommissionStatus $status): void
    {
        $updateData = ['status' => $status->value()];
        
        if ($status->isPaid()) {
            $updateData['paid_at'] = now();
        }

        ReferralCommission::whereIn('id', $commissionIds)->update($updateData);
    }

    public function delete(CommissionId $id): void
    {
        ReferralCommission::where('id', $id->value())->delete();
    }

    private function toDomainEntity(ReferralCommission $model): Commission
    {
        return Commission::fromPersistence(
            CommissionId::fromInt($model->id),
            UserId::fromInt($model->referrer_id),
            UserId::fromInt($model->referred_id),
            CommissionLevel::fromInt($model->level),
            CommissionAmount::fromFloat($model->amount),
            CommissionType::fromString($model->commission_type),
            CommissionStatus::fromString($model->status),
            new \DateTimeImmutable($model->created_at),
            $model->paid_at ? new \DateTimeImmutable($model->paid_at) : null
        );
    }
}