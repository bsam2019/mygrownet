<?php

namespace App\Application\CommandHandlers;

use App\Application\Commands\CancelSubscriptionCommand;
use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Application\UseCases\Module\CancelSubscriptionUseCase;

class CancelSubscriptionCommandHandler
{
    public function __construct(
        private CancelSubscriptionUseCase $useCase
    ) {}

    public function handle(CancelSubscriptionCommand $command): ModuleSubscriptionDTO
    {
        return $this->useCase->execute(
            userId: $command->userId,
            moduleId: $command->moduleId,
            reason: $command->reason,
            immediate: $command->immediate
        );
    }
}
