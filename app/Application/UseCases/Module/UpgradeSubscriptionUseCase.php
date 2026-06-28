<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\ValueObjects\Money;

class UpgradeSubscriptionUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    /**
     * Upgrade a module subscription to a higher tier
     *
     * @param int $userId
     * @param string $moduleId
     * @param string $newTier
     * @param float $newAmount
     * @param string $currency
     * @return ModuleSubscriptionDTO
     * @throws \DomainException
     */
    public function execute(
        int $userId,
        string $moduleId,
        string $newTier,
        float $newAmount,
        string $currency = 'ZMW'
    ): ModuleSubscriptionDTO {
        // Find existing subscription
        $subscription = $this->subscriptionRepository->findByUserAndModule($userId, $moduleId);
        if (!$subscription) {
            throw new \DomainException("No subscription found for this module");
        }

        if (!$subscription->isActive()) {
            throw new \DomainException("Subscription is not active");
        }

        // Validate module and tier
        $module = $this->moduleRepository->findById($moduleId);
        if (!$module) {
            throw new \DomainException("Module not found: {$moduleId}");
        }

        $tiers = $module->getConfiguration()->getSubscriptionTiers();
        if (!isset($tiers[$newTier])) {
            throw new \DomainException("Invalid subscription tier: {$newTier}");
        }

        // Upgrade using domain service
        $this->subscriptionService->upgrade(
            subscription: $subscription,
            newTier: $newTier,
            newAmount: new Money($newAmount, $currency)
        );

        // Persist
        $this->subscriptionRepository->save($subscription);

        // TODO: Dispatch event
        // event(new ModuleSubscriptionUpgraded($subscription));

        return ModuleSubscriptionDTO::fromEntity($subscription);
    }
}
