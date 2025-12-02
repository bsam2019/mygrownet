# Phase 3: Application Layer - Command Reference

**Last Updated:** December 1, 2025  
**Status:** Ready to Execute

---

## Overview

Phase 3 focuses on building the Application Layer - the orchestration layer between the domain and presentation layers. This includes Use Cases, DTOs, Commands, Queries, and Event Handlers.

---

## Quick Start Commands

### 1. Create Use Case Classes

```bash
# No artisan command for use cases - create manually
# Location: app/Application/UseCases/Module/
```

### 2. Create DTO Classes

```bash
# No artisan command for DTOs - create manually
# Location: app/Application/DTOs/
```

### 3. Create Event Handlers

```bash
# Create event handler
php artisan make:listener ModuleSubscriptionCreatedListener --event=ModuleSubscriptionCreated

# Create event
php artisan make:event ModuleSubscriptionCreated
```

---

## File Creation Checklist

### Use Cases (app/Application/UseCases/Module/)
- [ ] `SubscribeToModuleUseCase.php`
- [ ] `StartTrialUseCase.php`
- [ ] `CancelSubscriptionUseCase.php`
- [ ] `UpgradeSubscriptionUseCase.php`
- [ ] `GetUserModulesUseCase.php`
- [ ] `CheckModuleAccessUseCase.php`
- [ ] `RenewSubscriptionUseCase.php`
- [ ] `ProcessExpiredSubscriptionsUseCase.php`

### DTOs (app/Application/DTOs/)
- [ ] `ModuleDTO.php`
- [ ] `ModuleSubscriptionDTO.php`
- [ ] `ModuleCardDTO.php`
- [ ] `SubscriptionTierDTO.php`
- [ ] `ModuleAccessDTO.php`

### Commands (app/Application/Commands/)
- [ ] `SubscribeToModuleCommand.php`
- [ ] `CancelSubscriptionCommand.php`
- [ ] `UpgradeSubscriptionCommand.php`

### Queries (app/Application/Queries/)
- [ ] `GetUserModulesQuery.php`
- [ ] `GetModuleByIdQuery.php`
- [ ] `GetAvailableModulesQuery.php`

### Event Handlers (app/Application/EventHandlers/)
- [ ] `ModuleSubscriptionCreatedHandler.php`
- [ ] `ModuleSubscriptionCancelledHandler.php`
- [ ] `ModuleSubscriptionExpiredHandler.php`

---

## Use Case Templates

### SubscribeToModuleUseCase

```php
<?php

namespace App\Application\UseCases\Module;

use App\Domain\Module\Entities\ModuleSubscription;
use App\Domain\Module\Repositories\ModuleRepositoryInterface;
use App\Domain\Module\Repositories\ModuleSubscriptionRepositoryInterface;
use App\Domain\Module\Services\ModuleSubscriptionService;
use App\Domain\Module\ValueObjects\Money;

class SubscribeToModuleUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private ModuleSubscriptionRepositoryInterface $subscriptionRepository,
        private ModuleSubscriptionService $subscriptionService
    ) {}

    public function execute(
        int $userId,
        string $moduleId,
        string $tier,
        float $amount,
        string $currency = 'ZMW',
        string $billingCycle = 'monthly'
    ): ModuleSubscription {
        // Validate module exists
        $module = $this->moduleRepository->findById($moduleId);
        if (!$module) {
            throw new \DomainException("Module not found: {$moduleId}");
        }

        // Check if user already has subscription
        $existing = $this->subscriptionRepository->findByUserAndModule($userId, $moduleId);
        if ($existing && $existing->isActive()) {
            throw new \DomainException("User already has an active subscription to this module");
        }

        // Create subscription using domain service
        $subscription = $this->subscriptionService->subscribe(
            userId: $userId,
            moduleId: $moduleId,
            tier: $tier,
            amount: new Money($amount, $currency),
            billingCycle: $billingCycle
        );

        // Persist
        $this->subscriptionRepository->save($subscription);

        // Dispatch event (optional)
        // event(new ModuleSubscriptionCreated($subscription));

        return $subscription;
    }
}
```

### GetUserModulesUseCase

```php
<?php

namespace App\Application\UseCases\Module;

use App\Application\DTOs\ModuleCardDTO;
use App\Domain\Module\Services\ModuleAccessService;
use App\Models\User;

class GetUserModulesUseCase
{
    public function __construct(
        private ModuleAccessService $accessService
    ) {}

    public function execute(User $user): array
    {
        $modules = $this->accessService->getUserModules($user);

        return array_map(
            fn($module) => ModuleCardDTO::fromEntity($module, $user),
            $modules
        );
    }
}
```

