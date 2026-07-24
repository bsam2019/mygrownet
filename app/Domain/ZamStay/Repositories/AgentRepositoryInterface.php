<?php

declare(strict_types=1);

namespace App\Domain\ZamStay\Repositories;

use App\Domain\ZamStay\Entities\Agent;

interface AgentRepositoryInterface
{
    public function findById(int $id): ?Agent;

    public function save(Agent $agent): Agent;

    public function findByUser(int $userId): ?Agent;

    public function findAllApproved(): array;

    public function existsByUser(int $userId): bool;
}
