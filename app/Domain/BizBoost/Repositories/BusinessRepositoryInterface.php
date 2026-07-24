<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\Business;
use App\Domain\BizBoost\Entities\BusinessProfile;

interface BusinessRepositoryInterface
{
    public function findById(int $id): ?Business;

    public function findByUserId(int $userId): ?Business;

    public function findBySlug(string $slug): ?Business;

    public function save(Business $entity): Business;

    public function delete(int $id): void;

    public function findProfile(int $businessId): ?BusinessProfile;

    public function saveProfile(BusinessProfile $profile): BusinessProfile;

    public function getBusinessIdsByUserId(int $userId): array;
}