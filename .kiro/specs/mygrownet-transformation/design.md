# Design Document

## Overview

The MyGrowNet transformation involves converting the existing VBIF investment platform into a comprehensive multilevel marketing (MLM) ecosystem with educational content, community projects, physical asset rewards, and a five-level commission structure. The design follows Domain-Driven Design principles and maintains the existing Laravel/Vue.js technology stack while introducing new bounded contexts for MLM operations, asset management, and community features.

## Architecture

### High-Level Architecture

The system will be organized into the following bounded contexts:

1. **MLM Management Context** - Handles multilevel commissions, team volume tracking, and performance bonuses
2. **Membership & Tiers Context** - Manages tier advancement, qualifications, and member progression
3. **Asset Reward Context** - Manages physical asset allocation, tracking, and ownership transfers
4. **Community Projects Context** - Handles profit-sharing pools, project investments, and member voting
5. **Education & Content Context** - Manages educational resources, webinars, and tier-specific content
6. **Gamification Context** - Handles leaderboards, achievements, badges, and recognition systems

### System Integration Points

- **Payment Processing** - Integration with mobile money (MTN, Airtel) and bank payment gateways
- **Asset Management** - Integration with property management and vehicle tracking systems
- **Communication** - SMS/Email notifications for commissions, achievements, and events
- **Analytics** - Real-time dashboard with earnings tracking and team performance metrics

## Components and Interfaces

### 1. MLM Management Context

#### Core Entities
```php
// Domain/MLM/Entities/Commission.php
class Commission
{
    private CommissionId $id;
    private UserId $earnerId;
    private UserId $sourceId;
    private CommissionLevel $level;
    private CommissionAmount $amount;
    private CommissionType $type; // REFERRAL, TEAM_VOLUME, PERFORMANCE
    private CommissionStatus $status;
    private DateTimeImmutable $earnedAt;
    
    public function calculateAmount(
        PackagePurchase $purchase, 
        CommissionLevel $level
    ): CommissionAmount;
    
    public function isEligibleForPayment(): bool;
}

// Domain/MLM/Entities/TeamVolume.php
class TeamVolume
{
    private UserId $userId;
    private VolumeAmount $personalVolume;
    private VolumeAmount $teamVolume;
    private int $teamDepth;
    private DateTimeImmutable $periodStart;
    private DateTimeImmutable $periodEnd;
    
    public function calculatePerformanceBonus(): PerformanceBonusAmount;
    public function qualifiesForTierUpgrade(TierRequirements $requirements): bool;
}
```

#### Services
```php
// Domain/MLM/Services/CommissionCalculationService.php
class CommissionCalculationService
{
    public function calculateMultilevelCommissions(
        PackagePurchase $purchase,
        UserNetwork $network
    ): array;
    
    public function calculateTeamVolumeBonus(
        TeamVolume $volume,
        TierLevel $tier
    ): PerformanceBonusAmount;
}

// Domain/MLM/Services/NetworkBuildingService.php
class NetworkBuildingService
{
    public function addReferral(UserId $referrerId, UserId $referralId): void;
    public function calculateNetworkDepth(UserId $userId): int;
    public function getTeamMembers(UserId $userId, int $maxDepth = 5): array;
}
```

### 2. Membership & Tiers Context

#### Core Entities
```php
// Domain/Membership/Entities/MembershipTier.php
class MembershipTier
{
    private TierId $id;
    private TierName $name;
    private MonthlyFee $monthlyFee;
    private TierRequirements $requirements;
    private TierBenefits $benefits;
    
    public function canUpgradeTo(MembershipTier $targetTier): bool;
    public function calculateMonthlyShare(): MonthlyShareAmount;
    public function getCommissionRates(): array;
}

// Domain/Membership/Entities/TierQualification.php
class TierQualification
{
    private UserId $userId;
    private TierId $currentTier;
    private int $activeReferrals;
    private VolumeAmount $teamVolume;
    private int $consecutiveMonths;
    
    public function qualifiesForUpgrade(TierId $targetTier): bool;
    public function maintainsTierRequirements(): bool;
}
```

### 3. Asset Reward Context