---

## DTO Templates

### ModuleDTO

```php
<?php

namespace App\Application\DTOs;

use App\Domain\Module\Entities\Module;

class ModuleDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $category,
        public readonly ?string $description,
        public readonly ?string $icon,
        public readonly ?string $color,
        public readonly ?string $thumbnail,
        public readonly array $accountTypes,
        public readonly bool $requiresSubscription,
        public readonly array $routes,
        public readonly array $pwaConfig,
        public readonly array $features,
        public readonly ?array $subscriptionTiers,
        public readonly string $status,
        public readonly string $version
    ) {}

    public static function fromEntity(Module $module): self
    {
        return new self(
            id: $module->getId(),
            name: $module->getName(),
            slug: $module->getSlug(),
            category: $module->getCategory(),
            description: $module->getDescription(),
            icon: $module->getIcon(),
            color: $module->getColor(),
            thumbnail: $module->getThumbnail(),
            accountTypes: $module->getAccountTypes(),
            requiresSubscription: $module->requiresSubscription(),
            routes: $module->getConfiguration()->getRoutes(),
            pwaConfig: $module->getConfiguration()->getPwaConfig(),
            features: $module->getConfiguration()->getFeatures(),
            subscriptionTiers: $module->getConfiguration()->getSubscriptionTiers(),
            status: $module->getStatus(),
            version: $module->getVersion()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'thumbnail' => $this->thumbnail,
            'account_types' => $this->accountTypes,
            'requires_subscription' => $this->requiresSubscription,
            'routes' => $this->routes,
            'pwa_config' => $this->pwaConfig,
            'features' => $this->features,
            'subscription_tiers' => $this->subscriptionTiers,
            'status' => $this->status,
            'version' => $this->version,
        ];
    }
}
```

### ModuleCardDTO

```php
<?php

namespace App\Application\DTOs;

use App\Domain\Module\Entities\Module;
use App\Models\User;

class ModuleCardDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $category,
        public readonly ?string $description,
        public readonly ?string $icon,
        public readonly ?string $color,
        public readonly ?string $thumbnail,
        public readonly bool $hasAccess,
        public readonly bool $isSubscribed,
        public readonly ?string $subscriptionStatus,
        public readonly ?string $subscriptionTier,
        public readonly bool $requiresSubscription,
        public readonly ?array $subscriptionTiers,
        public readonly string $primaryRoute,
        public readonly bool $isPWA,
        public readonly string $status
    ) {}

    public static function fromEntity(Module $module, User $user): self
    {
        // This would use ModuleAccessService to determine access
        // For now, simplified version
        
        return new self(
            id: $module->getId(),
            name: $module->getName(),
            slug: $module->getSlug(),
            category: $module->getCategory(),
            description: $module->getDescription(),
            icon: $module->getIcon(),
            color: $module->getColor(),
            thumbnail: $module->getThumbnail(),
            hasAccess: false, // TODO: Check via ModuleAccessService
            isSubscribed: false, // TODO: Check subscription
            subscriptionStatus: null,
            subscriptionTier: null,
            requiresSubscription: $module->requiresSubscription(),
            subscriptionTiers: $module->getConfiguration()->getSubscriptionTiers(),
            primaryRoute: $module->getConfiguration()->getRoutes()['integrated'] ?? '#',
            isPWA: $module->getConfiguration()->getPwaConfig()['enabled'] ?? false,
            status: $module->getStatus()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'thumbnail' => $this->thumbnail,
            'has_access' => $this->hasAccess,
            'is_subscribed' => $this->isSubscribed,
            'subscription_status' => $this->subscriptionStatus,
            'subscription_tier' => $this->subscriptionTier,
            'requires_subscription' => $this->requiresSubscription,
            'subscription_tiers' => $this->subscriptionTiers,
            'primary_route' => $this->primaryRoute,
            'is_pwa' => $this->isPWA,
            'status' => $this->status,
        ];
    }
}
```

---

## Command/Query Pattern (Optional CQRS)

### Command Example

```php
<?php

namespace App\Application\Commands;

class SubscribeToModuleCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly string $moduleId,
        public readonly string $tier,
        public readonly float $amount,
        public readonly string $currency = 'ZMW',
        public readonly string $billingCycle = 'monthly'
    ) {}
}
```

### Command Handler Example

