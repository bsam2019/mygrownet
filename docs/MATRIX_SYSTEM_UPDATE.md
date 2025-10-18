# Matrix System Update - 7-Level Professional Progression

**Date**: October 17, 2025  
**Version**: 2.0

---

## Overview

The matrix system has been updated from a 5-level to a **7-level professional progression system** to align with the MyGrowNet platform concept. This update introduces professional level names and expanded network capacity.

---

## Changes Made

### 1. Commission Structure Update

**File**: `app/Models/ReferralCommission.php`

**Previous (5 Levels)**:
```php
1 => 12.0% // Level 1
2 => 6.0%  // Level 2
3 => 4.0%  // Level 3
4 => 2.0%  // Level 4
5 => 1.0%  // Level 5
Total: 25%
```

**Updated (7 Levels)**:
```php
1 => 15.0% // Level 1 (Associate)
2 => 10.0% // Level 2 (Professional)
3 => 8.0%  // Level 3 (Senior)
4 => 6.0%  // Level 4 (Manager)
5 => 4.0%  // Level 5 (Director)
6 => 3.0%  // Level 6 (Executive)
7 => 2.0%  // Level 7 (Ambassador)
Total: 48%
```

**New Constants Added**:
- `LEVEL_NAMES` - Professional level names for each level
- `MAX_COMMISSION_LEVELS` - Maximum depth (7)

**New Methods**:
- `getLevelName(int $level)` - Get professional name for a level
- `getAllCommissionRates()` - Get all rates with level names

---

### 2. Matrix Position Model Update

**File**: `app/Models/MatrixPosition.php`

**New Constants**:
```php
MAX_LEVELS = 7
MAX_CHILDREN = 3 // 3x3 matrix

LEVEL_NAMES = [
    1 => 'Associate',
    2 => 'Professional',
    3 => 'Senior',
    4 => 'Manager',
    5 => 'Director',
    6 => 'Executive',
    7 => 'Ambassador',
]

LEVEL_CAPACITY = [
    1 => 3,      // Associate
    2 => 9,      // Professional
    3 => 27,     // Senior
    4 => 81,     // Manager
    5 => 243,    // Director
    6 => 729,    // Executive
    7 => 2187,   // Ambassador
]
```

**New Attributes**:
- `level_name` - Computed attribute for professional level name
- `network_capacity` - Computed attribute for level capacity

**New Methods**:
- `getProfessionalLevelName(int $level)` - Static method to get level name
- `getTotalNetworkCapacity(int $maxLevel)` - Calculate total network capacity
- `scopeByProfessionalLevel($query, string $levelName)` - Query by level name
- `getNetworkStatistics()` - Get comprehensive position statistics
- `isAtMaxDepth()` - Check if at maximum level
- `getNextLevelInfo()` - Get information about next level
- `getAllLevelsInfo()` - Get all level information

---

### 3. New Matrix Service

**File**: `app/Services/MatrixService.php`

A comprehensive service for managing matrix operations:

**Key Methods**:

#### Placement
- `placeUserInMatrix(User $user, ?User $sponsor)` - Place user in matrix with spillover
- `createRootPosition(User $user)` - Create root position
- `findAndPlaceInMatrix(User $user, User $sponsor)` - Find available position
- `placeUnderPosition(User $user, MatrixPosition $parent, int $sponsorId)` - Place under specific position
- `findNextAvailablePosition(MatrixPosition $start)` - Breadth-first search for available slot

#### Network Tree
- `getUserNetworkTree(User $user, int $maxDepth)` - Get complete network tree
- `buildNetworkTree(MatrixPosition $position, int $maxDepth)` - Recursive tree building

#### Statistics
- `getNetworkStatistics(User $user)` - Comprehensive network stats
- `calculateNetworkSize(MatrixPosition $position)` - Calculate total network size
- `getMatrixOverview()` - Platform-wide matrix statistics
- `getPositionsByLevel()` - Positions grouped by professional level
- `getUserLevelProgression(User $user)` - User's progression through levels

---

### 4. Database Migration

**File**: `database/migrations/2025_10_17_000001_update_matrix_positions_for_7_levels.php`

**Changes**:
- Added `professional_level_name` column to `matrix_positions` table
- Added indexes for performance:
  - `idx_level_active` - Composite index on level and is_active
  - `idx_professional_level` - Index on professional_level_name
- Backfills existing records with professional level names

---

## Network Capacity

### Total Network Capacity by Level

| Level | Name | Positions | Cumulative Total |
|-------|------|-----------|------------------|
| 1 | Associate | 3 | 3 |
| 2 | Professional | 9 | 12 |
| 3 | Senior | 27 | 39 |
| 4 | Manager | 81 | 120 |
| 5 | Director | 243 | 363 |
| 6 | Executive | 729 | 1,092 |
| 7 | Ambassador | 2,187 | **3,279** |

**Maximum Network Size**: 3,279 members per person

---

## Commission Distribution Example

### Example: K200 Subscription Fee

| Level | Name | Rate | Commission per Person | Total if Full |
|-------|------|------|----------------------|---------------|
| 1 | Associate | 15% | K30 | K90 (3 people) |
| 2 | Professional | 10% | K20 | K180 (9 people) |
| 3 | Senior | 8% | K16 | K432 (27 people) |
| 4 | Manager | 6% | K12 | K972 (81 people) |
| 5 | Director | 4% | K8 | K1,944 (243 people) |
| 6 | Executive | 3% | K6 | K4,374 (729 people) |
| 7 | Ambassador | 2% | K4 | K8,748 (2,187 people) |

**Total Monthly Potential**: K16,740 (if full network)

---

## Usage Examples

