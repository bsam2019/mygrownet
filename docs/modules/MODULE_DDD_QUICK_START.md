# Module System DDD Quick Start Guide

**Last Updated:** December 1, 2025

## What We Built (Phase 1)

We've implemented the **Domain Layer** of the Module System following DDD principles. This is the core business logic layer with zero framework dependencies.

---

## Key Concepts

### 1. Entities (Business Objects with Identity)

**Module** - Represents an app/module in the platform
```php
$module = Module::create(
    id: ModuleId::fromString('sme-accounting'),
    name: ModuleName::fromString('SME Accounting'),
    slug: ModuleSlug::fromString('accounting'),
    category: ModuleCategory::SME,
    description: 'Complete accounting solution for SMEs',
    accountTypes: [AccountType::BUSINESS],
    configuration: $config
);

// Check access
if ($module->isAccessibleBy(AccountType::BUSINESS)) {
    // Grant access
}
```

**ModuleSubscription** - Represents a user's subscription
```php
// Create paid subscription
$subscription = ModuleSubscription::create(
    id: SubscriptionId::fromInt(1),
    userId: $user->id,
    moduleId: ModuleId::fromString('accounting'),
    tier: SubscriptionTier::pro(),
    amount: Money::fromAmount(200),
    billingCycle: 'monthly'
);

// Create trial
$trial = ModuleSubscription::createTrial(
    id: SubscriptionId::fromInt(2),
    userId: $user->id,
    moduleId: ModuleId::fromString('accounting'),
    tier: SubscriptionTier::basic(),
    trialDays: 14
);

// Check status
if ($subscription->isActive()) {
    // Allow access
}
```

### 2. Value Objects (Immutable Domain Concepts)

All value objects are **immutable** and **self-validating**:

```php
// ModuleId
$id = ModuleId::fromString('wedding-planner');

// Money (currency-aware)
$price = Money::fromAmount(50, 'ZMW');
$total = $price->add(Money::fromAmount(10));
echo $total->formatted(); // "K60.00"

// ModuleSlug (validated)
$slug = ModuleSlug::fromString('sme-accounting'); // ✅
$slug = ModuleSlug::fromString('SME Accounting'); // ❌ throws exception

// Enums
$status = ModuleStatus::ACTIVE;
$category = ModuleCategory::SME;
```

### 3. Domain Services (Complex Business Logic)

**ModuleSubscriptionService** - Manages subscription lifecycle
```php
$service = new ModuleSubscriptionService($repository);

// Subscribe user
$subscription = $service->subscribe(
    userId: $user->id,
    moduleId: ModuleId::fromString('accounting'),
    tier: SubscriptionTier::pro(),
    amount: Money::fromAmount(200),
    billingCycle: 'monthly'
);

// Start trial
$trial = $service->startTrial(
    userId: $user->id,
    moduleId: ModuleId::fromString('accounting'),
    tier: SubscriptionTier::basic()
);

// Upgrade
$service->upgrade(
    userId: $user->id,
    moduleId: ModuleId::fromString('accounting'),
    newTier: SubscriptionTier::enterprise(),
    newAmount: Money::fromAmount(500)
);
```

**ModuleAccessService** - Controls access permissions
```php
$accessService = new ModuleAccessService($moduleRepo, $subscriptionRepo);

// Check access
if ($accessService->canAccess($user, ModuleId::fromString('accounting'))) {
    // Grant access
}

// Get user's modules
$modules = $accessService->getUserModules($user);

// Get available modules (not subscribed)
$available = $accessService->getAvailableModules($user);
```

### 4. Repository Interfaces (Data Access Contracts)

Defined in Domain, implemented in Infrastructure:

```php
interface ModuleRepositoryInterface
{
    public function findById(ModuleId $id): ?Module;
    public function findBySlug(ModuleSlug $slug): ?Module;
    public function findByAccountType(AccountType $type): array;
    public function save(Module $module): void;
}

interface ModuleSubscriptionRepositoryInterface
{
    public function findByUserAndModule(int $userId, ModuleId $moduleId): ?ModuleSubscription;
    public function findActiveByUser(int $userId): array;
    public function save(ModuleSubscription $subscription): void;
}
```

---

## How It Works Together

### Example: User Subscribes to Module

```php
// 1. Get the module
$module = $moduleRepository->findBySlug(
    ModuleSlug::fromString('accounting')
);

// 2. Check if user has required account type
if (!$module->isAccessibleBy(AccountType::BUSINESS)) {
    throw new \DomainException('Account type not allowed');
}

// 3. Create subscription
$subscription = $subscriptionService->subscribe(
    userId: $user->id,
    moduleId: $module->getId(),
    tier: SubscriptionTier::pro(),
    amount: Money::fromAmount(200),
    billingCycle: 'monthly'
);

// 4. Check access
if ($accessService->canAccess($user, $module->getId())) {
    // Redirect to module
}
```

### Example: Check Module Access (Middleware)

```php
public function handle(Request $request, Closure $next, string $moduleSlug)
{
    $user = $request->user();
    $moduleId = ModuleId::fromString($moduleSlug);
    
    if (!$this->accessService->canAccess($user, $moduleId)) {
        return redirect()->route('home-hub')
            ->with('error', 'Please subscribe to access this module');
    }
    
    return $next($request);
}
```

---

## Directory Structure

