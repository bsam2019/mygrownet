<?php

namespace App\Application\PrimeEdge\UseCases;

use App\Application\PrimeEdge\DTOs\EngagementDTO;
use App\Domain\PrimeEdge\Services\EngagementService;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\EngagementType;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Domain\PrimeEdge\Repositories\ClientRepositoryInterface;
use App\Domain\PrimeEdge\Exceptions\ClientNotFoundException;

class CreateEngagementUseCase
{
    public function __construct(
        private EngagementService $engagementService,
        private ClientRepositoryInterface $clientRepository,
    ) {}

    public function execute(
        string $clientId,
        string $type,
        string $description,
        ?string $scope = null,
        ?float $agreedFee = null,
        ?string $currency = 'ZMW',
        ?string $notes = null,
    ): EngagementDTO {
        $client = $this->clientRepository->findById(ClientId::fromString($clientId));
        if (!$client) {
            throw new ClientNotFoundException($clientId);
        }

        $fee = $agreedFee !== null
            ? ($currency === 'ZMW' ? Money::fromKwacha($agreedFee) : Money::fromUsd($agreedFee))
            : null;

        $engagement = $this->engagementService->create(
            clientId: ClientId::fromString($clientId),
            type: EngagementType::from($type),
            description: $description,
            scope: $scope,
            agreedFee: $fee,
            notes: $notes,
        );

        return EngagementDTO::fromEntity($engagement);
    }
}
