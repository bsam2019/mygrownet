<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\ValueObjects\PlatformContext;
use App\Domain\Core\Exceptions\AuthorizationException;
use App\Domain\Core\Exceptions\NotFoundException;
use App\Domain\Core\Contracts\ProviderContract;
use App\Domain\Core\Contracts\HealthService;
use Illuminate\Support\Facades\Log;

class IntegrationGuard
{
    public function __construct(
        private IdentityService $identity,
        private OrganizationService $organizations,
        private ?HealthService $health = null,
        private ?FeatureFlagService $featureFlags = null,
    ) {}

    public function authorize(PlatformContext $context, string $contractClass): void
    {
        $this->requireAuthenticated($context);
        $this->requireOrganizationMember($context);
        $this->requireHealthy($context);
        $this->requireFeatureEnabled($context, $contractClass);
    }

    public function requireAuthenticated(PlatformContext $context): void
    {
        if (empty($context->userId)) {
            Log::warning('IntegrationGuard: unauthenticated access attempt', [
                'trace_id' => $context->traceId,
            ]);
            throw new AuthorizationException('User is not authenticated');
        }
    }

    public function requireOrganizationMember(PlatformContext $context): void
    {
        if (empty($context->organizationId)) {
            return;
        }

        $user = $this->identity->findById((int) $context->userId);
        if (!$user || !$this->organizations->getUserOrganizations($user)->contains('id', $context->organizationId)) {
            Log::warning('IntegrationGuard: user not in organization', [
                'user_id' => $context->userId,
                'org_id' => $context->organizationId,
            ]);
            throw new AuthorizationException('User does not belong to the required organization');
        }
    }

    public function requireHealthy(PlatformContext $context): void
    {
        if (!$this->health) {
            return;
        }

        if (!$this->health->isHealthy($context->applicationId)) {
            Log::warning('IntegrationGuard: application unhealthy', [
                'app_id' => $context->applicationId,
            ]);
            throw new AuthorizationException("Application {$context->applicationId} is not healthy");
        }
    }

    public function requireFeatureEnabled(PlatformContext $context, string $contractClass): void
    {
        if (!$this->featureFlags) {
            return;
        }

        $parts = explode('\\', $contractClass);
        $flagName = 'contract.' . lcfirst(end($parts));

        if (!$this->featureFlags->isEnabled($flagName, $context)) {
            Log::warning('IntegrationGuard: feature flag disabled', [
                'contract' => $contractClass,
                'flag' => $flagName,
            ]);
            throw new AuthorizationException("Contract '{$flagName}' is not enabled");
        }
    }
}
