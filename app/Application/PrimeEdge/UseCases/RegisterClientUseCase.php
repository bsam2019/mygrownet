<?php

namespace App\Application\PrimeEdge\UseCases;

use App\Application\PrimeEdge\DTOs\ClientDTO;
use App\Domain\PrimeEdge\Services\ClientOnboardingService;
use Illuminate\Support\Facades\Hash;

class RegisterClientUseCase
{
    public function __construct(
        private ClientOnboardingService $onboardingService,
    ) {}

    public function execute(string $name, string $email, string $password, ?string $phone = null, ?string $businessType = null, ?string $companyName = null): ClientDTO
    {
        $client = $this->onboardingService->register(
            name: $name,
            email: $email,
            password: Hash::make($password),
            phone: $phone,
            businessType: $businessType,
            companyName: $companyName,
        );

        return ClientDTO::fromEntity($client);
    }
}
