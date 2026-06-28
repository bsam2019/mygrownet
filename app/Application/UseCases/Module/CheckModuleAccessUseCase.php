<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleAccessDTO;
use App\Application\DTOs\ModuleSubscriptionDTO;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleAccessService;
use App\Models\User;

class CheckModuleAccessUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleAccessService $accessService
    ) {}

    /**
     * Check if user has access to a module and return detailed access information
     *
     * @param User $user
     * @param string $moduleId
     * @return ModuleAccessDTO
     */
    public function execute(User $user, string $moduleId): ModuleAccessDTO
    {
        // Check if module exists
        $module = $this->moduleRepository->findById($moduleId);
        if (!$module) {
            return new ModuleAccessDTO(
                hasAccess: false,
                accessType: 'none',
                reason: 'Module not found'
            );
        }

        // Check if module is active
        if ($module->getStatus() !== 'active') {
            return new ModuleAccessDTO(
                hasAccess: false,
                accessType: 'none',
                reason: 'Module is not active'
            );
        }

        // Check access via service
        $hasAccess = $this->accessService->canAccessModule($user, $moduleId);

        if (!$hasAccess) {
            $reason = $module->requiresSubscription()
                ? 'Subscription required'
                : 'Access denied';

            return new ModuleAccessDTO(
                hasAccess: false,
                accessType: 'none',
                reason: $reason
            );
        }

        // Determine access type and get subscription if applicable
        $subscription = $this->subscriptionRepository->findByUserAndModule($user->id, $moduleId);
        
        if ($subscription && $subscription->isActive()) {
            return new ModuleAccessDTO(
                hasAccess: true,
                accessType: 'subscription',
                reason: 'Active subscription',
                subscription: ModuleSubscriptionDTO::fromEntity($subscription)
            );
        }

        // Check if admin
        if ($user->hasRole('admin') || $user->hasRole('super-admin')) {
            return new ModuleAccessDTO(
                hasAccess: true,
                accessType: 'admin',
                reason: 'Admin access'
            );
        }

        // Free module or other access type
        return new ModuleAccessDTO(
            hasAccess: true,
            accessType: $module->requiresSubscription() ? 'team' : 'free',
            reason: $module->requiresSubscription() ? 'Team access' : 'Free module'
        );
    }
}