```
app/Domain/Module/
├── Entities/
│   ├── Module.php                    # Module entity
│   └── ModuleSubscription.php        # Subscription entity
├── ValueObjects/
│   ├── ModuleId.php                  # Module identifier
│   ├── ModuleName.php                # Module name
│   ├── ModuleSlug.php                # URL slug
│   ├── ModuleCategory.php            # Category enum
│   ├── ModuleStatus.php              # Status enum
│   ├── ModuleConfiguration.php       # Configuration VO
│   ├── SubscriptionId.php            # Subscription ID
│   ├── SubscriptionTier.php          # Tier VO
│   ├── SubscriptionStatus.php        # Status enum
│   └── Money.php                     # Money VO
├── Services/
│   ├── ModuleSubscriptionService.php # Subscription logic
│   └── ModuleAccessService.php       # Access control
└── Repositories/
    ├── ModuleRepositoryInterface.php
    └── ModuleSubscriptionRepositoryInterface.php
```

---

## Design Principles Applied

### 1. **Encapsulation**
Business rules are inside entities, not scattered in controllers:
```php
// ✅ Good - Business rule in entity
$subscription->cancel();

// ❌ Bad - Business rule in controller
$subscription->status = 'cancelled';
$subscription->cancelled_at = now();
```

### 2. **Immutability**
Value objects cannot be changed after creation:
```php
$price = Money::fromAmount(100);
$newPrice = $price->add(Money::fromAmount(50)); // Creates new instance
// $price is still 100
```

### 3. **Type Safety**
Use value objects instead of primitives:
```php
// ✅ Good
function subscribe(ModuleId $moduleId, Money $amount) { }

// ❌ Bad
function subscribe(string $moduleId, int $amount) { }
```

### 4. **Single Responsibility**
Each class has one reason to change:
- **Module** - Module business rules
- **ModuleSubscription** - Subscription lifecycle
- **ModuleAccessService** - Access control logic
- **ModuleSubscriptionService** - Subscription operations

---

## Next Steps

### Phase 2: Infrastructure Layer
1. Create database migrations
2. Build Eloquent models
3. Implement repositories
4. Set up dependency injection

### Phase 3: Application Layer
1. Create use cases
2. Build DTOs
3. Add command/query handlers

### Phase 4: Presentation Layer
1. Build controllers
2. Create middleware
3. Set up routes
4. Build Vue components

---

## Testing Examples

### Unit Test (Domain Entity)
```php
test('subscription can be cancelled', function () {
    $subscription = ModuleSubscription::create(
        id: SubscriptionId::fromInt(1),
        userId: 1,
        moduleId: ModuleId::fromString('test'),
        tier: SubscriptionTier::basic(),
        amount: Money::fromAmount(50)
    );
    
    $subscription->cancel();
    
    expect($subscription->getStatus()->isCancelled())->toBeTrue();
    expect($subscription->isActive())->toBeFalse();
});
```

### Integration Test (Repository)
```php
test('can find subscription by user and module', function () {
    $subscription = ModuleSubscription::create(/* ... */);
    $repository->save($subscription);
    
    $found = $repository->findByUserAndModule(1, ModuleId::fromString('test'));
    
    expect($found)->not->toBeNull();
    expect($found->getUserId())->toBe(1);
});
```

---

## Common Patterns

### Creating a Module
```php
$config = ModuleConfiguration::create(
    icon: 'calculator',
    color: 'purple',
    routes: [
        'integrated' => '/modules/accounting',
        'standalone' => '/apps/accounting'
    ],
    pwaConfig: [
        'enabled' => true,
        'installable' => true
    ],
    features: [
        'offline' => true,
        'dataSync' => true,
        'multiUser' => true
    ],
    subscriptionTiers: ['basic', 'pro', 'enterprise'],
    requiresSubscription: true
);

$module = Module::create(
    id: ModuleId::fromString('sme-accounting'),
    name: ModuleName::fromString('SME Accounting'),
    slug: ModuleSlug::fromString('accounting'),
    category: ModuleCategory::SME,
    description: 'Complete accounting solution',
    accountTypes: [AccountType::BUSINESS],
    configuration: $config
);
```

### Handling Subscription Lifecycle
```php
// Start trial
$trial = $service->startTrial($userId, $moduleId, $tier);

// Convert to paid
$service->convertFromTrial($userId, $moduleId, $amount, 'monthly');

// Upgrade
$service->upgrade($userId, $moduleId, $newTier, $newAmount);

// Cancel
$service->cancel($userId, $moduleId);

// Process expired (cron job)
$renewed = $service->processExpiredSubscriptions();
```

---

## Benefits of This Approach

✅ **Testable** - Pure business logic, easy to unit test
✅ **Maintainable** - Clear separation of concerns
✅ **Type-Safe** - Value objects prevent invalid states
✅ **Flexible** - Easy to change infrastructure without touching domain
✅ **Readable** - Code reads like business requirements
✅ **Scalable** - Clean architecture supports growth

---

## Resources

- [MODULE_DDD_IMPLEMENTATION_STATUS.md](MODULE_DDD_IMPLEMENTATION_STATUS.md) - Full status
- [MODULE_SYSTEM_ARCHITECTURE.md](MODULE_SYSTEM_ARCHITECTURE.md) - Architecture details
- [domain-design.md](../../.kiro/steering/domain-design.md) - DDD guidelines

---

**Phase 1 Complete!** ✅  
**Ready for Phase 2: Infrastructure Layer** 🚀