### 1. Place User in Matrix

```php
use App\Services\MatrixService;

$matrixService = app(MatrixService::class);

// Place user with sponsor
$position = $matrixService->placeUserInMatrix($newUser, $sponsorUser);

// Place user without sponsor (root)
$position = $matrixService->placeUserInMatrix($newUser);
```

### 2. Get Network Statistics

```php
$stats = $matrixService->getNetworkStatistics($user);

// Returns:
[
    'has_position' => true,
    'current_level' => 3,
    'current_level_name' => 'Senior',
    'position_number' => 15,
    'is_full' => false,
    'available_slots' => 1,
    'total_network_size' => 45,
    'by_level' => [
        1 => ['level_name' => 'Associate', 'count' => 3, 'capacity' => 3],
        2 => ['level_name' => 'Professional', 'count' => 9, 'capacity' => 9],
        3 => ['level_name' => 'Senior', 'count' => 27, 'capacity' => 27],
        // ...
    ],
    'max_possible_network' => 3279,
    'network_completion_percentage' => 1.37,
]
```

### 3. Get User's Network Tree

```php
$tree = $matrixService->getUserNetworkTree($user, 7);

// Returns hierarchical tree structure with all downline members
```

### 4. Get Level Progression

```php
$progression = $matrixService->getUserLevelProgression($user);

// Returns:
[
    'current_level' => 3,
    'current_level_name' => 'Senior',
    'progression' => [
        [
            'level' => 1,
            'name' => 'Associate',
            'current_count' => 3,
            'capacity' => 3,
            'completion_percentage' => 100,
            'is_current_level' => false,
            'commission_rate' => 15.0,
        ],
        // ... for all 7 levels
    ],
    'total_network_size' => 45,
    'network_completion' => 1.37,
]
```

### 5. Get Matrix Overview (Admin)

```php
$overview = $matrixService->getMatrixOverview();

// Returns platform-wide statistics
[
    'total_positions' => 1250,
    'filled_positions' => 980,
    'full_positions' => 450,
    'by_level' => [
        1 => [
            'level' => 1,
            'name' => 'Associate',
            'count' => 500,
            'capacity' => 3,
            'fill_percentage' => 16666.67,
        ],
        // ... for all levels
    ],
    'spillover_count' => 125,
]
```

---

## Integration Points

### 1. User Registration

When a new user registers with a referral code:

```php
// In registration controller
$sponsor = User::where('referral_code', $request->referral_code)->first();
$matrixService = app(MatrixService::class);
$position = $matrixService->placeUserInMatrix($newUser, $sponsor);
```

### 2. Commission Calculation

When calculating commissions:

```php
// Use updated commission rates
$rate = ReferralCommission::getCommissionRate($level);
$levelName = ReferralCommission::getLevelName($level);
$commission = $subscriptionAmount * ($rate / 100);
```

### 3. Dashboard Display

Show user's professional level:

```php
$position = MatrixPosition::where('user_id', $user->id)->first();
$levelName = $position->level_name; // "Senior", "Manager", etc.
$networkStats = $matrixService->getNetworkStatistics($user);
```

---

## Migration Steps

### 1. Run Migration

```bash
php artisan migrate
```

This will:
- Add `professional_level_name` column
- Add performance indexes
- Backfill existing records with level names

### 2. Update Existing Code

Update any code that references:
- Commission rates (now 7 levels instead of 5)
- Level names (use professional names)
- Network capacity calculations

### 3. Test Matrix Operations

```bash
php artisan test --filter=Matrix
```

---

## Performance Considerations

### Indexes Added

1. **Composite Index**: `(level, is_active)`
   - Optimizes queries filtering by level and active status
   - Used in statistics and overview queries

2. **Single Index**: `professional_level_name`
   - Enables fast lookups by level name
   - Supports the `scopeByProfessionalLevel` query

### Query Optimization

- Breadth-first search for spillover placement
- Recursive tree building with depth limits
- Cached network statistics (recommended)
- Batch updates for level name backfill

---

## Backward Compatibility

### Breaking Changes

1. **Commission Rates**: Changed from 5 to 7 levels
   - Old code expecting 5 levels will need updates
   - Total commission percentage increased from 25% to 48%

2. **Level References**: Numeric levels now have names
   - Update UI to show professional names
   - Update reports to include level names

### Non-Breaking Changes

1. **Matrix Structure**: Still 3x3 (no change)
2. **Placement Logic**: Enhanced but compatible
3. **Database Schema**: Additive only (no columns removed)

---

## Testing Checklist

- [ ] User placement in matrix
- [ ] Spillover functionality
- [ ] Network tree generation
- [ ] Statistics calculation
- [ ] Commission rate retrieval
- [ ] Level name display
- [ ] Maximum depth enforcement
- [ ] Performance with large networks
- [ ] Migration rollback
- [ ] Existing data integrity

---

## Future Enhancements

### Potential Additions

1. **Matrix Compression**: Automatically compress inactive branches
2. **Level Achievements**: Trigger events when reaching new levels
3. **Network Visualization**: Interactive tree visualization
4. **Spillover Preferences**: Allow users to set spillover preferences
5. **Matrix Analytics**: Advanced analytics and predictions
6. **Level Badges**: Visual badges for each professional level
7. **Network Alerts**: Notifications for network milestones

---

## Support

For questions or issues related to the matrix system update:

1. Check the code documentation in the updated files
2. Review the examples in this document
3. Test with the MatrixService methods
4. Check logs for placement errors

---

**Document Prepared By**: MyGrowNet Development Team  
**Last Updated**: October 17, 2025  
**Version**: 2.0 - 7-Level Professional Progression System
