# Sidebar Collapsible Sections Implementation

**Last Updated:** April 28, 2026
**Status:** ✅ Complete

## Overview

Implemented collapsible sections in the CMS sidebar to improve navigation and reduce visual clutter. Users can collapse/expand sections, and their preferences are persisted in localStorage.

## Features Implemented

### 1. Collapsible Sections (9 Total)

All navigation items are now organized into collapsible sections:

1. **Core Business** (Blue)
   - Jobs
   - Measurements (if fabrication enabled)
   - Customers
   - Invoices
   - Payments
   - Reports
   - Budgets

2. **Financial Management** (Green)
   - Expenses
   - Quotations
   - Inventory
   - Materials (if fabrication/construction enabled)
   - Assets
   - Payroll
   - Workers

3. **Analytics** (Blue)
   - Operations Analytics
   - Finance Analytics

4. **Construction** (Orange)
   - Projects
   - Subcontractors
   - Equipment
   - Labour Crews
   - Timesheets
   - BOQ
   - Progress Billing

5. **Operations** (Blue)
   - Production
   - Installation
   - Stock Management
   - Fleet
   - Documents
   - Safety
   - Quality

6. **HR Management** (Purple)
   - Departments
   - Leave Management
   - Shifts
   - Attendance
   - Overtime
   - Recruitment
   - Onboarding
   - Performance
   - Training
   - HR Reports

7. **Administration** (Indigo)
   - Time Tracking
   - Recurring Invoices
   - Approvals
   - Chart of Accounts

8. **Payroll Config** (Emerald)
   - Allowance Types
   - Deduction Types

9. **Settings** (Gray)
   - Company Settings
   - Document Templates
   - Pricing Rules
   - Industry Presets
   - Email Settings
   - SMS Settings
   - Currency
   - Security

### 2. State Management

- **localStorage Persistence**: User preferences saved to `cms_sidebar_sections` key
- **Default State**: All sections expanded by default
- **Reactive Updates**: Changes immediately reflected in UI

### 3. Visual Design

- **Chevron Icons**: Rotate -90deg when collapsed
- **Smooth Transitions**: 200ms transition on chevron rotation
- **Color-Coded Headers**: Each section has its own color scheme
- **Hover Effects**: Subtle background color change on hover
- **Gradient Indicators**: Vertical gradient bar on left of each header

### 4. Search Integration

- **Auto-Expand on Search**: Sections automatically expand when user searches
- **Filtered Visibility**: Only sections with matching items are shown during search
- **Preserved State**: Collapsed state maintained when search is cleared

## Technical Implementation

### File Modified
- `resources/js/Layouts/CMSLayout.vue`

### Key Code Sections

#### State Management
```typescript
const STORAGE_KEY = 'cms_sidebar_sections'
const collapsedSections = ref<Record<string, boolean>>({
  business: false,
  financial: false,
  analytics: false,
  construction: false,
  operations: false,
  hr: false,
  administration: false,
  payroll: false,
  settings: false,
})

// Load from localStorage on mount
onMounted(() => {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved) {
    try {
      collapsedSections.value = { ...collapsedSections.value, ...JSON.parse(saved) }
    } catch (e) {
      console.error('Failed to parse sidebar state:', e)
    }
  }
})
```

#### Toggle Function
```typescript
const toggleSection = (section: string) => {
  collapsedSections.value[section] = !collapsedSections.value[section]
  localStorage.setItem(STORAGE_KEY, JSON.stringify(collapsedSections.value))
}
```

#### Section Visibility Helper
```typescript
const isSectionVisible = (sectionRoutes: string[]) => {
  if (!searchQuery.value.trim()) return true
  return sectionRoutes.some(route => shouldShowNavItem(route))
}
```

#### Template Structure (Example)
```vue
<div v-if="isSectionVisible(['cms.analytics.operations', 'cms.analytics.finance'])">
  <button
    v-if="!sidebarCollapsed && !searchQuery"
    @click="toggleSection('analytics')"
    class="w-full px-3 pt-4 mb-2 group"
  >
    <div class="flex items-center justify-between px-2 py-1.5 bg-gradient-to-r from-blue-50 to-transparent rounded-lg hover:from-blue-100 transition-colors">
      <div class="flex items-center gap-2">
        <div class="w-1 h-4 bg-gradient-to-b from-blue-500 to-blue-600 rounded-full"></div>
        <p class="text-xs font-bold text-gray-700 uppercase tracking-wider">Analytics</p>
      </div>
      <ChevronDownIcon 
        :class="['h-4 w-4 text-gray-500 transition-transform duration-200', collapsedSections.analytics ? '-rotate-90' : '']"
        aria-hidden="true"
      />
    </div>
  </button>

  <div v-show="!collapsedSections.analytics || searchQuery" class="space-y-1">
    <!-- Nav items here -->
  </div>
</div>
```

## User Experience

### Default Behavior
- All sections start expanded
- User can click section header to collapse/expand
- Preference is saved and persists across sessions

### Search Behavior
- When user types in search box, all sections auto-expand
- Only sections with matching items are visible
- When search is cleared, sections return to their saved state

### Collapsed Sidebar
- Section headers are hidden when sidebar is collapsed
- Only individual nav items are shown (with icons only)

## Benefits

1. **Reduced Visual Clutter**: Users can hide sections they don't use frequently
2. **Faster Navigation**: Less scrolling required to reach desired items
3. **Personalization**: Each user can customize their sidebar layout
4. **Persistent Preferences**: Settings saved across sessions
5. **Search-Friendly**: Search still works across all items, auto-expanding sections

## Testing Checklist

- [x] All 9 sections can be collapsed/expanded
- [x] State persists after page refresh
- [x] Search auto-expands sections
- [x] Chevron icons rotate correctly
- [x] Hover effects work
- [x] Color coding is consistent
- [x] Works with collapsed sidebar
- [x] localStorage saves/loads correctly
- [x] No duplicate items
- [x] All navigation items are grouped logically

## Future Enhancements (Optional)

- **Favorites/Pinned Items**: Allow users to star frequently used pages
- **Recently Visited**: Show recently accessed pages at the top
- **Keyboard Shortcuts**: Add Ctrl+K for quick search
- **Section Reordering**: Allow users to reorder sections
- **Collapse All/Expand All**: Buttons to quickly collapse or expand all sections

## Notes

- The implementation maintains backward compatibility
- No breaking changes to existing navigation
- Search functionality enhanced, not replaced
- Mobile menu not affected (uses separate component)
