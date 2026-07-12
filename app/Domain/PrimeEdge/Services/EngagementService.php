<?php

namespace App\Domain\PrimeEdge\Services;

use App\Domain\PrimeEdge\Entities\Engagement;
use App\Domain\PrimeEdge\Entities\EngagementDeliverable;
use App\Domain\PrimeEdge\ValueObjects\EngagementId;
use App\Domain\PrimeEdge\ValueObjects\EngagementType;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Domain\PrimeEdge\Repositories\EngagementRepositoryInterface;
use App\Domain\PrimeEdge\Exceptions\EngagementNotFoundException;

class EngagementService
{
    public function __construct(
        private EngagementRepositoryInterface $engagementRepository,
    ) {}

    public function create(
        ClientId $clientId,
        EngagementType $type,
        string $description,
        ?string $scope = null,
        ?Money $agreedFee = null,
        ?string $notes = null,
    ): Engagement {
        $engagement = Engagement::create(
            id: $this->engagementRepository->nextId(),
            clientId: $clientId,
            type: $type,
            description: $description,
            scope: $scope,
            agreedFee: $agreedFee,
            notes: $notes,
        );

        $this->engagementRepository->save($engagement);

        return $engagement;
    }

    public function start(EngagementId $id): Engagement
    {
        $engagement = $this->engagementRepository->findById($id);
        if (!$engagement) {
            throw new EngagementNotFoundException($id->toString());
        }

        $engagement->start();
        $this->engagementRepository->save($engagement);

        return $engagement;
    }

    public function complete(EngagementId $id): Engagement
    {
        $engagement = $this->engagementRepository->findById($id);
        if (!$engagement) {
            throw new EngagementNotFoundException($id->toString());
        }

        $engagement->complete();
        $this->engagementRepository->save($engagement);

        return $engagement;
    }

    public function cancel(EngagementId $id, ?string $reason = null): Engagement
    {
        $engagement = $this->engagementRepository->findById($id);
        if (!$engagement) {
            throw new EngagementNotFoundException($id->toString());
        }

        $engagement->cancel($reason);
        $this->engagementRepository->save($engagement);

        return $engagement;
    }
}
