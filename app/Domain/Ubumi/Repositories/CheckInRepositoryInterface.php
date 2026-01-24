<?php

namespace App\Domain\Ubumi\Repositories;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\ValueObjects\CheckInId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\FamilyId;

interface CheckInRepositoryInterface
{
    public function save(CheckIn $checkIn): void;
    
    public function findById(CheckInId $id): ?CheckIn;
    
    public function findByPersonId(PersonId $personId, int $limit = 10): array;
    
    public function findLatestByPersonId(PersonId $personId): ?CheckIn;
    
    public function findByFamilyId(FamilyId $familyId, int $limit = 50): array;
    
    public function findRecentByFamilyId(FamilyId $familyId, int $hours = 24): array;
    
    public function countByPersonId(PersonId $personId): int;
    
    public function delete(CheckInId $id): void;
}
