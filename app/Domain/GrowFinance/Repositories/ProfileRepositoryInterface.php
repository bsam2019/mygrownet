<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\Profile;

interface ProfileRepositoryInterface
{
    public function findById(int $id): ?Profile;

    public function save(Profile $profile): Profile;

    public function findByUser(int $userId): ?Profile;
}
