<?php

namespace App\Application\CommandHandlers;

use App\Application\Commands\UpgradeSubscriptionCommand;
use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Application\UseCases\Module\UpgradeSubscriptionUseCase;

class UpgradeSubscriptionCommandHandler
{
    public function __construct(
        private UpgradeSubscriptionUseCase $useCase
    ) {}

    public function handle(UpgradeSubscriptionCommand $command): ModuleSubscriptionDTO
    {
        return $this->useCase->execute(
            userId: $command->userId,
            moduleId: $command->moduleId,
            newTier: $command->newTier,
            newAmount: $command->newAmount,
            currency: $command->currency
        );
    }
}
