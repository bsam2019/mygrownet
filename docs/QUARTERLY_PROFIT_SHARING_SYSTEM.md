# Quarterly Profit Sharing System

## Overview

The Quarterly Profit Sharing System implements MyGrowNet's profit distribution model where 60% of project profits are distributed to active members quarterly, with 40% retained by the company.

## Architecture

Built using Domain-Driven Design (DDD) principles with clear separation of concerns:

### Domain Layer (`app/Domain/ProfitSharing/`)
- **Entities**: `QuarterlyProfitShare`, `MemberProfitShare`
- **Value Objects**: `Quarter`, `ProfitAmount`
- **Services**: `ProfitDistributionCalculator`
- **Repositories**: Interfaces for data access

### Application Layer (`app/Application/ProfitSharing/`)
- **Use Cases**: 
  - `CreateQuarterlyProfitShareUseCase` - Creates and calculates profit distribution
  - `ApproveProfitShareUseCase` - Approves calculated distributions
  - `DistributeProfitShareUseCase` - Credits member wallets
- **DTOs**: `CreateProfitShareDTO`

### Infrastructure Layer (`app/Infrastructure/Persistence/`)
- **Eloquent Models**: `QuarterlyProfitShareModel`, `MemberProfitShareModel`
- **Repository Implementations**: Concrete implementations of repository interfaces

### Presentation Layer
- **Admin Controllers**: `Admin\ProfitSharingController`
- **Member Controllers**: `MyGrowNet\ProfitShareController`
- **Vue Pages**: Admin and member interfaces

## Distribution Methods

### 1. BP-Based Distribution
- Distribution proportional to member's Business Points (BP)
- Formula: `Member Share = (Member BP / Total BP) × 60% of Profit`
- Best for rewarding active engagement and performance

### 2. Level-Based Distribution
- Distribution weighted by professional level multipliers
- Multipliers:
  - Associate: 1.0x
  - Professional: 1.2x
  - Senior: 1.5x
  - Manager: 2.0x
  - Director: 2.5x
  - Executive: 3.0x
  - Ambassador: 4.0x
- Formula: `Member Share = (Level Multiplier / Total Multipliers) × 60% of Profit`

## Active Member Criteria

To qualify for profit sharing, members must:
1. Have active subscription status
2. Have logged in within last 30 days of quarter end
3. Be participating in platform activities

## Workflow

### 1. Create Quarterly Profit Share (Admin)
- Admin enters total project profit for the quarter
- Selects distribution method (BP-based or Level-based)
- System automatically:
  - Identifies all active members
  - Calculates 60/40 split
  - Distributes member share based on chosen method
  - Creates individual member profit share records
  - Marks as "calculated"

### 2. Approve Distribution (Admin)
- Admin reviews calculated distribution
- Approves to lock the distribution
- Status changes to "approved"

### 3. Distribute to Wallets (Admin)
- Admin triggers distribution
- System credits each member's wallet balance
- Marks individual shares as "paid"
- Status changes to "distributed"

## Database Schema

### `quarterly_profit_shares` Table
- `id` - Primary key
- `year` - Year of the quarter
- `quarter` - Quarter number (1-4)
- `total_project_profit` - Total profit from projects
- `member_share_amount` - 60% for members
- `company_retained` - 40% for company
- `total_active_members` - Count of qualifying members
- `total_bp_pool` - Total BP (for BP-based distribution)
- `distribution_method` - 'bp_based' or 'level_based'
- `status` - 'draft', 'calculated', 'approved', 'distributed'
- `notes` - Optional notes
- `created_by`, `approved_by` - Admin user IDs
- `approved_at`, `distributed_at` - Timestamps

### `member_profit_shares` Table
- `id` - Primary key
- `quarterly_profit_share_id` - Foreign key
- `user_id` - Member receiving share
- `professional_level` - Member's level at time of distribution
- `level_multiplier` - Multiplier used
- `member_bp` - Member's BP at time of distribution
- `share_amount` - Amount credited to member
- `status` - 'pending' or 'paid'
- `paid_at` - When credited to wallet

## Admin Interface

### List View (`/admin/profit-sharing`)
- View all quarterly profit shares
- See status, amounts, and member counts
- Actions: Approve (for calculated), Distribute (for approved)

### Create Form (`/admin/profit-sharing/create`)
- Select year and quarter
- Enter total project profit
- Choose distribution method
- Add optional notes
- System calculates and creates distribution

## Member Interface

### My Profit Shares (`/mygrownet/profit-shares`)
- View all received profit shares
- See amount, status, and payment date
- Track quarterly earnings from company projects

## Routes

### Admin Routes (requires admin middleware)
```php
GET  /admin/profit-sharing          - List all profit shares
GET  /admin/profit-sharing/create   - Create form
POST /admin/profit-sharing          - Store new profit share
POST /admin/profit-sharing/{id}/approve    - Approve distribution
POST /admin/profit-sharing/{id}/distribute - Credit wallets
```

### Member Routes (requires auth middleware)
```php
GET /mygrownet/profit-shares - View my profit shares
```

## Key Features

1. **Automatic Calculation**: System automatically identifies active members and calculates distributions
2. **Flexible Methods**: Support for both BP-based and level-based distribution
3. **Audit Trail**: Complete tracking of creation, approval, and distribution
4. **Immutable Records**: Once distributed, records cannot be modified
5. **Wallet Integration**: Seamless crediting to member wallet balances
6. **Status Workflow**: Clear progression from draft → calculated → approved → distributed

## Business Rules

1. **60/40 Split**: Always 60% to members, 40% to company
2. **Active Members Only**: Only qualifying active members receive shares
3. **One Per Quarter**: Cannot create duplicate profit shares for same quarter
4. **Sequential Status**: Must follow status progression (cannot skip steps)
5. **Immutable After Distribution**: Cannot modify after distribution is complete

## Integration Points

- **User Model**: Reads subscription status, last login, professional level, BP
- **Wallet System**: Credits member wallet balances on distribution
- **Points System**: Uses BP for BP-based distribution method

## Future Enhancements

- Email notifications when profit shares are distributed
- Detailed breakdown showing calculation methodology
- Historical trends and analytics
- Export functionality for accounting
- Scheduled automatic quarterly creation
- Member projections based on current BP/level

## Testing Checklist

- [ ] Create profit share with BP-based distribution
- [ ] Create profit share with level-based distribution
- [ ] Verify active member identification logic
- [ ] Test approval workflow
- [ ] Test distribution to wallets
- [ ] Verify status transitions
- [ ] Test duplicate quarter prevention
- [ ] Verify 60/40 split calculations
- [ ] Test member view of profit shares
- [ ] Verify audit trail completeness
