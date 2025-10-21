<?php

namespace App\Infrastructure\Persistence\Repositories\ProfitSharing;

use App\Domain\ProfitSharing\Entities\MemberProfitShare;
use App\Domain\ProfitSharing\Repositories\MemberProfitShareRepository;
use App\Infrastructure\Persistence\Eloquent\ProfitSharing\MemberProfitShareModel;
use DateTimeImmutable;

class EloquentMemberProfitShareRepository implements MemberProfitShareRepository
{
    public function save(MemberProfitShare $memberShare): MemberProfitShare
    {
        $data = [
            'quarterly_profit_share_id' => $memberShare->quarterlyProfitShareId(),
            'user_id' => $memberShare->userId(),
            'professional_level' => $memberShare->professionalLevel(),
            'level_multiplier' => $memberShare->levelMultiplier(),
            'member_bp' => $memberShare->memberBp(),
            'share_amount' => $memberShare->shareAmount(),
            'status' => $memberShare->status(),
            'paid_at' => $memberShare->paidAt()?->format('Y-m-d H:i:s'),
        ];

        if ($memberShare->id()) {
            $model = MemberProfitShareModel::findOrFail($memberShare->id());
            $model->update($data);
        } else {
            $model = MemberProfitShareModel::create($data);
        }

        return $this->toDomainEntity($model);
    }

    public function saveBatch(array $memberShares): void
    {
        $data = array_map(function (MemberProfitShare $memberShare) {
            return [
                'quarterly_profit_share_id' => $memberShare->quarterlyProfitShareId(),
                'user_id' => $memberShare->userId(),
                'professional_level' => $memberShare->professionalLevel(),
                'level_multiplier' => $memberShare->levelMultiplier(),
                'member_bp' => $memberShare->memberBp(),
                'share_amount' => $memberShare->shareAmount(),
                'status' => $memberShare->status(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $memberShares);

        MemberProfitShareModel::insert($data);
    }

    public function findByQuarterlyProfitShareId(int $quarterlyProfitShareId): array
    {
        return MemberProfitShareModel::where('quarterly_profit_share_id', $quarterlyProfitShareId)
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByUserId(int $userId): array
    {
        return MemberProfitShareModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->toDomainEntity($model))
            ->toArray();
    }

    public function findByUserIdAndQuarter(int $userId, int $quarterlyProfitShareId): ?MemberProfitShare
    {
        $model = MemberProfitShareModel::where('user_id', $userId)
            ->where('quarterly_profit_share_id', $quarterlyProfitShareId)
            ->first();
            
        return $model ? $this->toDomainEntity($model) : null;
    }

    private function toDomainEntity(MemberProfitShareModel $model): MemberProfitShare
    {
        return new MemberProfitShare(
            id: $model->id,
            quarterlyProfitShareId: $model->quarterly_profit_share_id,
            userId: $model->user_id,
            professionalLevel: $model->professional_level,
            levelMultiplier: $model->level_multiplier,
            memberBp: $model->member_bp,
            shareAmount: $model->share_amount,
            status: $model->status,
            paidAt: $model->paid_at ? new DateTimeImmutable($model->paid_at) : null,
            createdAt: new DateTimeImmutable($model->created_at),
            updatedAt: new DateTimeImmutable($model->updated_at)
        );
    }
}
