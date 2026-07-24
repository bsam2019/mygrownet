<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\GrowFinance;

use App\Domain\GrowFinance\Entities\TeamMember;
use App\Domain\GrowFinance\Repositories\TeamMemberRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceTeamMemberModel;

class EloquentTeamMemberRepository implements TeamMemberRepositoryInterface
{
    public function findById(int $id): ?TeamMember
    {
        $model = GrowFinanceTeamMemberModel::find($id);
        return $model ? TeamMember::reconstitute($model->toArray()) : null;
    }

    public function save(TeamMember $entity): TeamMember
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            GrowFinanceTeamMemberModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = GrowFinanceTeamMemberModel::create($data);
        return TeamMember::reconstitute($model->toArray());
    }

    public function findByBusiness(int $businessId): array
    {
        return GrowFinanceTeamMemberModel::forBusiness($businessId)->get()->map(fn($m) => TeamMember::reconstitute($m->toArray()))->toArray();
    }

    public function findActive(int $businessId): array
    {
        return GrowFinanceTeamMemberModel::forBusiness($businessId)->active()->get()->map(fn($m) => TeamMember::reconstitute($m->toArray()))->toArray();
    }

    public function findPending(int $businessId): array
    {
        return GrowFinanceTeamMemberModel::forBusiness($businessId)->where('status', 'pending')->get()->map(fn($m) => TeamMember::reconstitute($m->toArray()))->toArray();
    }

    public function findByUserAndBusiness(int $userId, int $businessId): ?TeamMember
    {
        $_ = GrowFinanceTeamMemberModel::forBusiness($businessId)->where('user_id', $userId)->first(); return $_ ? TeamMember::reconstitute($_->toArray()) : null;
    }

}
