<?php

namespace App\Domain\Workshop\Repositories;

use App\Domain\Workshop\Entities\WorkshopRegistration;

interface WorkshopRegistrationRepository
{
    public function save(WorkshopRegistration $registration): WorkshopRegistration;
    
    public function findById(int $id): ?WorkshopRegistration;
    
    public function findByWorkshopAndUser(int $workshopId, int $userId): ?WorkshopRegistration;
    
    public function findByWorkshop(int $workshopId): array;
    
    public function findByUser(int $userId): array;
    
    public function countByWorkshop(int $workshopId): int;
}
