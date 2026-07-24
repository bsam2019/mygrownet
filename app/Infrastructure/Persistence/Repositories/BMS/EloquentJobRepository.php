<?php

namespace App\Infrastructure\Persistence\Repositories\BMS;

use App\Domain\BMS\Entities\Job;
use App\Domain\BMS\Repositories\JobRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\BMS\JobModel;

class EloquentJobRepository implements JobRepositoryInterface
{
    public function findById(int $id): ?Job
    {
        $model = JobModel::find($id);
        return $model ? Job::reconstitute($model->toArray()) : null;
    }

    public function save(Job $entity): Job
    {
        $data = $entity->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id) {
            JobModel::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $model = JobModel::create($data);
        return Job::reconstitute($model->toArray());
    }

    public function findByCompany(int $companyId): array
    {
        return JobModel::where('company_id', $companyId)->get()
            ->map(fn($m) => Job::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByCustomer(int $customerId): array
    {
        return JobModel::where('customer_id', $customerId)->get()
            ->map(fn($m) => Job::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByStatus(int $companyId, string $status): array
    {
        return JobModel::where('company_id', $companyId)->where('status', $status)->get()
            ->map(fn($m) => Job::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findActiveByCompany(int $companyId): array
    {
        return JobModel::where('company_id', $companyId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->get()
            ->map(fn($m) => Job::reconstitute($m->toArray()))
            ->toArray();
    }

    public function findByNumber(int $companyId, string $number): ?Job
    {
        $model = JobModel::where('company_id', $companyId)->where('job_number', $number)->first();
        return $model ? Job::reconstitute($model->toArray()) : null;
    }
}
