<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowNet;

use App\Domain\GrowNet\Entities\Member;
use App\Domain\GrowNet\Repositories\MemberRepositoryInterface;
use App\Domain\GrowNet\ValueObjects\MemberId;
use App\Infrastructure\Persistence\Eloquent\GrowNet\MemberModel;

class EloquentMemberRepository implements MemberRepositoryInterface
{
    public function findById(MemberId $id): ?Member
    {
        $model = MemberModel::find($id->value());
        return $model ? $this->toDomain($model) : null;
    }

    public function findByUserId(int $userId): ?Member
    {
        $model = MemberModel::where('user_id', $userId)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findByReferralCode(string $code): ?Member
    {
        $model = MemberModel::where('referral_code', $code)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function save(Member $member): Member
    {
        $data = $member->toArray();
        unset($data['id']);

        if ($member->id()->value() > 0) {
            MemberModel::where('id', $member->id()->value())->update($data);
            return $member;
        }

        $model = MemberModel::create($data);
        return $this->toDomain($model);
    }

    public function delete(MemberId $id): void
    {
        MemberModel::destroy($id->value());
    }

    public function findEligibleForTierUpgrade(): array
    {
        return MemberModel::whereNotNull('current_professional_level')
            ->where('is_currently_active', true)
            ->get()
            ->map(fn(MemberModel $m) => $this->toDomain($m))
            ->toArray();
    }

    public function findWithExpiringSubscriptions(\DateTimeImmutable $cutoff): array
    {
        return MemberModel::where('subscription_end_date', '<=', $cutoff->format('Y-m-d'))
            ->where('subscription_status', 'active')
            ->get()
            ->map(fn(MemberModel $m) => $this->toDomain($m))
            ->toArray();
    }

    public function countByTier(string $tier): int
    {
        return MemberModel::where('current_professional_level', $tier)->count();
    }

    public function getLeaderboard(int $limit = 10): array
    {
        return MemberModel::where('is_currently_active', true)
            ->orderByDesc('total_earnings')
            ->limit($limit)
            ->get()
            ->map(fn(MemberModel $m) => [
                'member' => $this->toDomain($m),
                'team_volume' => $m->current_team_volume ?? 0,
                'referral_count' => $m->referral_count ?? 0,
            ])
            ->toArray();
    }

    private function toDomain(MemberModel $model): Member
    {
        return Member::reconstitute($model->toArray());
    }
}
