<?php

namespace App\Application\DTOs;

class ModuleAccessDTO
{
    public function __construct(
        public readonly bool $hasAccess,
        public readonly string $accessType, // 'free', 'subscription', 'team', 'admin'
        public readonly ?string $reason,
        public readonly ?ModuleSubscriptionDTO $subscription = null,
        public readonly ?array $permissions = null
    ) {}

    public function toArray(): array
    {
        return [
            'has_access' => $this->hasAccess,
            'access_type' => $this->accessType,
            'reason' => $this->reason,
            'subscription' => $this->subscription?->toArray(),
            'permissions' => $this->permissions,
        ];
    }
}
