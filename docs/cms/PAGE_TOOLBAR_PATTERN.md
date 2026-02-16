# Page Toolbar Pattern

**Last Updated:** February 16, 2026  
**Status:** Implemented - Ready for Adoption

---

## Overview

The Page Toolbar is a sticky header component that provides quick access to filters, actions, and tools on every page. Inspired by modern SaaS applications like Surge, it improves workflow efficiency by keeping important actions always visible.

## Benefits

### 1. Always Visible Actions
- Toolbar stays at the top when scrolling
- No need to scroll back up to access filters or create buttons
- Reduces clicks and improves speed

### 2. Consistent UX
- Same toolbar structure across all pages
- Users learn once, use everywhere
- Professional, polished appearance

### 3. Better Mobile Experience
- Responsive design with mobile-specific layout
- Touch-friendly button sizes
- Collapsible filters on small screens

### 4. Power User Friendly
- Quick filters always accessible
- Keyboard shortcuts possible (future enhancement)
- Bulk actions in overflow menu

---

## Component Structure

### PageToolbar Component

Located at: `resources/js/components/CMS/PageToolbar.vue`

**Props:**
- `title` (string, required) - Page title
- `subtitle` (string, optional) - Additional context (e.g., "45 total • K12,500 outstanding")
- `showMoreMenu` (boolean, optional) - Show overflow menu with additional actions

**Slots:**
- `filters` - Quick filters (dropdowns, search, date pickers)
- `actions` - Primary action buttons (Export, Create New)
- `menu` - Overflow menu items (bulk actions, import, etc.)
- `mobile-filters` - Mobile-specific filter layout

---

## Usage Example

### Basic Implementation

```vue
<script setup lang="ts">
import PageToolbar from '@/components/CMS/PageToolbar.vue'
import { MenuItem } from '@headlessui/vue'
import { PlusIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline'

const exportData = () => {
  // Export logic
}
</script>

<template>
  <PageToolbar 
    title="Invoices" 
    subtitle="45 total • K12,500 outstanding"
    :show-more-menu="true"
  >
    <!-- Quick Filters -->
    <template #filters>
      <select v-model="statusFilter" class="h-9 px-3 text-sm border rounded-lg">
        <option value="">All Status</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
      </select>

      <input
        v-model="search"
        type="text"
        placeholder="Search..."
        class="h-9 px-3 text-sm border rounded-lg w-64"
      />
    </template>

    <!-- Primary Actions -->
    <template #actions>
      <button
        @click="exportData"
        class="h-9 px-3 text-sm border rounded-lg hover:bg-gray-50"
      >
        <ArrowDownTrayIcon class="h-4 w-4 inline mr-1" />
        Export
      </button>

      <button
        @click="createNew"
        class="h-9 px-3 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        <PlusIcon class="h-4 w-4 inline mr-1" />
        New Invoice
      </button>
    </template>

    <!-- Overflow Menu -->
    <template #menu>
      <MenuItem v-slot="{ active }">
        <button
          @click="bulkSend"
          :class="[active ? 'bg-gray-100' : '', 'flex w-full items-center gap-2 px-4 py-2 text-sm']"
        >
          Bulk Send
        </button>
      </MenuItem>
    </template>
  </PageToolbar>

  <!-- Page content below -->
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Your content here -->
  </div>
</template>
```

---

## Real-World Example

See `resources/js/Pages/CMS/Invoices/IndexWithToolbar.vue` for a complete implementation showing:

- Status filter dropdown
- Date range picker button
- Search input with icon
- Export button
- Create new button
- Overflow menu with bulk actions
- Mobile-responsive filters
- Checkbox selection for bulk operations

---

## Design Guidelines

### Toolbar Height
- Fixed height: `h-16` (64px)
- Consistent across all pages
- Enough space for single-line content

### Button Sizing
- Standard height: `h-9` (36px)
- Padding: `px-3 py-1.5`
- Text size: `text-sm`
- Icons: `h-4 w-4`

### Color Scheme
- Background: White (`bg-white`)
- Border: Gray 200 (`border-gray-200`)
- Primary action: Blue 600 (`bg-blue-600`)
- Secondary action: White with border (`bg-white border-gray-300`)

### Spacing
- Gap between elements: `gap-3` (12px)
- Horizontal padding: `px-4 sm:px-6 lg:px-8` (matches page content)

### Responsive Behavior
- Desktop (lg+): Show all filters inline
- Mobile: Collapse filters below toolbar
- Hide less important text on small screens (`hidden sm:inline`)

---

## When to Use

### ✅ Use Page Toolbar For:
- List/index pages (Invoices, Workers, Customers, Jobs)
- Pages with filters and search
- Pages with create/export actions
- Pages with bulk operations
- Dashboard pages with quick actions

### ❌ Don't Use For:
- Form pages (Create/Edit)
- Detail/show pages (use page header instead)
- Simple pages with no actions
- Modal/slide-over content

