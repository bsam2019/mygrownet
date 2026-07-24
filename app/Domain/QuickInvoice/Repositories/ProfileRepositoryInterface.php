<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Repositories;

use App\Domain\QuickInvoice\Entities\Profile;

interface ProfileRepositoryInterface
{
    public function findById(int $id): ?Profile;

    public function findByUser(int $userId): ?Profile;

    public function save(Profile $profile): Profile;

    public function delete(int $id): bool;
}