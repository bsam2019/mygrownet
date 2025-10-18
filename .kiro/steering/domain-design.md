# Domain-Driven Design (DDD) Guidelines

## Domain Structure

The VBIF system follows Domain-Driven Design principles to organize business logic around core domains and bounded contexts.

### Bounded Contexts

The system is organized into the following bounded contexts:

#### 1. Investment Management Context
**Purpose**: Handles investment tiers, user investments, and tier-related business rules
**Core Entities**: InvestmentTier, UserInvestment
**Value Objects**: InvestmentAmount, TierBenefits
**Domain Services**: InvestmentTierService, InvestmentCalculationService

#### 2. Reward & Commission Context  
**Purpose**: Manages MLM commissions, referral bonuses, and reward calculations
**Core Entities**: Commission, ReferralBonus, MatrixPosition
**Value Objects**: CommissionRate, BonusAmount
**Domain Services**: CommissionCalculationService, MatrixService, RewardDistributionService

#### 3. Financial Operations Context
**Purpose**: Handles withdrawals, profit distributions, and financial transactions
**Core Entities**: WithdrawalRequest, ProfitDistribution, Transaction
**Value Objects**: WithdrawalAmount, PenaltyAmount, DistributionAmount
**Domain Services**: WithdrawalService, ProfitDistributionService, PenaltyCalculationService

#### 4. User Management Context
**Purpose**: User authentication, profiles, and membership management
**Core Entities**: User, UserProfile, Membership
**Value Objects**: UserCredentials, ContactInformation
**Domain Services**: UserRegistrationService, MembershipService

## Directory Structure (Domain-Oriented)

```
app/
├── Domain/                    # Domain layer
│   ├── Investment/           # Investment Management Context
│   │   ├── Entities/         # Core business entities
│   │   ├── ValueObjects/     # Immutable value objects
│   │   ├── Services/         # Domain services
│   │   ├── Repositories/     # Repository interfaces
│   │   └── Events/           # Domain events
│   ├── Reward/              # Reward & Commission Context
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Events/
│   ├── Financial/           # Financial Operations Context
│   │   ├── Entities/
│   │   ├── ValueObjects/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   └── Events/
│   └── User/                # User Management Context
│       ├── Entities/
│       ├── ValueObjects/
│       ├── Services/
│       ├── Repositories/
│       └── Events/
├── Infrastructure/           # Infrastructure layer
│   ├── Persistence/         # Database implementations
│   │   ├── Eloquent/        # Eloquent models (data layer)
│   │   └── Repositories/    # Repository implementations
│   ├── External/            # External service integrations
│   └── Events/              # Event handlers
├── Application/             # Application layer
│   ├── UseCases/            # Application use cases
│   ├── Commands/            # Command handlers
│   ├── Queries/             # Query handlers
│   └── DTOs/                # Data transfer objects
└── Presentation/            # Presentation layer (existing structure)
    ├── Http/
    │   ├── Controllers/
    │   └── Requests/
    └── Console/
```

## Domain Design Principles

### 1. Entity Design
- **Rich Domain Models**: Entities contain business logic, not just data
- **Encapsulation**: Business rules are enforced within entities
- **Identity**: Each entity has a unique identifier
- **Invariants**: Entities maintain their own consistency rules

```php
// Example: Investment Tier Entity
class InvestmentTier
{
    private function __construct(
        private TierId $id,
        private TierName $name,
        private InvestmentAmount $minimumAmount,
        private CommissionRate $profitShareRate
    ) {}
    
    public function calculateProfitShare(InvestmentAmount $totalInvestment): ProfitAmount
    {
        // Business logic encapsulated in entity
        return $this->profitShareRate->calculateProfit($totalInvestment);
    }
    
    public function canUpgradeTo(InvestmentTier $targetTier): bool
    {
        // Domain rule: can only upgrade to higher tiers
        return $targetTier->minimumAmount->isGreaterThan($this->minimumAmount);
    }
}
```

### 2. Value Objects
- **Immutable**: Cannot be changed after creation
- **Self-validating**: Validate their own constraints
- **Behavior-rich**: Contain relevant business operations

