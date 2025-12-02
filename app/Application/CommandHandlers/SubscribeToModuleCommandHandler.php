<?php

namespace App\Application\CommandHandlers;

use App\Application\Commands\SubscribeToModuleCommand;
use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Application\UseCases\Module\SubscribeToModuleUseCase;

class SubscribeToModuleCommandHandler
{
    public function __construct(
        private SubscribeToModuleUseCase $useCase
    ) {}

    public function handle(SubscribeToModuleCommand $command): ModuleSubscriptionDTO
    {
        return $this->useCase->execute(
            userId: $command->userId,
            moduleId: $command->moduleId,
            tier: $command->tier,
            amount: $command->amount,
            currency: $command->currency,
            billingCycle: $command->billingCycle
        );
    }
}