```php
<?php

namespace App\Application\CommandHandlers;

use App\Application\Commands\SubscribeToModuleCommand;
use App\Application\UseCases\Module\SubscribeToModuleUseCase;

class SubscribeToModuleCommandHandler
{
    public function __construct(
        private SubscribeToModuleUseCase $useCase
    ) {}

    public function handle(SubscribeToModuleCommand $command): void
    {
        $this->useCase->execute(
            userId: $command->userId,
            moduleId: $command->moduleId,
            tier: $command->tier,
            amount: $command->amount,
            currency: $command->currency,
            billingCycle: $command->billingCycle
        );
    }
}
```

---

## Event Handler Template

```php
<?php

namespace App\Application\EventHandlers;

use App\Events\ModuleSubscriptionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class ModuleSubscriptionCreatedHandler implements ShouldQueue
{
    public function handle(ModuleSubscriptionCreated $event): void
    {
        // Send welcome email
        // Log analytics
        // Update user stats
        // etc.
    }
}
```

---

## Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test tests/Feature/Module/SubscribeToModuleTest.php

# Create test
php artisan make:test Module/SubscribeToModuleTest --unit
```

---

## Directory Structure

```
app/Application/
├── UseCases/
│   └── Module/
│       ├── SubscribeToModuleUseCase.php
│       ├── StartTrialUseCase.php
│       ├── CancelSubscriptionUseCase.php
│       ├── UpgradeSubscriptionUseCase.php
│       ├── GetUserModulesUseCase.php
│       ├── CheckModuleAccessUseCase.php
│       ├── RenewSubscriptionUseCase.php
│       └── ProcessExpiredSubscriptionsUseCase.php
├── DTOs/
│   ├── ModuleDTO.php
│   ├── ModuleSubscriptionDTO.php
│   ├── ModuleCardDTO.php
│   ├── SubscriptionTierDTO.php
│   └── ModuleAccessDTO.php
├── Commands/
│   ├── SubscribeToModuleCommand.php
│   ├── CancelSubscriptionCommand.php
│   └── UpgradeSubscriptionCommand.php
├── CommandHandlers/
│   ├── SubscribeToModuleCommandHandler.php
│   ├── CancelSubscriptionCommandHandler.php
│   └── UpgradeSubscriptionCommandHandler.php
├── Queries/
│   ├── GetUserModulesQuery.php
│   ├── GetModuleByIdQuery.php
│   └── GetAvailableModulesQuery.php
├── QueryHandlers/
│   ├── GetUserModulesQueryHandler.php
│   ├── GetModuleByIdQueryHandler.php
│   └── GetAvailableModulesQueryHandler.php
└── EventHandlers/
    ├── ModuleSubscriptionCreatedHandler.php
    ├── ModuleSubscriptionCancelledHandler.php
    └── ModuleSubscriptionExpiredHandler.php
```

---

## Implementation Order

### Step 1: Core DTOs
1. Create `ModuleDTO`
2. Create `ModuleSubscriptionDTO`
3. Create `ModuleCardDTO`

### Step 2: Read Use Cases
1. Create `GetUserModulesUseCase`
2. Create `CheckModuleAccessUseCase`
3. Test with existing data

### Step 3: Write Use Cases
1. Create `SubscribeToModuleUseCase`
2. Create `StartTrialUseCase`
3. Create `CancelSubscriptionUseCase`
4. Create `UpgradeSubscriptionUseCase`

### Step 4: Background Processing
1. Create `RenewSubscriptionUseCase`
2. Create `ProcessExpiredSubscriptionsUseCase`
3. Create scheduled command

### Step 5: Events (Optional)
1. Create domain events
2. Create event handlers
3. Register listeners

---

## Next Steps After Phase 3

### Phase 4: Presentation Layer

```bash
# Create controllers
php artisan make:controller HomeHubController
php artisan make:controller ModuleSubscriptionController

# Create middleware
php artisan make:middleware CheckModuleAccess

# Create requests
php artisan make:request SubscribeToModuleRequest
```

---

## Key Principles

### Use Cases
- Single responsibility
- Orchestrate domain operations
- No business logic (delegate to domain)
- Return DTOs, not entities

### DTOs
- Immutable
- Simple data structures
- Easy serialization
- No business logic

### Commands/Queries
- Separate reads from writes
- Commands change state
- Queries return data
- Both are simple data structures

---

**Ready to start Phase 3!** 🚀

**Estimated Time:** 2-3 days  
**Team:** Backend developers  
**Goal:** Build application orchestration layer
