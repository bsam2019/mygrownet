<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\Models\FeatureFlag;
use App\Domain\Core\ValueObjects\PlatformContext;
use Illuminate\Support\Facades\Cache;

class FeatureFlagService
{
    private const CACHE_KEY = 'feature_flags';
    private const CACHE_TTL = 300;

    public function isEnabled(string $flagName, ?PlatformContext $context = null): bool
    {
        $flag = $this->resolveFlag($flagName, $context);

        if (!$flag) {
            return false;
        }

        if (!$flag->enabled) {
            return false;
        }

        if ($context && $flag->rules) {
            return $this->evaluateRules($flag->rules, $context);
        }

        return true;
    }

    public function isEnabledForOrg(string $flagName, int $organizationId): bool
    {
        $flag = FeatureFlag::where('name', $flagName)
            ->where(function ($q) use ($organizationId) {
                $q->whereNull('organization_id')
                  ->orWhere('organization_id', $organizationId);
            })
            ->first();

        if (!$flag) {
            return false;
        }

        if (!$flag->enabled) {
            return false;
        }

        if ($flag->rules && $flag->organization_id === $organizationId) {
            $context = PlatformContext::make(
                userId: '',
                organizationId: (string) $organizationId,
                applicationId: '',
            );
            return $this->evaluateRules($flag->rules, $context);
        }

        return true;
    }

    public function enable(string $flagName, ?int $organizationId = null, ?int $applicationId = null): void
    {
        FeatureFlag::updateOrCreate(
            ['name' => $flagName, 'organization_id' => $organizationId, 'application_id' => $applicationId],
            ['enabled' => true]
        );
        $this->clearCache();
    }

    public function disable(string $flagName, ?int $organizationId = null, ?int $applicationId = null): void
    {
        FeatureFlag::updateOrCreate(
            ['name' => $flagName, 'organization_id' => $organizationId, 'application_id' => $applicationId],
            ['enabled' => false]
        );
        $this->clearCache();
    }

    public function setRules(string $flagName, array $rules, ?int $organizationId = null, ?int $applicationId = null): void
    {
        FeatureFlag::updateOrCreate(
            ['name' => $flagName, 'organization_id' => $organizationId, 'application_id' => $applicationId],
            ['rules' => $rules]
        );
        $this->clearCache();
    }

    public function all(?int $applicationId = null): array
    {
        $query = FeatureFlag::query();
        if ($applicationId) {
            $query->where('application_id', $applicationId);
        }
        return $query->get()->toArray();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function resolveFlag(string $flagName, ?PlatformContext $context): ?FeatureFlag
    {
        $query = FeatureFlag::where('name', $flagName);

        if ($context && $context->organizationId) {
            $query->where(function ($q) use ($context) {
                $q->whereNull('organization_id')
                  ->orWhere('organization_id', (int) $context->organizationId);
            });
        }

        if ($context && $context->applicationId) {
            $query->where(function ($q) use ($context) {
                $q->whereNull('application_id')
                  ->orWhere('application_id', (int) $context->applicationId);
            });
        }

        return $query->orderBy('organization_id', 'desc')->orderBy('application_id', 'desc')->first();
    }

    private function evaluateRules(array $rules, PlatformContext $context): bool
    {
        foreach ($rules as $rule) {
            $key = $rule['key'] ?? null;
            $operator = $rule['operator'] ?? 'eq';
            $value = $rule['value'] ?? null;

            $actual = match ($key) {
                'user_id' => $context->userId,
                'organization_id' => $context->organizationId,
                'application_id' => $context->applicationId,
                default => null,
            };

            $result = match ($operator) {
                'eq' => $actual == $value,
                'neq' => $actual != $value,
                'in' => in_array($actual, (array) $value, true),
                'not_in' => !in_array($actual, (array) $value, true),
                default => true,
            };

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
