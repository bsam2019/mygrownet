<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use App\Domain\GrowStart\Entities\StartupJourney;
use Illuminate\Support\Collection;

interface JourneyRepositoryInterface
{
    public function findById(int $id): ?StartupJourney;
    
    public function findByUserId(int $userId): ?StartupJourney;
    
    public function findActiveByUserId(int $userId): ?StartupJourney;
    
    public function findAllByUserId(int $userId): Collection;
    
    public function save(StartupJourney $journey): StartupJourney;
    
    public function delete(int $id): bool;
    
    public function countByStatus(string $status): int;
    
    public function countByIndustry(int $industryId): int;
    
    public function countByCountry(int $countryId): int;
}
