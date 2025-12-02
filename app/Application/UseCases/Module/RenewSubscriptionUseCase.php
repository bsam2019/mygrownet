<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;

class RenewSubscriptionUseCase
{
    public function __construct(
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    /**
     * Renew a module subscription
     *
     * @param string $subscriptionId
     * @return ModuleSubscriptionDTO
     * @throws \DomainException
     */
    public function execute(string $subscriptionId): ModuleSubscriptionDTO
    {
        // Find subscription
        $subscription = $this->subscriptionRepository->findById($subscriptionId);
        if (!$subscription) {
            throw new \DomainException("Subscription not found: {$subscriptionId}");
        }

        if (!$subscription->isActive()) {
            throw new \DomainException("Cannot renew inactive subscription");
        }

        if (!$subscription->isAutoRenew()) {
            throw new \DomainException("Subscription does not have auto-renew enabled");
        }

        // Renew using domain service
        $this->subscriptionService->renew($subscription);

        // Persist
        $this->subscriptionRepository->save($subscription);

        // TODO: Dispatch event
        // event(new ModuleSubscriptionRenewed($subscription));

        return ModuleSubscriptionDTO::fromEntity($subscription);
    }
}
