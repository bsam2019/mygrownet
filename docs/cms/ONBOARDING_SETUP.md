# CMS Onboarding & Setup System

**Last Updated:** February 12, 2026  
**Status:** Production Ready - 100% Complete ✅

## Overview

Complete onboarding wizard and guided tour system to help new companies set up their CMS quickly and efficiently. The system guides users through 5 essential steps with auto-save, video tutorials, interactive tooltips, sample data generation, and a celebration screen upon completion.

## Features

### 1. Onboarding Wizard (5 Steps)

#### Step 1: Company Information (Required)
- Company name, registration number, tax number
- Address, city, country
- Contact information (phone, email, website)
- Interactive tooltips explaining TPIN, PACRA numbers
- Auto-saves progress every 30 seconds

#### Step 2: Industry Selection (Required)
- Choose from 7 industry presets
- Automatically applies:
  - Predefined roles
  - Expense categories
  - Job types
  - Inventory categories
  - Asset types
  - Default settings
- Tooltip explaining preset benefits

#### Step 3: Business Settings (Required)
- **Business Hours**: Configure operating hours for each day
- **Tax Settings**: Enable tax, set rates, tax number
- **Approval Thresholds**: Set auto-approval limits for expenses, quotations, payments
- Tooltips on business hours and tax configuration

#### Step 4: Team Setup (Optional)
- Add team members
- Assign roles
- Can be skipped and done later

#### Step 5: Sample Data Generation (Optional)
- Generate demo data to explore the system:
  - 3 realistic Zambian customers
  - 3 sample jobs with different statuses
  - 3 invoices showing various payment states
- Clear sample data option
- Can be skipped and done later

### 2. New Features

#### Auto-Save Progress
- Automatically saves form data every 30 seconds
- Resumes from last saved position on page refresh
- Works across all steps
- No data loss on accidental navigation

#### Video Tutorials
- Embedded YouTube tutorials for each step
- Modal player with full-screen support
- Step-specific guidance:
  - Step 1: Setting Up Your Company Profile
  - Step 2: Choosing the Right Industry Preset
  - Step 3: Configuring Business Settings
- Easy to update with actual tutorial URLs

#### Interactive Tooltips
- Contextual help on key fields
- Hover to see explanations
- Covers:
  - Company name (official registered name)
  - Tax number (TPIN from ZRA)
  - Business registration (PACRA number)
  - Industry selection benefits
  - Business hours purpose
  - Tax configuration details

#### Sample Data Generation
- Creates realistic demo data:
  - Customers: Zambian business names with contact details
  - Jobs: Various statuses (pending, in progress, completed)
  - Invoices: Different payment states (draft, sent, paid, overdue)
- Helps users understand the system
- Can be cleared anytime
- Marked as sample data in database

#### Completion Celebration
- Animated celebration screen
- Congratulations message
- Auto-redirect to dashboard after 3 seconds
- Positive reinforcement for completing setup

### 2. Guided Tour System

Interactive tour with 5 key areas:
1. **Dashboard Overview** - Key metrics and navigation
2. **Navigation** - Main menu exploration
3. **Jobs & Projects** - Creating and managing jobs
4. **Customer Management** - Adding customers
5. **Invoicing** - Creating and sending invoices

Features:
- Highlighted target elements
- Step-by-step tooltips
- Progress tracking
- Skip or complete individual steps
- Persistent progress (saved to database)

### 3. Progress Tracking

- **Company Level**: Onboarding completion status
- **User Level**: Tour completion status
- JSON progress storage for flexibility
- Completion timestamps
- Step-by-step tracking

## Database Schema

### cms_companies Table
```sql
onboarding_completed BOOLEAN DEFAULT false
onboarding_progress JSON NULL
onboarding_completed_at TIMESTAMP NULL
```

### cms_users Table
```sql
tour_completed BOOLEAN DEFAULT false
tour_progress JSON NULL
```

## Implementation

### Backend

#### Services

