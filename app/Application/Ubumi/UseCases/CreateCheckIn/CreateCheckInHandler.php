<?php

namespace App\Application\Ubumi\UseCases\CreateCheckIn;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\Repositories\CheckInRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\Services\AlertService;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\CheckInStatus;
use DateTimeImmutable;

final class CreateCheckInHandler
{
    public function __construct(
        private readonly CheckInRepositoryInterface $checkInRepository,
        private readonly PersonRepositoryInterface $personRepository,
        private readonly AlertService $alertService
    ) {}

    public function handle(CreateCheckInCommand $command): CheckIn
    {
        // Verify person exists
        $personId = PersonId::fromString($command->personId);
        $person = $this->personRepository->findById($personId);
        
        if (!$person) {
            throw new \DomainException('Person not found');
        }

        // Create check-in
        $checkIn = CheckIn::create(
            personId: $personId,
            status: CheckInStatus::from($command->status),
            note: $command->note,
            location: $command->location,
            photoUrl: $command->photoUrl,
            checkedInAt: $command->checkedInAt ? new DateTimeImmutable($command->checkedInAt) : null
        );

        // Save to repository
        $this->checkInRepository->save($checkIn);

        // Process alerts if needed
        $this->alertService->processCheckIn($checkIn);

        return $checkIn;
    }
}
