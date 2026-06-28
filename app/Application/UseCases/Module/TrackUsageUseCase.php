<?php

namespace App\Application\UseCases\Module;

use App\Domain\Module\Repositories\ModuleUsageRepositoryInterface;
use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;

/**
 * Track Usage Use Case
 * 
 * Tracks usage for freemium modules and checks limits.
 */
class TrackUsageUseCase
{
    public function __construct(
        private ModuleUsageRepositoryInterface $usageRepository,
        private ModuleAccessService $accessService
    ) {}

    /**
     * Track usage and return remaining count
     * 
     * @throws \DomainException if usage limit exceeded
     */
    public function execute(
        User $user,
        string $moduleId,
        string $usageType,
        int $amount = 1
    ): array {
        // Get user's current access level
        $accessLevel = $this->accessService->getAccessLevel($user, new ModuleId($moduleId));
        
        if ($accessLevel === 'none') {
            throw new \DomainException('User does not have access to this module');
        }

        // Get limits for user's tier
        $limits = $this->accessService->getUserLimits($user, new ModuleId($moduleId));
        $limit = $limits[$usageType] ?? -1; // -1 means unlimited

        // Check if within limit before incrementing
        if (!$this->usageRepository->isWithinLimit($user->id, $moduleId, $usageType, $limit)) {
            throw new \DomainException("Usage limit exceeded for {$usageType}. Please upgrade your plan.");
        }

        // Increment usage
        $newCount = $this->usageRepository->incrementUsage($user->id, $moduleId, $usageType, $amount);
        $remaining = $this->usageRepository->getRemainingUsage($user->id, $moduleId, $usageType, $limit);

        return [
            'success' => true,
            'usage_type' => $usageType,
            'current_count' => $newCount,
            'limit' => $limit === -1 ? 'unlimited' : $limit,
            'remaining' => $limit === -1 ? 'unlimited' : $remaining,
            'is_unlimited' => $limit === -1,
        ];
    }
}
