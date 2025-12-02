<?php

namespace App\Application\DTOs;

/**
 * Module Usage DTO
 * 
 * Data transfer object for module usage information.
 */
class ModuleUsageDTO
{
    public function __construct(
        public readonly string $moduleId,
        public readonly string $moduleName,
        public readonly string $accessLevel,
        public readonly array $usage,
        public readonly string $periodStart,
        public readonly string $periodEnd
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            moduleId: $data['module_id'],
            moduleName: $data['module_name'] ?? '',
            accessLevel: $data['access_level'],
            usage: $data['usage'] ?? [],
            periodStart: $data['period']['start'] ?? now()->startOfMonth()->toDateString(),
            periodEnd: $data['period']['end'] ?? now()->endOfMonth()->toDateString()
        );
    }

    public function toArray(): array
    {
        return [
            'module_id' => $this->moduleId,
            'module_name' => $this->moduleName,
            'access_level' => $this->accessLevel,
            'usage' => $this->usage,
            'period' => [
                'start' => $this->periodStart,
                'end' => $this->periodEnd,
            ],
        ];
    }

    /**
     * Get usage for a specific type
     */
    public function getUsageForType(string $type): ?array
    {
        return $this->usage[$type] ?? null;
    }

    /**
     * Check if any usage is near limit (>80%)
     */
    public function hasUsageNearLimit(): bool
    {
        foreach ($this->usage as $type => $data) {
            if (!$data['is_unlimited'] && $data['percentage'] >= 80) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if any usage has exceeded limit
     */
    public function hasExceededLimit(): bool
    {
        foreach ($this->usage as $type => $data) {
            if (!$data['is_unlimited'] && $data['percentage'] >= 100) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get types that are near limit
     */
    public function getTypesNearLimit(): array
    {
        $nearLimit = [];
        foreach ($this->usage as $type => $data) {
            if (!$data['is_unlimited'] && $data['percentage'] >= 80) {
                $nearLimit[] = $type;
            }
        }
        return $nearLimit;
    }
}