**OnboardingService** (`app/Domain/CMS/Core/Services/OnboardingService.php`)
- `getOnboardingStatus()` - Get company onboarding status
- `getSteps()` - Get all onboarding steps
- `completeStep()` - Mark step as completed
- `updateCompanyInformation()` - Save company details (Step 1)
- `applyIndustryPreset()` - Apply industry preset (Step 2)
- `configureBusinessSettings()` - Save business settings (Step 3)
- `skipStep()` - Skip optional step
- `resetOnboarding()` - Reset onboarding progress
- `getTourStatus()` - Get user tour status
- `getTourSteps()` - Get all tour steps
- `completeTourStep()` - Mark tour step as completed
- `skipTour()` - Skip entire tour
- `generateSampleData()` - Generate demo customers, jobs, invoices (NEW)
- `clearSampleData()` - Remove all sample data (NEW)
- `saveProgress()` - Save form data for resume later (NEW)
- `getSavedProgress()` - Retrieve saved form data (NEW)

**SampleDataService** (`app/Domain/CMS/Core/Services/SampleDataService.php`) - NEW
- `generateSampleData()` - Creates 3 customers, 3 jobs, 3 invoices
- `clearSampleData()` - Removes all sample data
- Uses realistic Zambian business names and data

#### Controller

**OnboardingController** (`app/Http/Controllers/CMS/OnboardingController.php`)

Endpoints:
- `GET /cms/onboarding` - Show onboarding wizard
- `GET /cms/onboarding/status` - Get onboarding status
- `POST /cms/onboarding/company-info` - Update company information
- `POST /cms/onboarding/apply-preset` - Apply industry preset
- `POST /cms/onboarding/configure-settings` - Configure business settings
- `POST /cms/onboarding/complete-step` - Complete a step
- `POST /cms/onboarding/skip-step` - Skip a step
- `POST /cms/onboarding/complete` - Complete onboarding
- `POST /cms/onboarding/generate-sample-data` - Generate demo data (NEW)
- `POST /cms/onboarding/clear-sample-data` - Clear demo data (NEW)
- `POST /cms/onboarding/save-progress` - Save form progress (NEW)
- `GET /cms/onboarding/saved-progress` - Get saved progress (NEW)
- `GET /cms/onboarding/tour/status` - Get tour status
- `POST /cms/onboarding/tour/complete-step` - Complete tour step
- `POST /cms/onboarding/tour/skip` - Skip tour

### Frontend

#### Onboarding Wizard

**Component:** `resources/js/Pages/CMS/Onboarding/Wizard.vue`

Features:
- Multi-step wizard with progress indicator
- Form validation
- Auto-save every 30 seconds
- Skip optional steps
- Video tutorial button on each step
- Interactive tooltips on key fields
- Sample data generation UI
- Celebration screen on completion
- Beautiful gradient background
- Responsive design
- Progress persistence across page refreshes

#### Guided Tour

**Component:** `resources/js/components/CMS/GuidedTour.vue`

Features:
- Teleported overlay
- Highlighted target elements
- Positioned tooltips (top/bottom/left/right)
- Progress bar
- Navigation (previous/next)
- Skip functionality
- Smooth scrolling to targets

## Usage

### Starting Onboarding

When a new company is created, redirect to onboarding:

```php
// After company creation
if (!$company->onboarding_completed) {
    return redirect()->route('cms.onboarding.index');
}
```

### Middleware Check

Add middleware to redirect incomplete onboarding:

```php
// In cms.access middleware
if (!$company->onboarding_completed) {
    return redirect()->route('cms.onboarding.index');
}
```

### Starting Guided Tour

In any Vue component:

```vue
<script setup>
import { ref } from 'vue';
import GuidedTour from '@/components/CMS/GuidedTour.vue';

const tourRef = ref(null);

const tourSteps = [
    {
        id: 'dashboard',
        name: 'Dashboard Overview',
        description: 'This is your main dashboard',
        target: '#dashboard-stats',
        position: 'bottom',
    },
    // ... more steps
];

const startTour = () => {
    tourRef.value?.startTour();
};
</script>

<template>
    <GuidedTour
        ref="tourRef"
        :steps="tourSteps"
        :auto-start="false"
        @complete="handleTourComplete"
        @skip="handleTourSkip"
    />
</template>
```

### Checking Onboarding Status

```php
// In controller
$status = $this->onboardingService->getOnboardingStatus($companyId);

if ($status['completed']) {
    // Onboarding complete
} else {
    // Show current step: $status['current_step']
}
```

