<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\OmnichannelLog;

interface OmnichannelLogRepositoryInterface
{
    public function findById(int $id): ?OmnichannelLog;

    public function findByUser(int $userId, array $filters = []): array;

    public function save(OmnichannelLog $entity): OmnichannelLog;
}