#### Core Entities
```php
// Domain/Assets/Entities/PhysicalAsset.php
class PhysicalAsset
{
    private AssetId $id;
    private AssetType $type; // SMARTPHONE, MOTORBIKE, CAR, PROPERTY
    private AssetValue $value;
    private AssetStatus $status;
    private UserId $ownerId;
    private OwnershipConditions $conditions;
    
    public function canTransferOwnership(): bool;
    public function calculateIncomeGeneration(): IncomeAmount;
    public function applyDepreciation(): AssetValue;
}

// Domain/Assets/Entities/AssetAllocation.php
class AssetAllocation
{
    private UserId $userId;
    private TierId $qualifyingTier;
    private VolumeAmount $qualifyingVolume;
    private int $maintenancePeriod;
    private AllocationStatus $status;
    
    public function isEligibleForAsset(AssetType $assetType): bool;
    public function hasMetMaintenanceRequirements(): bool;
}
```

### 4. Community Projects Context

#### Core Entities
```php
// Domain/Community/Entities/CommunityProject.php
class CommunityProject
{
    private ProjectId $id;
    private ProjectName $name;
    private ProjectType $type; // REAL_ESTATE, AGRICULTURE, SME
    private InvestmentAmount $totalInvestment;
    private ProjectStatus $status;
    private array $contributors;
    
    public function addContribution(UserId $userId, ContributionAmount $amount): void;
    public function calculateProfitShare(UserId $userId): ProfitShareAmount;
    public function isEligibleForVoting(UserId $userId): bool;
}

// Domain/Community/Entities/ProfitDistribution.php
class ProfitDistribution
{
    private ProjectId $projectId;
    private ProfitAmount $totalProfit;
    private DistributionPeriod $period;
    private array $distributions;
    
    public function calculateMemberShare(
        UserId $userId, 
        TierId $tier
    ): ProfitShareAmount;
}
```

## Data Models

### Database Schema Design

#### MLM Tables
```sql
-- Multilevel commission tracking
CREATE TABLE mlm_commissions (
    id BIGINT PRIMARY KEY,
    earner_id BIGINT REFERENCES users(id),
    source_id BIGINT REFERENCES users(id),
    level TINYINT, -- 1-5 levels
    amount DECIMAL(10,2),
    type ENUM('REFERRAL', 'TEAM_VOLUME', 'PERFORMANCE'),
    status ENUM('PENDING', 'PAID', 'CANCELLED'),
    earned_at TIMESTAMP,
    paid_at TIMESTAMP NULL
);

-- Team volume tracking
CREATE TABLE team_volumes (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    personal_volume DECIMAL(12,2),
    team_volume DECIMAL(12,2),
    team_depth INT,
    period_start DATE,
    period_end DATE,
    created_at TIMESTAMP
);

-- Network relationships
CREATE TABLE user_networks (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    referrer_id BIGINT REFERENCES users(id),
    level TINYINT,
    path TEXT, -- Materialized path for efficient queries
    created_at TIMESTAMP
);
```

#### Asset Management Tables
```sql
-- Physical asset inventory
CREATE TABLE physical_assets (
    id BIGINT PRIMARY KEY,
    type ENUM('SMARTPHONE', 'TABLET', 'MOTORBIKE', 'CAR', 'PROPERTY'),
    model VARCHAR(255),
    value DECIMAL(10,2),
    status ENUM('AVAILABLE', 'ALLOCATED', 'TRANSFERRED'),
    owner_id BIGINT REFERENCES users(id) NULL,
    allocated_at TIMESTAMP NULL,
    transferred_at TIMESTAMP NULL
);

-- Asset allocation tracking
CREATE TABLE asset_allocations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT REFERENCES users(id),
    asset_id BIGINT REFERENCES physical_assets(id),
    qualifying_tier_id BIGINT REFERENCES membership_tiers(id),
    qualifying_volume DECIMAL(12,2),
    maintenance_period INT, -- months
    status ENUM('PENDING', 'ACTIVE', 'COMPLETED', 'FORFEITED'),
    allocated_at TIMESTAMP,
    completed_at TIMESTAMP NULL
);
```

