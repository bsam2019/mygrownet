<?php

namespace App\Application\Workshop\UseCases;

use App\Domain\Workshop\Entities\WorkshopRegistration;
use App\Domain\Workshop\Repositories\WorkshopRepository;
use App\Domain\Workshop\Repositories\WorkshopRegistrationRepository;

class RegisterForWorkshopUseCase
{
    public function __construct(
        private WorkshopRepository $workshopRepository,
        private WorkshopRegistrationRepository $registrationRepository
    ) {}

    public function execute(int $workshopId, int $userId, ?string $notes = null): WorkshopRegistration
    {
        // Check if workshop exists and is open for registration
        $workshop = $this->workshopRepository->findById($workshopId);
        
        if (!$workshop) {
            throw new \DomainException('Workshop not found');
        }

        if (!$workshop->isRegistrationOpen()) {
            throw new \DomainException('Workshop registration is closed');
        }

        // Check if user is already registered
        $existingRegistration = $this->registrationRepository->findByWorkshopAndUser($workshopId, $userId);
        
        if ($existingRegistration) {
            throw new \DomainException('You are already registered for this workshop');
        }

        // Check if workshop is full
        $currentRegistrations = $this->registrationRepository->countByWorkshop($workshopId);
        
        if ($workshop->isFull($currentRegistrations)) {
            throw new \DomainException('Workshop is full');
        }

        // Create registration
        $registration = WorkshopRegistration::create($workshopId, $userId, $notes);
        
        return $this->registrationRepository->save($registration);
    }
}
