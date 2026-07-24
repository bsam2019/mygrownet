<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Project;
use App\Domain\BMS\Repositories\ProjectRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\ProjectModel;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function findById(int $id): ?Project
    {
        $model = ProjectModel::find($id);
        return $model ? Project::reconstitute($model->toArray()) : null;
    }

    public function save(Project $entity): Project
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            ProjectModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = ProjectModel::create($data);
        return Project::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return ProjectModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Project::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return ProjectModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => Project::reconstitute($m->toArray()))
            ->toArray();
    }
}