#### Community Projects Tables
```sql
-- Community investment projects
CREATE TABLE community_projects (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    type ENUM('REAL_ESTATE', 'AGRICULTURE', 'SME', 'DIGITAL'),
    description TEXT,
    target_amount DECIMAL(12,2),
    current_amount DECIMAL(12,2),
    status ENUM('PLANNING', 'FUNDING', 'ACTIVE', 'COMPLETED'),
    created_at TIMESTAMP
);

-- Project contributions
CREATE TABLE project_contributions (
    id BIGINT PRIMARY KEY,
    project_id BIGINT REFERENCES community_projects(id),
    user_id BIGINT REFERENCES users(id),
    amount DECIMAL(10,2),
    contributed_at TIMESTAMP
);

-- Profit distributions
CREATE TABLE profit_distributions (
    id BIGINT PRIMARY KEY,
    project_id BIGINT REFERENCES community_projects(id),
    user_id BIGINT REFERENCES users(id),
    tier_id BIGINT REFERENCES membership_tiers(id),
    profit_share_percentage DECIMAL(5,2),
    amount DECIMAL(10,2),
    period_start DATE,
    period_end DATE,
    distributed_at TIMESTAMP
);
```

## Error Handling

### Domain-Specific Exceptions
```php
// MLM Context Exceptions
class InsufficientTeamVolumeException extends DomainException {}
class InvalidCommissionLevelException extends DomainException {}
class CommissionCalculationException extends DomainException {}

// Asset Context Exceptions
class AssetNotAvailableException extends DomainException {}
class InsufficientQualificationException extends DomainException {}
class AssetMaintenanceViolationException extends DomainException {}

// Community Context Exceptions
class ProjectContributionLimitExceededException extends DomainException {}
class IneligibleForProfitSharingException extends DomainException {}
```

### Error Response Handling
- **Validation Errors**: Return structured error responses with field-specific messages
- **Business Rule Violations**: Return domain-specific error messages with suggested actions
- **System Errors**: Log errors and return generic user-friendly messages
- **Payment Failures**: Implement retry mechanisms and notification systems

## Testing Strategy

### Unit Testing
- **Domain Entities**: Test business logic, invariants, and state transitions
- **Value Objects**: Test validation, immutability, and behavior
- **Domain Services**: Test complex business calculations and rules
- **Commission Calculations**: Test multilevel commission accuracy across all scenarios

### Integration Testing
- **Repository Implementations**: Test data persistence and retrieval
- **Payment Gateway Integration**: Test commission payments and subscription billing
- **Asset Management Integration**: Test asset allocation and tracking workflows
- **Email/SMS Notifications**: Test communication triggers and delivery

### Feature Testing
- **MLM Workflows**: Test complete referral and commission processes
- **Tier Advancement**: Test qualification checking and upgrade processes
- **Asset Reward Workflows**: Test allocation, maintenance, and transfer processes
- **Community Project Workflows**: Test contribution, voting, and profit distribution

### Performance Testing
- **Commission Calculations**: Test performance with large network structures
- **Team Volume Aggregation**: Test efficiency of volume rollup calculations
- **Dashboard Queries**: Test real-time analytics performance
- **Bulk Payment Processing**: Test monthly commission payment batches

## Implementation Considerations

### Migration Strategy
1. **Phase 1**: Implement MLM commission structure alongside existing investment system
2. **Phase 2**: Add tier advancement and team volume tracking
3. **Phase 3**: Implement asset reward system and community projects
4. **Phase 4**: Add gamification and advanced analytics
5. **Phase 5**: Migrate existing users to new tier structure

### Data Migration
- **User Tier Mapping**: Map existing investment tiers to new membership tiers
- **Commission History**: Preserve existing commission data with new structure
- **Network Relationships**: Build referral networks from existing data
- **Asset Allocations**: Initialize asset tracking for existing high-tier members

### Performance Optimization
- **Materialized Paths**: Use materialized path pattern for efficient network queries
- **Cached Calculations**: Cache team volume and commission calculations
- **Background Processing**: Process commission calculations and payments asynchronously
- **Database Indexing**: Optimize indexes for network traversal and volume aggregation

### Security Considerations
- **Commission Integrity**: Implement audit trails for all commission calculations
- **Asset Security**: Secure asset allocation and transfer processes
- **Payment Security**: Encrypt sensitive payment and financial data
- **Access Control**: Implement role-based access for administrative functions

### Scalability Planning
- **Horizontal Scaling**: Design for database sharding by user networks
- **Caching Strategy**: Implement Redis caching for frequently accessed data
- **Queue Management**: Use Laravel queues for background processing
- **API Rate Limiting**: Implement rate limiting for high-frequency operations