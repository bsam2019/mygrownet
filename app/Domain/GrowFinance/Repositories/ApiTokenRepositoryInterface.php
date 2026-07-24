<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\ApiToken;

interface ApiTokenRepositoryInterface
{
    public function findById(int $id): ?ApiToken;

    public function save(ApiToken $apiToken): ApiToken;

    public function findByBusiness(int $businessId): array;

    public function findActive(int $businessId): array;

    public function findValid(int $businessId): array;

    public function findByToken(string $token): ?ApiToken;

    public function findByBusinessAndName(int $businessId, string $name): ?ApiToken;
}
