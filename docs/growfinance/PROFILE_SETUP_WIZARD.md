# GrowFinance Profile Setup Wizard

**Last Updated:** December 3, 2025
**Status:** Implemented ✅
**Module:** GrowFinance

## Overview

A guided onboarding wizard that helps new users set up their financial profile when they first access GrowFinance. The wizard collects essential information needed for personalized budgeting, savings goals, and financial insights.

## User Flow

### Step 1: Welcome & Introduction
- Brief explanation of GrowFinance benefits
- What data will be collected and why
- Privacy assurance (data is private and secure)
- Option to skip (can complete later)

### Step 2: Income Setup
**Purpose:** Establish primary income sources for budget calculations

**Fields:**
- Primary income source (Salary/Business/Freelance/Other)
- Monthly income amount (in Kwacha)
- Income frequency (Monthly/Bi-weekly/Weekly)
- Additional income sources (optional, can add multiple)
- Next expected income date

**Validation:**
- Income amount must be positive
- At least one income source required

### Step 3: Expense Categories
**Purpose:** Identify user's main spending categories

**Pre-populated categories with customization:**
- Essential: Rent, Utilities, Food, Transport, Healthcare
- Lifestyle: Entertainment, Dining Out, Shopping
- Financial: Savings, Debt Payments, Investments
- Other: Custom categories

**User actions:**
- Enable/disable categories relevant to them
- Set estimated monthly amount for each
- Add custom categories
- Mark categories as "fixed" or "variable"

### Step 4: Financial Goals
**Purpose:** Set initial savings targets

**Quick goal templates:**
- Emergency Fund (3-6 months expenses)
- Specific Purchase (phone, laptop, etc.)
- Education/Training
- Business Capital
- Custom Goal

**For each goal:**
- Goal name
- Target amount
- Target date (optional)
- Priority (High/Medium/Low)

**Can skip:** Users can set goals later

### Step 5: Budget Preferences
**Purpose:** Configure budget settings

**Options:**
- Budget period (Monthly/Bi-weekly/Weekly)
- Budget method:
  - 50/30/20 Rule (50% needs, 30% wants, 20% savings)
  - Zero-based budgeting
  - Custom percentages
- Alert preferences:
  - Notify when 80% of category budget spent
  - Notify when over budget
  - Weekly spending summary
- Currency display (ZMW default)

### Step 6: Review & Confirm
**Purpose:** Summary of setup before completion

**Display:**
- Monthly income total
- Active expense categories
- Initial budget allocation
- Savings goals (if set)
- Estimated monthly surplus/deficit

**Actions:**
- Edit any section
- Confirm and start using GrowFinance
- Save as draft (complete later)

## Technical Implementation

### Database Schema

```sql
-- User financial profile
CREATE TABLE user_financial_profiles (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL UNIQUE,
    setup_completed BOOLEAN DEFAULT FALSE,
    setup_completed_at TIMESTAMP NULL,
    budget_method VARCHAR(50) DEFAULT '50/30/20',
    budget_period VARCHAR(20) DEFAULT 'monthly',
    currency VARCHAR(3) DEFAULT 'ZMW',
    alert_preferences JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Income sources
CREATE TABLE income_sources (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    source_type VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    frequency VARCHAR(20) NOT NULL,
    next_expected_date DATE NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User expense categories (personalized)
CREATE TABLE user_expense_categories (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    category_type VARCHAR(50) NOT NULL, -- essential, lifestyle, financial, other
    estimated_monthly_amount DECIMAL(15,2) DEFAULT 0,
    is_fixed BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Savings goals
CREATE TABLE savings_goals (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    goal_name VARCHAR(200) NOT NULL,
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) DEFAULT 0,
    target_date DATE NULL,
    priority VARCHAR(20) DEFAULT 'medium',
    status VARCHAR(20) DEFAULT 'active', -- active, completed, paused
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Routes

```php
// Wizard routes
Route::middleware(['auth'])->prefix('growfinance/setup')->group(function () {
    Route::get('/', [FinanceSetupController::class, 'index'])->name('growfinance.setup.index');
    Route::post('/income', [FinanceSetupController::class, 'saveIncome'])->name('growfinance.setup.income');
    Route::post('/categories', [FinanceSetupController::class, 'saveCategories'])->name('growfinance.setup.categories');
    Route::post('/goals', [FinanceSetupController::class, 'saveGoals'])->name('growfinance.setup.goals');
    Route::post('/preferences', [FinanceSetupController::class, 'savePreferences'])->name('growfinance.setup.preferences');
    Route::post('/complete', [FinanceSetupController::class, 'complete'])->name('growfinance.setup.complete');
    Route::get('/skip', [FinanceSetupController::class, 'skip'])->name('growfinance.setup.skip');
});
```

### Vue Components

```
resources/js/Pages/GrowFinance/Setup/
├── SetupWizard.vue              # Main wizard container
├── Steps/
│   ├── WelcomeStep.vue          # Step 1
│   ├── IncomeStep.vue           # Step 2
│   ├── CategoriesStep.vue       # Step 3
│   ├── GoalsStep.vue            # Step 4
│   ├── PreferencesStep.vue      # Step 5
│   └── ReviewStep.vue           # Step 6
└── Components/
    ├── WizardProgress.vue       # Progress indicator
    ├── IncomeSourceForm.vue     # Add/edit income
    ├── CategorySelector.vue     # Category selection
    └── GoalForm.vue             # Goal creation
