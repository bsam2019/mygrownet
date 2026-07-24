<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\BizBoost;

use App\Domain\BizBoost\Entities\TeamMember;
use App\Domain\BizBoost\Repositories\TeamMemberRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentTeamMemberRepository implements TeamMemberRepositoryInterface
{
    public function findById(int $id): ?TeamMember
    {
        $row = DB::table('bizboost_team_members')->where('id', $id)->first();
        return $row ? TeamMember::reconstitute((array) $row) : null;
    }

    public function findByBusiness(int $businessId): array
    {
        $rows = DB::table('bizboost_team_members')
            ->where('business_id', $businessId)
            ->orderBy('role')
            ->orderBy('name')
            ->get();
        return $rows->map(fn($r) => TeamMember::reconstitute((array) $r))->toArray();
    }

    public function findByEmail(int $businessId, string $email): ?TeamMember
    {
        $row = DB::table('bizboost_team_members')
            ->where('business_id', $businessId)
            ->where('email', $email)
            ->first();
        return $row ? TeamMember::reconstitute((array) $row) : null;
    }

    public function save(TeamMember $entity): TeamMember
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($data['permissions'] && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }

        if ($id) {
            DB::table('bizboost_team_members')->where('id', $id)->update($data);
            return $this->findById($id);
        }

        $newId = DB::table('bizboost_team_members')->insertGetId($data);
        return $this->findById($newId);
    }

    public function delete(int $id): void
    {
        DB::table('bizboost_team_members')->where('id', $id)->delete();
    }

    public function countActive(int $businessId): int
    {
        return DB::table('bizboost_team_members')
            ->where('business_id', $businessId)
            ->where('status', 'active')
            ->count();
    }
}