---

## Migration Guide

### Step 1: Import Component
```vue
import PageToolbar from '@/components/CMS/PageToolbar.vue'
```

### Step 2: Replace Existing Header
Remove your current page header section and replace with PageToolbar.

**Before:**
```vue
<div class="mb-6 flex items-center justify-between">
  <div>
    <h1 class="text-2xl font-bold">Invoices</h1>
    <p class="text-sm text-gray-600">Manage invoices</p>
  </div>
  <button>Create Invoice</button>
</div>
```

**After:**
```vue
<PageToolbar title="Invoices" subtitle="45 total">
  <template #actions>
    <button>Create Invoice</button>
  </template>
</PageToolbar>
```

### Step 3: Move Filters to Toolbar
Move your filter section into the `#filters` slot.

### Step 4: Add Overflow Menu (Optional)
Add bulk actions and less common operations to the overflow menu.

### Step 5: Test Responsiveness
Check mobile layout and adjust `#mobile-filters` slot if needed.

---

## Keyboard Shortcuts (Future Enhancement)

Potential shortcuts to add:
- `Ctrl/Cmd + K` - Focus search
- `Ctrl/Cmd + N` - Create new
- `Ctrl/Cmd + E` - Export
- `Ctrl/Cmd + /` - Show keyboard shortcuts

---

## Pages to Migrate

### High Priority (Most Used)
1. ✅ Invoices/Index.vue - Example created
2. ⏳ Workers/Index.vue
3. ⏳ Customers/Index.vue
4. ⏳ Jobs/Index.vue
5. ⏳ Payments/Index.vue

### Medium Priority
6. ⏳ Quotations/Index.vue
7. ⏳ Expenses/Index.vue
8. ⏳ Inventory/Index.vue
9. ⏳ Assets/Index.vue
10. ⏳ Leave/Index.vue

### Low Priority
11. ⏳ Departments/Index.vue
12. ⏳ Shifts/Index.vue
13. ⏳ Attendance/Index.vue
14. ⏳ Training/Programs.vue

---

## Comparison: Before vs After

### Before (Current Pattern)
```
┌─────────────────────────────────────────────┐
│ Page Header (scrolls away)                  │
│ - Title                                     │
│ - Create button                             │
└─────────────────────────────────────────────┘
│                                             │
│ Filters Section (scrolls away)              │
│ - Search, dropdowns                         │
│                                             │
└─────────────────────────────────────────────┘
│                                             │
│ Content (table)                             │
│                                             │
│ (User scrolls down)                         │
│                                             │
│ More content...                             │
│                                             │
│ (Filters and create button now hidden)     │
│                                             │
```

### After (With Toolbar)
```
┌─────────────────────────────────────────────┐
│ ⭐ STICKY TOOLBAR (always visible)          │
│ Title | Filters | Search | Export | Create │
└─────────────────────────────────────────────┘
│                                             │
│ Content (table)                             │
│                                             │
│ (User scrolls down)                         │
│                                             │
│ More content...                             │
│                                             │
│ ⭐ Toolbar still visible at top             │
│                                             │
```

---

## Benefits Summary

1. **Faster Workflow** - Actions always accessible, no scrolling needed
2. **Consistent UX** - Same pattern across all pages
3. **Professional Look** - Modern SaaS application feel
4. **Mobile Friendly** - Responsive design with mobile-specific layout
5. **Power User Ready** - Quick filters, bulk actions, keyboard shortcuts (future)
6. **Easy to Maintain** - Single component, consistent styling

---

## Next Steps

1. Review the example implementation in `IndexWithToolbar.vue`
2. Test the toolbar on desktop and mobile
3. Decide which pages to migrate first
4. Create a migration plan
5. Update pages incrementally

---

## Troubleshooting

### Double Headers Issue
**Problem:** Seeing two toolbars/headers stacked on top of each other

**Cause:** The CMSLayoutNew already has a header with breadcrumbs and search. The PageToolbar was initially set to `sticky` which caused visual stacking.

**Solution:** PageToolbar is now non-sticky (`position: relative`) and sits below the layout header. This is intentional - the layout header provides global navigation, while the PageToolbar provides page-specific tools.

### Toolbar Not Showing
**Problem:** Toolbar content not visible

**Solution:** Check that:
- PageToolbar is used inside CMSLayoutNew wrapper
- Slots are properly named (#filters, #actions, #menu)
- Content is provided to at least one slot

---

## Changelog

### February 16, 2026
- Fixed double header issue by removing sticky positioning
- Updated documentation with troubleshooting section
- Clarified relationship between layout header and page toolbar

---

## Questions?

- Check the example: `resources/js/Pages/CMS/Invoices/IndexWithToolbar.vue`
- Review component: `resources/js/components/CMS/PageToolbar.vue`
- Test on your local environment
- Provide feedback for improvements
