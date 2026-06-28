<?php

namespace App\Application\UseCases\Module;

use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleUsageRepositoryInterface;
use App\Domain\Module\Services\ModuleAccessService;
use App\Domain\Module\ValueObjects\ModuleId;
use App\Models\User;

/**
 * Get Usage Summary Use Case
 * 
 * Returns usage summary for a user's module access.
 */
class GetUsageSummaryUseCase
{
    public function __construct(
        private ModuleUsageRepositoryInterface $usageRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleAccessService $accessService
    ) {}

    /**
     * Get usage summary for a module
     */
    public function execute(User $user, string $moduleId): array
    {
        $module = $this->moduleRepository->findById(new ModuleId($moduleId));
        
        if (!$module) {
            throw new \DomainException('Module not found');
        }

        // Get user's access level
        $accessLevel = $this->accessService->getAccessLevel($user, new ModuleId($moduleId));
        
        if ($accessLevel === 'none') {
            return [
                'has_access' => false,
                'access_level' => 'none',
                'usage' => [],
            ];
        }

        // Get limits for user's tier
        $limits = $this->accessService->getUserLimits($user, new ModuleId($moduleId));
        
        // Get current usage
        $currentUsage = $this->usageRepository->getAllUsage($user->id, $moduleId);

        // Build usage summary
        $usageSummary = [];
        foreach ($limits as $type => $limit) {
            $used = $currentUsage[$type] ?? 0;
            $remaining = $limit === -1 ? 'unlimited' : max(0, $limit - $used);
            
            $usageSummary[$type] = [
                'used' => $used,
                'limit' => $limit === -1 ? 'unlimited' : $limit,
                'remaining' => $remaining,
                'is_unlimited' => $limit === -1,
                'percentage' => $limit === -1 ? 0 : ($limit > 0 ? round(($used / $limit) * 100, 1) : 100),
            ];
        }

        return [
            'has_access' => true,
            'access_level' => $accessLevel,
            'module_id' => $moduleId,
            'module_name' => $module->getName()->value(),
            'usage' => $usageSummary,
            'period' => [
                'start' => now()->startOfMonth()->toDateString(),
                'end' => now()->endOfMonth()->toDateString(),
            ],
        ];
    }
}
