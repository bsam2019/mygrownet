<?php

namespace App\Domain\PrimeEdge\Services;

use App\Domain\PrimeEdge\Entities\Client;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\ClientName;
use App\Domain\PrimeEdge\ValueObjects\Email;
use App\Domain\PrimeEdge\ValueObjects\PhoneNumber;
use App\Domain\PrimeEdge\ValueObjects\BusinessType;
use App\Domain\PrimeEdge\Repositories\ClientRepositoryInterface;
use App\Domain\PrimeEdge\Exceptions\DuplicateEmailException;

class ClientOnboardingService
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
    ) {}

    public function register(
        string $name,
        string $email,
        string $password,
        ?string $phone = null,
        ?string $businessType = null,
        ?string $companyName = null,
    ): Client {
        $emailVo = Email::fromString($email);

        $existing = $this->clientRepository->findByEmail($emailVo);
        if ($existing) {
            throw new DuplicateEmailException($email);
        }

        $client = Client::create(
            id: $this->clientRepository->nextId(),
            name: ClientName::fromString($name),
            email: $emailVo,
            password: $password,
            phone: $phone ? PhoneNumber::fromString($phone) : null,
            businessType: $businessType ? BusinessType::fromString($businessType) : null,
            companyName: $companyName,
        );

        $this->clientRepository->save($client);

        return $client;
    }
}
