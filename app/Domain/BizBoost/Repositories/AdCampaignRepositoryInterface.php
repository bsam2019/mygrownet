<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Repositories;

use App\Domain\BizBoost\Entities\AdCampaign;

interface AdCampaignRepositoryInterface
{
    public function findById(int $id): ?AdCampaign;

    public function findByUser(int $userId, array $filters = []): array;

    public function findByUserAndId(int $userId, int $id): ?AdCampaign;

    public function getQueryByUser(int $userId);

    public function create(array $data): AdCampaign;

    public function save(AdCampaign $entity): AdCampaign;

    public function update(int $id, array $data): ?AdCampaign;

    public function delete(int $id): void;
}