<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\ZamStay;

use App\Domain\ZamStay\Entities\Agent;
use App\Domain\ZamStay\Repositories\AgentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\ZamStay\ZamStayAgentModel;

class EloquentAgentRepository implements AgentRepositoryInterface
{
    public function findById(int $id): ?Agent
    {
        $model = ZamStayAgentModel::find($id);
        return $model ? Agent::reconstitute($model->toArray()) : null;
    }

    public function save(Agent $entity): Agent
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ZamStayAgentModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ZamStayAgentModel::create($data);
        return Agent::reconstitute($model->toArray());
    }

    public function findByUser(int $userId): ?Agent
    {
        $model = ZamStayAgentModel::where('user_id', $userId)->first();
        return $model ? Agent::reconstitute($model->toArray()) : null;
    }

    public function findAllApproved(): array
    {
        return ZamStayAgentModel::approved()
            ->get()
            ->map(fn($m) => Agent::reconstitute($m->toArray()))
            ->toArray();
    }

    public function existsByUser(int $userId): bool
    {
        return ZamStayAgentModel::where('user_id', $userId)->exists();
    }
}
