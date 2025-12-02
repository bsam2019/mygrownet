<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\ValueObjects\Money;

class SubscribeToModuleUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    /**
     * Subscribe user to a module
     *
     * @param int $userId
     * @param string $moduleId
     * @param string $tier
     * @param float $amount
     * @param string $currency
     * @param string $billingCycle
     * @return ModuleSubscriptionDTO
     * @throws \DomainException
     */
    public function execute(
        int $userId,
        string $moduleId,
        string $tier,
        float $amount,
        string $currency = 'ZMW',
        string $billingCycle = 'monthly'
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

        // Check if user already has active subscription
        $existing = $this->subscriptionRepository->findByUserAndModule($userId, $moduleId);
        if ($existing && $existing->isActive()) {
            throw new \DomainException("User already has an active subscription to this module");
        }

        // Validate tier exists in module configuration
        $tiers = $module->getConfiguration()->getSubscriptionTiers();
        if (!isset($tiers[$tier])) {
            throw new \DomainException("Invalid subscription tier: {$tier}");
        }

        // Create subscription using domain service
        $subscription = $this->subscriptionService->subscribe(
            userId: $userId,
            moduleId: $moduleId,
            tier: $tier,
            amount: new Money($amount, $currency),
            billingCycle: $billingCycle
        );

        // Persist
        $this->subscriptionRepository->save($subscription);

        // TODO: Dispatch event
        // event(new ModuleSubscriptionCreated($subscription));

        return ModuleSubscriptionDTO::fromEntity($subscription);
    }
}