```php
// Example: Investment Amount Value Object
class InvestmentAmount
{
    private function __construct(private int $amount)
    {
        if ($amount < 0) {
            throw new InvalidInvestmentAmountException();
        }
    }
    
    public static function fromKwacha(int $amount): self
    {
        return new self($amount);
    }
    
    public function add(InvestmentAmount $other): self
    {
        return new self($this->amount + $other->amount);
    }
    
    public function calculatePercentage(float $percentage): self
    {
        return new self((int)($this->amount * $percentage / 100));
    }
}
```

### 3. Domain Services
- **Stateless**: No internal state
- **Domain Logic**: Contain business logic that doesn't belong to entities
- **Coordination**: Orchestrate operations between multiple entities

```php
// Example: Commission Calculation Service
class CommissionCalculationService
{
    public function calculateReferralCommission(
        UserInvestment $investment,
        MatrixPosition $referrerPosition,
        int $level
    ): CommissionAmount {
        // Complex business logic for commission calculation
        $baseCommission = $investment->getAmount()->calculatePercentage(
            $this->getCommissionRateForLevel($level)
        );
        
        return $this->applyMatrixBonuses($baseCommission, $referrerPosition);
    }
}
```

### 4. Repository Pattern
- **Interface in Domain**: Repository interfaces defined in domain layer
- **Implementation in Infrastructure**: Concrete implementations in infrastructure layer
- **Domain-focused**: Methods reflect domain language, not database operations

```php
// Domain layer interface
interface InvestmentTierRepository
{
    public function findByName(TierName $name): ?InvestmentTier;
    public function findEligibleUpgrades(InvestmentAmount $currentAmount): array;
    public function save(InvestmentTier $tier): void;
}

// Infrastructure layer implementation
class EloquentInvestmentTierRepository implements InvestmentTierRepository
{
    public function findByName(TierName $name): ?InvestmentTier
    {
        $model = InvestmentTierModel::where('name', $name->value())->first();
        return $model ? $this->toDomainEntity($model) : null;
    }
}
```

### 5. Domain Events
- **Business Events**: Represent important business occurrences
- **Decoupling**: Allow different parts of the system to react to events
- **Eventual Consistency**: Enable eventual consistency across bounded contexts

```php
// Example: Domain Event
class InvestmentUpgraded
{
    public function __construct(
        public readonly UserId $userId,
        public readonly TierId $fromTierId,
        public readonly TierId $toTierId,
        public readonly InvestmentAmount $additionalAmount,
        public readonly DateTimeImmutable $occurredAt
    ) {}
}
```

## Implementation Guidelines

### 1. Separation of Concerns
- **Domain Layer**: Pure business logic, no framework dependencies
- **Application Layer**: Use cases and application services
- **Infrastructure Layer**: Framework-specific implementations
- **Presentation Layer**: Controllers, requests, and responses

### 2. Dependency Direction
- **Inward Dependencies**: Outer layers depend on inner layers
- **Interface Segregation**: Use interfaces to decouple layers
- **Dependency Injection**: Inject dependencies rather than creating them

### 3. Testing Strategy
- **Unit Tests**: Test domain entities and value objects in isolation
- **Integration Tests**: Test repository implementations and external services
- **Application Tests**: Test use cases and application services
- **Feature Tests**: Test complete user workflows

### 4. Migration Strategy
- **Gradual Refactoring**: Migrate existing code incrementally
- **Facade Pattern**: Use facades to maintain existing interfaces while refactoring
- **Feature Flags**: Use feature flags to switch between old and new implementations

## Common Patterns

### 1. Aggregate Pattern
Group related entities under a single aggregate root to maintain consistency.

### 2. Factory Pattern
Use factories to create complex domain objects with proper validation.

### 3. Specification Pattern
Use specifications to encapsulate business rules and queries.

### 4. Command Query Responsibility Segregation (CQRS)
Separate read and write operations for better performance and scalability.

## Anti-Patterns to Avoid

- **Anemic Domain Models**: Entities with only getters/setters and no behavior
- **God Objects**: Large entities or services that do too much
- **Leaky Abstractions**: Domain logic leaking into presentation or infrastructure layers
- **Database-Driven Design**: Designing domain based on database structure rather than business needs