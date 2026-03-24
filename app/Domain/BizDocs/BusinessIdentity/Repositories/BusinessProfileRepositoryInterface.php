<?php

namespace App\Domain\BizDocs\BusinessIdentity\Repositories;

use App\Domain\BizDocs\BusinessIdentity\Entities\BusinessProfile;

interface BusinessProfileRepositoryInterface
{
    public function save(BusinessProfile $profile): BusinessProfile;

    public function findById(int $id): ?BusinessProfile;

    public function findByUserId(int $userId): ?BusinessProfile;

    public function delete(int $id): bool;
}
