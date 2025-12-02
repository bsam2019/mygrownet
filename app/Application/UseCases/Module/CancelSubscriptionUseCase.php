<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;

class CancelSubscriptionUseCase
{
    public function __construct(
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    /**
     * Cancel a module subscription
     *
     * @param int $userId
     * @param string $moduleId
     * @param string|null $reason
     * @param bool $immediate If true, cancel immediately. If false, cancel at end of billing period
     * @return ModuleSubscriptionDTO
     * @throws \DomainException
     */
    public function execute(
        int $userId,
        string $moduleId,
        ?string $reason = null,
        bool $immediate = false
    ): ModuleSubscriptionDTO {
        // Find subscription
        $subscription = $this->subscriptionRepository->findByUserAndModule($userId, $moduleId);
        if (!$subscription) {
            throw new \DomainException("No subscription found for this module");
        }

        if (!$subscription->isActive()) {
            throw new \DomainException("Subscription is not active");
        }

        // Cancel using domain service
        if ($immediate) {
            $this->subscriptionService->cancelImmediately($subscription, $reason);
        } else {
            $this->subscriptionService->cancelAtPeriodEnd($subscription, $reason);
        }

        // Persist
        $this->subscriptionRepository->save($subscription);

        // TODO: Dispatch event
        // event(new ModuleSubscriptionCancelled($subscription));

        return ModuleSubscriptionDTO::fromEntity($subscription);
    }
}
