<?php

namespace App\Infrastructure\Persistence\Repositories\StockFlow;

use App\Domain\StockFlow\Entities\CompanyUser;
use App\Domain\StockFlow\Repositories\CompanyUserRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;
use App\Domain\StockFlow\ValueObjects\CompanyUserId;
use App\Infrastructure\Persistence\Eloquent\StockFlow\SaCompanyUserModel;

class EloquentCompanyUserRepository implements CompanyUserRepositoryInterface
{
    public function findById(CompanyUserId $id): ?CompanyUser
    {
        $model = SaCompanyUserModel::with('role')->find($id->toInt());
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByCompanyId(CompanyId $companyId, ?string $status = null): array
    {
        $query = SaCompanyUserModel::with('role')
            ->where('sa_company_id', $companyId->toInt())
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function findByCompanyIdAndUserId(CompanyId $companyId, int $userId): ?CompanyUser
    {
        $model = SaCompanyUserModel::with('role')
            ->where('sa_company_id', $companyId->toInt())
            ->where('user_id', $userId)
            ->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findPendingInvitations(CompanyId $companyId): array
    {
        return SaCompanyUserModel::with('role')
            ->where('sa_company_id', $companyId->toInt())
            ->where('status', 'pending')
            ->orderBy('invited_at', 'desc')
            ->get()
            ->map(fn($m) => $this->toDomainEntity($m))
            ->all();
    }

    public function save(CompanyUser $companyUser): CompanyUser
    {
        $data = [
            'sa_company_id' => $companyUser->getCompanyId()->toInt(),
            'user_id' => $companyUser->getUserId(),
            'sa_company_role_id' => $companyUser->getRoleId()?->toInt(),
            'status' => $companyUser->getStatus(),
            'invited_at' => $companyUser->getInvitedAt(),
            'joined_at' => $companyUser->getJoinedAt(),
            'removed_at' => $companyUser->getRemovedAt(),
            'removal_reason' => $companyUser->getRemovalReason(),
        ];

        if ($companyUser->id() === 0) {
            $model = SaCompanyUserModel::create($data);
        } else {
            $model = SaCompanyUserModel::find($companyUser->id());
            $model->update($data);
        }

        return $this->toDomainEntity($model->fresh(['role']));
    }

    public function delete(CompanyUserId $id): void
    {
        SaCompanyUserModel::destroy($id->toInt());
    }

    private function toDomainEntity(SaCompanyUserModel $model): CompanyUser
    {
        return CompanyUser::reconstitute(
            id: CompanyUserId::fromInt($model->id),
            companyId: CompanyId::fromInt($model->sa_company_id),
            userId: $model->user_id,
            roleId: $model->sa_company_role_id ? CompanyRoleId::fromInt($model->sa_company_role_id) : null,
            status: $model->status,
            invitedAt: $model->invited_at ? new \DateTimeImmutable($model->invited_at->format('Y-m-d H:i:s')) : null,
            joinedAt: $model->joined_at ? new \DateTimeImmutable($model->joined_at->format('Y-m-d H:i:s')) : null,
            removedAt: $model->removed_at ? new \DateTimeImmutable($model->removed_at->format('Y-m-d H:i:s')) : null,
            removalReason: $model->removal_reason,
            createdAt: new \DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new \DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s')),
        );
    }
}