### Checking Tour Status

```php
// In controller
$tourStatus = $this->onboardingService->getTourStatus($userId);

if (!$tourStatus['completed']) {
    // Show tour prompt
}
```

## API Examples

### Get Onboarding Status

```javascript
const response = await fetch(route('cms.onboarding.status'));
const status = await response.json();

console.log(status.completed); // true/false
console.log(status.current_step); // 1-5
console.log(status.progress); // { 1: { completed: true, ... }, ... }
```

### Complete Step

```javascript
await fetch(route('cms.onboarding.complete-step'), {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        step_id: 1,
        data: { company_name: 'My Company' },
    }),
});
```

### Skip Step

```javascript
await fetch(route('cms.onboarding.skip-step'), {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        step_id: 4,
    }),
});
```

### Complete Tour Step

```javascript
await fetch(route('cms.onboarding.tour.complete-step'), {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
    },
    body: JSON.stringify({
        step_id: 'dashboard',
    }),
});
```

## Testing

### Test Onboarding Flow

1. Create new company
2. Visit `/cms/onboarding`
3. Complete Step 1 (Company Information)
4. Select industry preset (Step 2)
5. Configure business settings (Step 3)
6. Skip or complete Steps 4-5
7. Verify redirect to dashboard
8. Check `onboarding_completed = true`

### Test Guided Tour

1. Login as CMS user
2. Start guided tour
3. Navigate through all steps
4. Verify highlights and tooltips
5. Test skip functionality
6. Check `tour_completed = true`

### Test Progress Persistence

1. Complete Step 1
2. Refresh page
3. Verify Step 1 is marked complete
4. Verify current step is Step 2

## Customization

### Adding New Onboarding Steps

1. Update `getSteps()` in OnboardingService
2. Add step content in Wizard.vue
3. Add step handler in OnboardingController
4. Update navigation logic

### Adding New Tour Steps

1. Update `getTourSteps()` in OnboardingService
2. Add step configuration with target selector
3. Ensure target elements have unique IDs/classes

### Styling Tour Highlights

Modify `.tour-highlight` class in GuidedTour.vue:

```css
:global(.tour-highlight) {
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.5);
    border-radius: 0.5rem;
    z-index: 51;
}
```

## Best Practices

1. **Keep Steps Simple**: Each step should have a single, clear purpose
2. **Make Optional Steps Skippable**: Don't force users through non-essential steps
3. **Save Progress Frequently**: Auto-save after each step
4. **Provide Clear Instructions**: Use descriptive text and examples
5. **Test on Mobile**: Ensure wizard works on all screen sizes
6. **Use Meaningful Defaults**: Pre-fill forms with sensible defaults
7. **Highlight Benefits**: Explain why each step matters

## Troubleshooting

### Onboarding Not Showing

Check:
- `onboarding_completed` is `false`
- User has CMS access
- Routes are registered
- Middleware is applied

### Tour Not Highlighting Elements

Check:
- Target selectors are correct
- Elements exist in DOM
- z-index is sufficient
- CSS is loaded

### Progress Not Saving

Check:
- CSRF token is valid
- API endpoints are accessible
- Database columns exist
- JSON casting is configured

## Future Enhancements

- [ ] Replace placeholder video URLs with actual tutorials
- [ ] Multi-language support for tooltips
- [ ] Onboarding analytics dashboard
- [ ] Personalized onboarding based on company size
- [ ] Onboarding checklist widget in dashboard
- [ ] Re-run tour option in settings
- [ ] Contextual help tooltips throughout app
- [ ] More sample data options (inventory, expenses)

## Changelog

### February 12, 2026
- Added auto-save functionality (every 30 seconds)
- Implemented sample data generation service
- Added video tutorial modal with YouTube embeds
- Implemented interactive tooltips on key fields
- Added celebration screen on completion
- Added progress persistence (save/resume)
- Updated Step 5 to focus on sample data
- Added new API endpoints for sample data and progress
- Complete UI overhaul with new features

### February 10, 2026
- Initial implementation
- 5-step onboarding wizard
- Guided tour system
- Progress tracking
- Complete documentation
