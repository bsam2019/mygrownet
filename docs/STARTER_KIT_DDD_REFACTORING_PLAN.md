# Starter Kit DDD Refactoring Plan

## Current Status
✅ **Library Module**: Fully refactored to DDD
⏳ **Starter Kit Module**: Needs refactoring

## Starter Kit Domain Model

### Value Objects to Create
```
app/Domain/StarterKit/ValueObjects/
├── Money.php ✅ (Created)
├── PurchaseStatus.php ✅ (Created)
├── ShopCredit.php (TODO)
├── LifetimePoints.php (TODO)
├── InvoiceNumber.php (TODO)
└── UnlockSchedule.php (TODO)
```

### Entities to Create
```
app/Domain/StarterKit/Entities/
├── StarterKitPurchase.php (Rich entity with business logic)
├── ContentItem.php (Rich entity for content management)
├── ContentUnlock.php (Unlock scheduling logic)
└── MemberAchievement.php (Achievement logic)
```

### Policies
```
app/Domain/StarterKit/Policies/
├── PurchasePolicy.php (Can user purchase? Already has one?)
└── ContentAccessPolicy.php (Can user access content?)
```

### Domain Services
```
app/Domain/StarterKit/Services/
├── UnlockScheduler.php (Calculate unlock dates)
├── ShopCreditCalculator.php (Calculate credit expiry)
└── PointsCalculator.php (Calculate LP bonus)
```

### Repository Interfaces
```
app/Domain/StarterKit/Repositories/
├── StarterKitPurchaseRepositoryInterface.php
├── ContentItemRepositoryInterface.php
└── ContentUnlockRepositoryInterface.php
```

### Domain Events
```
app/Domain/StarterKit/Events/
├── StarterKitPurchased.php
├── ContentUnlocked.php
├── ShopCreditGranted.php
└── AchievementEarned.php
```

## Application Layer

### Use Cases to Create
```
app/Application/StarterKit/UseCases/
├── PurchaseStarterKitUseCase.php
├── ProcessWalletPaymentUseCase.php
├── ProcessExternalPaymentUseCase.php
├── UnlockContentUseCase.php
├── GrantShopCreditUseCase.php
└── AwardPointsUseCase.php
```

### Command Handlers
```
app/Application/StarterKit/Commands/
├── PurchaseStarterKitCommand.php
└── PurchaseStarterKitHandler.php
```

## Infrastructure Layer

### Eloquent Models (Data Mappers)
```
app/Infrastructure/Persistence/Eloquent/StarterKit/
├── StarterKitPurchaseModel.php (from app/Models/StarterKitPurchase.php)
├── ContentItemModel.php (from app/Models/StarterKitContentItem.php)
├── ContentUnlockModel.php (from app/Models/StarterKitUnlock.php)
├── ContentAccessModel.php (from app/Models/StarterKitContentAccess.php)
└── MemberAchievementModel.php (from app/Models/MemberAchievement.php)
```

### Repository Implementations
```
app/Infrastructure/Persistence/Eloquent/StarterKit/
├── EloquentStarterKitPurchaseRepository.php
├── EloquentContentItemRepository.php
└── EloquentContentUnlockRepository.php
```

## Migration Steps

### Phase 1: Value Objects & Entities (2-3 hours)
1. ✅ Create Money value object
2. ✅ Create PurchaseStatus value object
3. Create remaining value objects
4. Create rich domain entities with business logic

### Phase 2: Policies & Services (1-2 hours)
1. Extract business rules into policies
2. Create domain services for complex calculations
3. Move logic from StarterKitService to domain layer

### Phase 3: Repositories (1-2 hours)
1. Define repository interfaces in domain
2. Move Eloquent models to Infrastructure
3. Implement repositories with entity mapping

### Phase 4: Use Cases (2-3 hours)
1. Create use cases for each workflow
2. Update controllers to use use cases
3. Remove direct model access from controllers

### Phase 5: Events (1 hour)
1. Create domain events
2. Set up event listeners
3. Decouple side effects (emails, notifications)

### Phase 6: Testing & Cleanup (1-2 hours)
1. Write unit tests for domain logic
2. Delete old unused files
3. Update documentation

## Benefits After Refactoring

### Testability
- Domain logic testable without Laravel
- No database needed for business rule tests
- Fast unit tests

### Maintainability
- Clear separation of concerns
- Business logic in one place
- Easy to understand and modify

### Scalability
- Easy to add new features
- Can swap data sources
- Domain events for extensibility

### Code Quality
- Value objects prevent invalid states
- Rich entities with behavior
- Explicit business rules in policies

## Current Files to Migrate

### Models (Move to Infrastructure)
- `app/Models/StarterKitPurchase.php` → `app/Infrastructure/Persistence/Eloquent/StarterKit/StarterKitPurchaseModel.php`
- `app/Models/StarterKitContentItem.php` → `app/Infrastructure/Persistence/Eloquent/StarterKit/ContentItemModel.php`
- `app/Models/StarterKitUnlock.php` → `app/Infrastructure/Persistence/Eloquent/StarterKit/ContentUnlockModel.php`
- `app/Models/StarterKitContentAccess.php` → `app/Infrastructure/Persistence/Eloquent/StarterKit/ContentAccessModel.php`
- `app/Models/MemberAchievement.php` → `app/Infrastructure/Persistence/Eloquent/StarterKit/MemberAchievementModel.php`

### Services (Extract to Domain/Application)
- `app/Services/StarterKitService.php` → Extract logic to:
  - Domain entities (business rules)
  - Domain services (calculations)
  - Use cases (workflows)
  - Keep thin service as facade if needed

### Controllers (Make Thin)
- `app/Http/Controllers/MyGrowNet/StarterKitController.php` → Use use cases
- `app/Http/Controllers/Admin/StarterKitAdminController.php` → Use use cases
- `app/Http/Controllers/Admin/StarterKitContentController.php` → Use use cases

## Estimated Total Time
**10-15 hours** for complete DDD refactoring of Starter Kit module

## Recommendation
Given the time investment, consider:
1. **Option A**: Refactor incrementally (one feature at a time)
2. **Option B**: Refactor during next major feature addition
3. **Option C**: Keep current implementation, apply DDD to new modules only

The current Starter Kit code **works fine** and is maintainable. DDD refactoring is an **optimization**, not a necessity for launch.
