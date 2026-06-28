<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleCardDTO;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleAccessService;
use App\Models\User;

class GetUserModulesUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleAccessService $accessService
    ) {}

    /**
     * Get all modules with user's access status
     *
     * @param User|null $user Null for public/unauthenticated access
     * @param string|null $category Filter by category
     * @return array<ModuleCardDTO>
     */
    public function execute(?User $user = null, ?string $category = null): array
    {
        // Get all active modules
        $modules = $category
            ? $this->moduleRepository->findByCategory($category)
            : $this->moduleRepository->findAllActive();

        // Get user's subscriptions (only if user is authenticated)
        $subscriptionMap = [];
        if ($user) {
            $subscriptions = $this->subscriptionRepository->findByUser($user->id);
            foreach ($subscriptions as $subscription) {
                $subscriptionMap[$subscription->getModuleId()] = $subscription;
            }
        }

        // Build DTOs with access information
        $cards = [];
        foreach ($modules as $module) {
            $moduleIdString = $module->getId()->value(); // Convert ModuleId to string for array key
            $subscription = $subscriptionMap[$moduleIdString] ?? null;
            
            // For unauthenticated users, show as no access (they'll need to login)
            $hasAccess = $user ? $this->accessService->canAccess($user, $module->getId()) : false;

            $cards[] = ModuleCardDTO::fromEntity($module, $hasAccess, $subscription);
        }

        return $cards;
    }
}
