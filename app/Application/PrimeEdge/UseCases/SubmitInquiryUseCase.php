<?php

namespace App\Application\PrimeEdge\UseCases;

use App\Application\PrimeEdge\DTOs\InquiryDTO;
use App\Domain\PrimeEdge\Services\InquiryService;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\Repositories\ClientRepositoryInterface;
use App\Domain\PrimeEdge\Exceptions\ClientNotFoundException;

class SubmitInquiryUseCase
{
    public function __construct(
        private InquiryService $inquiryService,
        private ClientRepositoryInterface $clientRepository,
    ) {}

    public function execute(string $clientId, string $serviceDescription, ?string $preferredServiceType = null, ?string $notes = null): InquiryDTO
    {
        $client = $this->clientRepository->findById(ClientId::fromString($clientId));
        if (!$client) {
            throw new ClientNotFoundException($clientId);
        }

        $inquiry = $this->inquiryService->submit(
            clientId: ClientId::fromString($clientId),
            serviceDescription: $serviceDescription,
            preferredServiceType: $preferredServiceType,
            notes: $notes,
        );

        return InquiryDTO::fromEntity($inquiry);
    }
}
