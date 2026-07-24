<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\CustomTemplate;

interface CustomTemplateRepositoryInterface
{
    public function findById(int $id): ?CustomTemplate;

    public function findByBusiness(int $businessId): array;

    public function save(CustomTemplate $entity): CustomTemplate;

    public function delete(int $id): void;
}