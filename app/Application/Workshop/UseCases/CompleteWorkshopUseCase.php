<?php

namespace App\Application\Workshop\UseCases;

use App\Domain\Workshop\Repositories\WorkshopRepository;
use App\Domain\Workshop\Repositories\WorkshopRegistrationRepository;
use App\Models\User;
use App\Services\PointsService;
use Illuminate\Support\Facades\DB;

class CompleteWorkshopUseCase
{
    public function __construct(
        private WorkshopRepository $workshopRepository,
        private WorkshopRegistrationRepository $registrationRepository,
        private PointsService $pointsService
    ) {}

    public function execute(int $workshopId, int $userId): void
    {
        $workshop = $this->workshopRepository->findById($workshopId);
        
        if (!$workshop) {
            throw new \DomainException('Workshop not found');
        }

        $registration = $this->registrationRepository->findByWorkshopAndUser($workshopId, $userId);
        
        if (!$registration) {
            throw new \DomainException('Registration not found');
        }

        DB::transaction(function () use ($registration, $workshop, $userId) {
            // Mark as completed
            $registration->markCompleted();
            $this->registrationRepository->save($registration);

            // Award points if not already awarded
            if (!$registration->pointsAwarded()) {
                $this->pointsService->awardPoints(
                    $userId,
                    $workshop->lpReward(),
                    $workshop->bpReward(),
                    'workshop_completion',
                    "Completed workshop: {$workshop->title()}"
                );

                $registration->awardPoints();
                $this->registrationRepository->save($registration);
            }

            // Issue certificate
            if (!$registration->certificateIssued) {
                $registration->issueCertificate();
                $this->registrationRepository->save($registration);
            }
        });
    }
}