```

### Middleware Check

```php
// Check if setup is required
Route::middleware(['auth', 'finance.setup.check'])->group(function () {
    // All GrowFinance routes
});

// Middleware
class CheckFinanceSetup
{
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        
        // Skip check for setup routes
        if ($request->routeIs('growfinance.setup.*')) {
            return $next($request);
        }
        
        // Check if profile setup is complete
        $profile = $user->financialProfile;
        
        if (!$profile || !$profile->setup_completed) {
            return redirect()->route('growfinance.setup.index');
        }
        
        return $next($request);
    }
}
```

## UI/UX Design

### Visual Style
- **Layout:** Centered card with progress indicator at top
- **Colors:** Primary blue for progress, green for success states
- **Typography:** Clear headings, helpful descriptions for each field
- **Spacing:** Generous padding, not cramped

### Progress Indicator
```
[●]━━━[○]━━━[○]━━━[○]━━━[○]━━━[○]
Welcome  Income  Categories  Goals  Preferences  Review
```

### Navigation
- "Next" button (primary, right-aligned)
- "Back" button (secondary, left-aligned)
- "Skip for now" link (subtle, top-right)
- "Save as draft" option on each step

### Responsive Design
- Mobile: Single column, full-width inputs
- Tablet/Desktop: Centered card (max-width: 800px)
- Touch-friendly buttons and inputs

## Validation Rules

### Income Step
- At least one income source required
- Amount must be positive number
- Frequency must be selected
- If multiple sources, one must be marked as primary

### Categories Step
- At least 3 categories must be enabled
- Estimated amounts must be non-negative
- Custom category names must be unique

### Goals Step
- Optional step, can skip
- If adding goal: name and target amount required
- Target date must be in future
- Target amount must be positive

### Preferences Step
- Budget method must be selected
- Alert preferences are optional

## Default Data

### Pre-populated Categories
```json
{
  "essential": [
    {"name": "Rent/Mortgage", "fixed": true},
    {"name": "Utilities", "fixed": false},
    {"name": "Groceries", "fixed": false},
    {"name": "Transport", "fixed": false},
    {"name": "Healthcare", "fixed": false}
  ],
  "lifestyle": [
    {"name": "Entertainment", "fixed": false},
    {"name": "Dining Out", "fixed": false},
    {"name": "Shopping", "fixed": false},
    {"name": "Personal Care", "fixed": false}
  ],
  "financial": [
    {"name": "Savings", "fixed": false},
    {"name": "Debt Payments", "fixed": true},
    {"name": "Insurance", "fixed": true}
  ]
}
```

### Budget Method Templates
```json
{
  "50/30/20": {
    "needs": 50,
    "wants": 30,
    "savings": 20
  },
  "zero_based": {
    "description": "Allocate every kwacha to a category"
  },
  "custom": {
    "description": "Set your own percentages"
  }
}
```

## Post-Setup Experience

### Dashboard Redirect
After completing setup, redirect to GrowFinance dashboard with:
- Welcome message
- Quick tour of features (optional)
- First action suggestions:
  - "Record your first expense"
  - "Review your budget"
  - "Track progress on goals"

### Edit Profile Later
- Settings page with "Edit Financial Profile" option
- Can modify any setup data
- Re-run wizard option available

## Analytics & Tracking

Track wizard completion metrics:
- Step completion rates
- Drop-off points
- Time spent per step
- Skip rate
- Most common categories selected
- Average income ranges
- Goal types popularity

## Accessibility

- Keyboard navigation support
- Screen reader friendly labels
- Clear error messages
- High contrast mode support
- Focus indicators on all interactive elements

## Future Enhancements

1. **Smart Suggestions:** AI-powered budget recommendations based on income
2. **Import Data:** Import from bank statements or other apps
3. **Guided Tour:** Interactive tutorial after setup
4. **Templates:** Pre-built profiles for common situations (student, freelancer, family)
5. **Multi-currency:** Support for users with income in multiple currencies

## Related Documentation

- `GROWFINANCE_MODULE.md` - Main module documentation
- `BUDGET_SYSTEM.md` - Budget management features
- `SAVINGS_GOALS.md` - Savings goal tracking

## Changelog

### December 3, 2025
- Initial specification created
- Defined 6-step wizard flow
- Database schema designed
- Component structure outlined
- **IMPLEMENTED:** Full wizard with all 6 steps
- Created database migration for all tables
- Created Eloquent models (UserFinancialProfileModel, IncomeSourceModel, UserExpenseCategoryModel, SavingsGoalModel)
- Created ProfileSetupService for business logic
- Updated SetupController with wizard endpoints
- Created Vue components:
  - SetupWizard.vue (main container)
  - WizardProgress.vue (progress indicator)
  - WelcomeStep.vue, IncomeStep.vue, CategoriesStep.vue, GoalsStep.vue, PreferencesStep.vue, ReviewStep.vue
  - CategoryItem.vue (reusable component)
- Updated Setup/Index.vue with link to guided wizard
- Added routes for wizard flow
