<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;

class StartTrialUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    /**
     * Start a trial subscription for a module
     *
     * @param int $userId
     * @param string $moduleId
     * @param int $trialDays
     * @return ModuleSubscriptionDTO
     * @throws \DomainException
     */
    public function execute(
        int $userId,
        string $moduleId,
        int $trialDays = 14
    ): ModuleSubscriptionDTO {
        // Validate module exists and is active
        $module = $this->moduleRepository->findById($moduleId);
        if (!$module) {
            throw new \DomainException("Module not found: {$moduleId}");
        }

        if ($module->getStatus() !== 'active') {
            throw new \DomainException("Module is not active: {$moduleId}");
        }

        if (!$module->requiresSubscription()) {
            throw new \DomainException("Module does not require subscription: {$moduleId}");
        }

        // Check if user already has or had a subscription (no multiple trials)
        $existing = $this->subscriptionRepository->findByUserAndModule($userId, $moduleId);
        if ($existing) {
            throw new \DomainException("User has already used trial or has a subscription for this module");
        }

        // Start trial using domain service
        $subscription = $this->subscriptionService->startTrial(
            userId: $userId,
            moduleId: $moduleId,
            trialDays: $trialDays
        );

        // Persist
        $this->subscriptionRepository->save($subscription);

        // TODO: Dispatch event
        // event(new ModuleTrialStarted($subscription));

        return ModuleSubscriptionDTO::fromEntity($subscription);
    }
}
