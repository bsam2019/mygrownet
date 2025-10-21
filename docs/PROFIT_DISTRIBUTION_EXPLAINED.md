# Community Profit Sharing Distribution Explained

## Overview

The Community Profit Sharing system distributes 60% of quarterly project profits to all active members, with 40% retained by the company.

## Distribution Flow

```
┌─────────────────────────────────────────────────────────────┐
│  STEP 1: Admin Enters Quarterly Project Profit             │
│  Example: K100,000 from community projects                 │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 2: Automatic 60/40 Split                             │
│  • Member Share: K60,000 (60%)                             │
│  • Company Retained: K40,000 (40%)                         │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 3: Identify Active Members                           │
│  Criteria:                                                  │
│  ✓ Active subscription status                              │
│  ✓ Logged in within last 30 days of quarter end           │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│  STEP 4: Calculate Individual Shares                       │
│  Choose distribution method:                                │
│  → BP-Based Distribution                                    │
│  → Level-Based Distribution                                 │
└─────────────────────────────────────────────────────────────┘
```

## Distribution Methods

### Method 1: BP-Based Distribution (Business Points)

**Formula:**
```
Member Share = (Member BP / Total BP) × Member Share Amount
```

**Example:**
- Total Member Share: K60,000
- Total BP Pool: 10,000 BP
- Member A has 500 BP
- Member B has 1,500 BP
- Member C has 8,000 BP

**Calculations:**
```
Member A: (500 / 10,000) × K60,000 = K3,000 (5%)
Member B: (1,500 / 10,000) × K60,000 = K9,000 (15%)
Member C: (8,000 / 10,000) × K60,000 = K48,000 (80%)
```

**Advantages:**
- Rewards active engagement and performance
- Proportional to contribution (BP earned through activities)
- Encourages members to increase their BP

---

### Method 2: Level-Based Distribution (Professional Levels)

**Level Multipliers:**
```
Associate:    1.0x
Professional: 1.2x
Senior:       1.5x
Manager:      2.0x
Director:     2.5x
Executive:    3.0x
Ambassador:   4.0x
```

**Formula:**
```
Member Share = (Level Multiplier / Total Multipliers) × Member Share Amount
```

**Example:**
- Total Member Share: K60,000
- 2 Associates (1.0x each) = 2.0
- 1 Professional (1.2x) = 1.2
- 1 Ambassador (4.0x) = 4.0
- Total Multipliers = 7.2

**Calculations:**
```
Associate 1: (1.0 / 7.2) × K60,000 = K8,333.33
Associate 2: (1.0 / 7.2) × K60,000 = K8,333.33
Professional: (1.2 / 7.2) × K60,000 = K10,000.00
Ambassador: (4.0 / 7.2) × K60,000 = K33,333.33
```

**Advantages:**
- Rewards long-term growth and advancement
- Recognizes leadership and experience
- Encourages members to advance through levels

---

## Complete Workflow

### Phase 1: Creation (Status: Draft → Calculated)

1. **Admin Action:** Creates quarterly profit share
   - Enters: Year, Quarter, Total Profit, Distribution Method
   
2. **System Action:** Automatically:
   - Calculates 60/40 split
   - Identifies all active members
   - Calculates individual shares
   - Creates member profit share records
   - Status: `calculated`

### Phase 2: Approval (Status: Calculated → Approved)

3. **Admin Action:** Reviews and approves distribution
   - Verifies calculations
   - Checks member list
   - Approves distribution
   - Status: `approved`

### Phase 3: Distribution (Status: Approved → Distributed)

4. **Admin Action:** Triggers distribution

5. **System Action:** Automatically:
   - Credits each member's wallet balance
   - Marks individual shares as `paid`
   - Records payment timestamp
   - Status: `distributed`

---

## Real-World Example

**Scenario:** Q1 2025 Community Project Profits

### Input:
- **Total Project Profit:** K500,000
- **Distribution Method:** Level-Based
- **Active Members:** 100 members
  - 50 Associates
  - 30 Professionals
  - 15 Seniors
  - 4 Managers
  - 1 Director

### Calculation:

**Step 1: 60/40 Split**
```
Member Share: K500,000 × 60% = K300,000
Company Retained: K500,000 × 40% = K200,000
```

**Step 2: Calculate Total Multipliers**
```
Associates:    50 × 1.0 = 50.0
Professionals: 30 × 1.2 = 36.0
Seniors:       15 × 1.5 = 22.5
Managers:       4 × 2.0 = 8.0
Director:       1 × 2.5 = 2.5
─────────────────────────────
Total Multipliers = 119.0
```

