<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\Commission;
use App\Domain\GrowNet\Repositories\CommissionRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\CommissionLevel;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Domain\GrowNet\ValueObjects\Money;
use App\Infrastructure\Persistence\Eloquent\GrowNet\ReferralCommission;
use DateTimeImmutable;

class EloquentCommissionRepository implements CommissionRepositoryInterface
{
    public function findByReferrerId(MemberId $referrerId, ?int $limit = null): array
    {
        $query = ReferralCommission::where('referrer_id', $referrerId->value())->latest();
        if ($limit) $query->limit($limit);
        return $query->get()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function findByReferrerIdAndLevel(MemberId $referrerId, CommissionLevel $level): array
    {
        return ReferralCommission::where('referrer_id', $referrerId->value())
            ->where('level', $level->value)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByStatus(string $status): array
    {
        return ReferralCommission::where('status', $status)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function sumByReferrerId(MemberId $referrerId): float
    {
        return (float) ReferralCommission::where('referrer_id', $referrerId->value())
            ->where('status', 'paid')
            ->sum('amount');
    }

    public function sumByReferrerIdAndMonth(MemberId $referrerId, int $month, int $year): float
    {
        return (float) ReferralCommission::where('referrer_id', $referrerId->value())
            ->where('status', 'paid')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');
    }

    public function sumPendingByReferrerId(MemberId $referrerId): float
    {
        return (float) ReferralCommission::where('referrer_id', $referrerId->value())
            ->where('status', 'pending')
            ->sum('amount');
    }

    public function save(Commission $commission): Commission
    {
        $data = $commission->toArray();
        if ($commission->id() > 0) {
            ReferralCommission::where('id', $commission->id())->update($data);
            return $commission;
        }
        $model = ReferralCommission::create($data);
        return $this->toDomain($model);
    }

    public function getLevelBreakdown(MemberId $referrerId): array
    {
        $breakdown = [];
        for ($level = 1; $level <= 7; $level++) {
            $commissions = ReferralCommission::where('referrer_id', $referrerId->value())
                ->where('level', $level)
                ->get();
            $breakdown[] = [
                'level' => $level,
                'total' => (float) $commissions->sum('amount'),
                'paid' => (float) $commissions->where('status', 'paid')->sum('amount'),
                'pending' => (float) $commissions->where('status', 'pending')->sum('amount'),
                'this_month' => (float) $commissions->where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            ];
        }
        return $breakdown;
    }

    public function getPaymentHistory(MemberId $referrerId, int $limit = 10): array
    {
        return ReferralCommission::where('referrer_id', $referrerId->value())
            ->where('status', 'paid')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    private function toDomain($model): Commission
    {
        return new Commission(
            id: $model->id,
            referrerId: new MemberId($model->referrer_id),
            referredMemberId: new MemberId($model->referred_user_id ?? $model->referred_member_id ?? 0),
            referredName: $model->referral?->name ?? 'Unknown',
            level: CommissionLevel::from($model->level),
            amount: new Money((float) $model->amount),
            originalAmount: new Money((float) ($model->original_amount ?? $model->amount)),
            type: $model->type ?? 'referral',
            status: $model->status ?? 'pending',
            source: $model->source ?? null,
            description: $model->description ?? null,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            paidAt: $model->updated_at && $model->status === 'paid' ? new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')) : null,
        );
    }
}
