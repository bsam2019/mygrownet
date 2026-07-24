<?php

namespace App\Domain\BMS\Repositories;

use App\Domain\BMS\Entities\Job;

interface JobRepositoryInterface
{
    public function findById(int $id): ?Job;

    public function save(Job $job): Job;

    public function findByCompany(int $companyId): array;

    public function findByCustomer(int $customerId): array;

    public function findByStatus(int $companyId, string $status): array;

    public function findActiveByCompany(int $companyId): array;

    public function findByNumber(int $companyId, string $number): ?Job;
}