**Step 3: Calculate Individual Shares**
```
Each Associate:    (1.0 / 119.0) × K300,000 = K2,521.01
Each Professional: (1.2 / 119.0) × K300,000 = K3,025.21
Each Senior:       (1.5 / 119.0) × K300,000 = K3,781.51
Each Manager:      (2.0 / 119.0) × K300,000 = K5,042.02
Director:          (2.5 / 119.0) × K300,000 = K6,302.52
```

**Step 4: Verify Total**
```
50 × K2,521.01 = K126,050.50
30 × K3,025.21 = K90,756.30
15 × K3,781.51 = K56,722.65
4 × K5,042.02  = K20,168.08
1 × K6,302.52  = K6,302.52
─────────────────────────────
Total = K300,000.05 ✓
```

---

## Active Member Criteria

To qualify for profit sharing, members must meet **both** criteria:

1. **Active Subscription**
   - `subscription_status = 'active'`
   - Subscription paid and current

2. **Recent Activity**
   - Logged in within last 30 days of quarter end
   - OR newly registered (no login yet)

**Example:**
- Q1 2025 ends: March 31, 2025
- Activity cutoff: March 1, 2025
- Must have logged in between March 1 - March 31

---

## Database Records

### Quarterly Profit Share Record
```
id: 1
year: 2025
quarter: 1
total_project_profit: 500000.00
member_share_amount: 300000.00
company_retained: 200000.00
total_active_members: 100
distribution_method: 'level_based'
status: 'distributed'
```

### Individual Member Profit Share Records (100 records created)
```
id: 1
quarterly_profit_share_id: 1
user_id: 123
professional_level: 'Associate'
level_multiplier: 1.00
share_amount: 2521.01
status: 'paid'
paid_at: 2025-04-05 10:30:00
```

---

## Key Features

### 1. Immutable Financial Records
- Once distributed, records cannot be modified
- Complete audit trail maintained
- All calculations preserved

### 2. Transparent Calculations
- Members can see their share amount
- Professional level at time of distribution recorded
- BP amount (if BP-based) recorded

### 3. Automatic Wallet Crediting
- No manual payment processing needed
- Instant credit to member wallets
- Members can withdraw or use balance immediately

### 4. Flexible Distribution Methods
- Choose method per quarter
- Can switch between BP-based and Level-based
- Adapts to business strategy

---

## Admin Dashboard Views

### List View
Shows all quarterly distributions with:
- Quarter label (Q1 2025, Q2 2025, etc.)
- Total profit and member share
- Number of active members
- Distribution method
- Status with action buttons

### Create Form
Admin enters:
- Year and Quarter selection
- Total project profit amount
- Distribution method choice
- Optional notes

### Actions Available
- **Calculated Status:** Approve button
- **Approved Status:** Distribute button
- **Distributed Status:** View only (completed)

---

## Member View

Members can view their profit share history showing:
- Date received
- Professional level at time
- Amount received
- Payment status
- Quarter information

---

## Business Rules

1. **One Distribution Per Quarter**
   - Cannot create duplicate for same quarter
   - System validates uniqueness

2. **Sequential Status Flow**
   - Must follow: draft → calculated → approved → distributed
   - Cannot skip steps

3. **Active Members Only**
   - Inactive members excluded automatically
   - Criteria checked at calculation time

4. **60/40 Split Always Applied**
   - Hardcoded business rule
   - Cannot be changed per distribution

5. **Immutable After Distribution**
   - Cannot modify distributed records
   - Ensures financial integrity

---

## Comparison: BP-Based vs Level-Based

| Aspect | BP-Based | Level-Based |
|--------|----------|-------------|
| **Basis** | Business Points earned | Professional level achieved |
| **Rewards** | Recent activity & performance | Long-term growth & advancement |
| **Variability** | High (BP changes monthly) | Low (level changes slowly) |
| **Fairness** | Merit-based | Experience-based |
| **Encourages** | Active engagement | Level progression |
| **Best For** | Performance-driven culture | Stability & loyalty |

---

## Recommendations

### Use BP-Based When:
- Want to reward recent performance
- Encourage active participation
- Have robust BP earning system
- Members understand BP value

### Use Level-Based When:
- Want to reward experience
- Encourage level advancement
- Provide predictable distributions
- Recognize leadership roles

### Hybrid Approach:
Consider alternating methods:
- Q1 & Q3: Level-based (stability)
- Q2 & Q4: BP-based (performance)
