# CMS Form Styling Guide

**Last Updated:** February 11, 2026  
**Status:** Production Ready

## Overview

Professional form styling system for all CMS create/edit pages using reusable components.

## Components Created

### 1. FormInput Component
**Location:** `resources/js/components/CMS/FormInput.vue`

**Features:**
- Text, number, date, textarea support
- Required field indicator (red asterisk)
- Error state with icon
- Help text support
- Disabled state styling
- Consistent focus states
- Shadow and border transitions

**Usage:**
```vue
<FormInput
  v-model="form.name"
  label="Full Name"
  placeholder="Enter name"
  required
  :error="form.errors.name"
  help-text="Optional help text"
/>
```

### 2. FormSelect Component
**Location:** `resources/js/components/CMS/FormSelect.vue`

**Features:**
- Array or object options support
- Placeholder option
- Required field indicator
- Error state with icon
- Help text support
- Disabled state styling

**Usage:**
```vue
<FormSelect
  v-model="form.type"
  label="Type"
  :options="[
    { value: 'option1', label: 'Option 1' },
    { value: 'option2', label: 'Option 2' }
  ]"
  required
  :error="form.errors.type"
/>
```

### 3. FormSection Component
**Location:** `resources/js/components/CMS/FormSection.vue`

**Features:**
- Section title and description
- Optional divider
- 2-column grid layout
- Consistent spacing

**Usage:**
```vue
<FormSection
  title="Personal Information"
  description="Basic details"
  :divider="false"
>
  <FormInput ... />
  <FormInput ... />
</FormSection>
```

## Styling Standards

### Form Container
```vue
<form class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
  <div class="p-6 sm:p-8 space-y-8">
    <!-- Form sections -->
  </div>
  
  <!-- Form Actions -->
  <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
    <!-- Buttons -->
  </div>
</form>
```

### Input Fields
- **Border:** `border-gray-300`
- **Focus:** `focus:border-blue-500 focus:ring-blue-500`
- **Error:** `border-red-300 focus:border-red-500 focus:ring-red-500`
- **Disabled:** `bg-gray-50 text-gray-500 cursor-not-allowed`
- **Rounded:** `rounded-lg`
- **Shadow:** `shadow-sm`
- **Transition:** `transition-colors duration-200`

### Buttons

**Primary (Submit):**
```vue
<button
  type="submit"
  :disabled="form.processing"
  class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
>
  <span v-if="form.processing">Creating...</span>
  <span v-else">Create</span>
</button>
```

**Secondary (Cancel):**
```vue
<button
  type="button"
  class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
>
  Cancel
</button>
```

### Back Button
```vue
<button
  @click="$inertia.visit(route('...'))"
  class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors"
>
  <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
  Back to List
</button>
```

## Pages Updated

### ✅ Workers Create
- **File:** `resources/js/Pages/CMS/Workers/Create.vue`
- **Sections:** Personal Info, Employment Details, Payment Info, Notes
- **Status:** Complete

### ✅ Assets Create
- **File:** `resources/js/Pages/CMS/Assets/Create.vue`
- **Sections:** Basic Info, Asset Details, Purchase Info, Notes
- **Status:** Complete

### ✅ Inventory Create
- **File:** `resources/js/Pages/CMS/Inventory/Create.vue`
- **Sections:** Basic Info, Item Details, Pricing, Stock Management
- **Status:** Complete

### ✅ Inventory Edit
- **File:** `resources/js/Pages/CMS/Inventory/Edit.vue`
- **Sections:** Basic Info, Item Details, Pricing, Stock Management, Status
- **Status:** Complete

### ✅ Customers Create
- **File:** `resources/js/Pages/CMS/Customers/Create.vue`
- **Sections:** Basic Info, Credit Settings, Additional Notes
- **Status:** Complete

### ✅ Jobs Create
- **File:** `resources/js/Pages/CMS/Jobs/Create.vue`
- **Sections:** Customer, Job Details, Pricing & Schedule, Additional Notes
- **Status:** Complete

### ✅ Payroll Create
- **File:** `resources/js/Pages/CMS/Payroll/Create.vue`
- **Sections:** Period Settings, Additional Notes
- **Status:** Complete

### ✅ Invoices Create
- **File:** `resources/js/Pages/CMS/Invoices/Create.vue`
- **Sections:** Customer Info, Invoice Items (dynamic), Additional Info
- **Status:** Complete
- **Note:** Complex form with dynamic line items

### ✅ Quotations Create
- **File:** `resources/js/Pages/CMS/Quotations/Create.vue`
- **Sections:** Quotation Details, Line Items (dynamic with tax/discount), Additional Info
- **Status:** Complete
- **Note:** Complex form with dynamic line items and calculations

## Summary

**All 9 CMS create/edit pages have been updated with professional styling (100% complete)**

## Implementation Pattern

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'
import FormInput from '@/components/CMS/FormInput.vue'
import FormSelect from '@/components/CMS/FormSelect.vue'
import FormSection from '@/components/CMS/FormSection.vue'

defineOptions({
  layout: CMSLayoutNew
})

const form = useForm({
  // form fields
})

const submit = () => {
  form.post(route('...'))
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header with back button -->
      <div class="mb-6">
        <button class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-3 transition-colors">
          <ArrowLeftIcon class="h-4 w-4 mr-1" />
          Back to List
        </button>
        <h1 class="text-2xl font-bold text-gray-900">Page Title</h1>
        <p class="mt-1 text-sm text-gray-500">Description</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8 space-y-8">
          <FormSection title="Section 1" :divider="false">
            <FormInput ... />
          </FormSection>
          
          <FormSection title="Section 2">
            <FormInput ... />
          </FormSection>
        </div>

        <!-- Actions -->
        <div class="bg-gray-50 px-6 sm:px-8 py-4 flex items-center justify-end gap-3 border-t border-gray-200">
          <button type="button">Cancel</button>
          <button type="submit">Submit</button>
        </div>
      </form>
    </div>
  </div>
</template>
```

## Benefits

1. **Consistency** - All forms look and behave the same
2. **Maintainability** - Update styling in one place
3. **Accessibility** - Proper labels, error states, focus management
4. **User Experience** - Clear visual hierarchy, helpful error messages
5. **Responsive** - Works on all screen sizes
6. **Professional** - Modern, clean design

## Next Steps

1. Update remaining create/edit pages
2. Add form validation feedback
3. Consider adding inline validation
4. Add loading states for async operations
5. Add success/error toast notifications

## Changelog

### February 10, 2026
- Created FormInput, FormSelect, FormSection components
- Updated Workers Create page
- Updated Assets Create page
- Updated Inventory Create page
- Updated Inventory Edit page
- Updated Customers Create page
- Updated Jobs Create page
- Updated Payroll Create page
- Updated Invoices Create page (complex form with dynamic items)
- Updated Quotations Create page (complex form with calculations)
- Established styling standards
- **All 9 pages complete (100% done)**


## Consistent Input Styling

### Standard Input Classes

All inputs across the CMS now use consistent styling:

```vue
<!-- Base styling for all inputs -->
<input
  class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
/>
```

### Color Palette

**Default State:**
- Background: `bg-gray-50` (#F9FAFB)
- Border: `border-gray-300` (#D1D5DB)
- Text: `text-gray-900` (#111827)
- Placeholder: `placeholder-gray-500` (#6B7280)

**Focus State:**
- Background: `bg-white` (#FFFFFF)
- Border: `border-transparent` (hidden)
- Ring: `ring-2 ring-blue-500` (#3B82F6)

**Error State:**
- Background: `bg-red-50` (#FEF2F2)
- Border: `border-red-300` (#FCA5A5)
- Text: `text-red-900` (#7F1D1D)
- Placeholder: `placeholder-red-300` (#FCA5A5)

**Disabled State:**
- Background: `bg-gray-100` (#F3F4F6)
- Border: `border-gray-300` (#D1D5DB)
- Text: `text-gray-500` (#6B7280)
- Cursor: `cursor-not-allowed`

### Applied To

This consistent styling is now applied to:
- ✅ Login page (`/cms/login`)
- ✅ Register page (`/cms/register`)
- ✅ FormInput component
- ✅ FormSelect component
- ✅ All slide-in forms
- ✅ All create/edit pages

## Changelog

### February 11, 2026
- Updated to use consistent `border-gray-300` across all inputs
- Changed from `border-2 border-gray-400` to `border border-gray-300`
- Applied to FormInput, FormSelect, and all auth pages
- Improved focus states with `border-transparent` and ring
- Added `bg-gray-50` default background for better visibility
- Login page redesigned to match register page styling


## Index Pages Updated

All CMS index pages now have consistent input styling:

### Pages Updated:
- ✅ Jobs Index (`/cms/jobs`) - Status filter
- ✅ Customers Index (`/cms/customers`) - Search + Status filter
- ✅ Invoices Index (`/cms/invoices`) - Search + Status filter
- ✅ Payments Index (`/cms/payments`) - Search + Customer filter
- ✅ Expenses Index (`/cms/expenses`) - Search + Category + Status filters
- ✅ Quotations Index (`/cms/quotations`) - Search + Status filter

### Search Box Pattern:
```vue
<div class="relative">
  <MagnifyingGlassIcon class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
  <input
    type="text"
    placeholder="Search..."
    class="w-full px-4 py-2.5 pl-10 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
  />
</div>
```

### Select/Dropdown Pattern:
```vue
<select
  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
>
  <option>Option 1</option>
</select>
```

## Header Components Updated

### Header Search and Date Fields
- ✅ Updated header search input with consistent styling
- Applied same `bg-gray-50 border border-gray-300` pattern
- Consistent focus states across all header inputs

### Notification Center
- ✅ Notification dropdown in header with unread count badge
- ✅ Recent notifications list (5 most recent)
- ✅ "View all" link to notifications page
- ✅ Mark as read functionality
- ✅ Time formatting (relative time display)
- ✅ Full notifications page at `/cms/notifications`
- ✅ Filter by type and read status
- ✅ Mark all as read functionality
- ✅ Delete individual notifications

### User Profile Dropdown
- ✅ User name and email display
- ✅ Profile Settings link
- ✅ Company Settings link
- ✅ Sign Out button with red styling
- ✅ Proper icon usage (UserCircleIcon, Cog6ToothIcon, ArrowRightOnRectangleIcon)

### Files Created:
- `resources/js/Pages/CMS/Notifications/Index.vue` - Full notifications page
- `app/Http/Controllers/CMS/NotificationController.php` - Notification controller
- Routes added to `routes/cms.php`:
  - GET `/cms/notifications` - Index page
  - GET `/cms/notifications/recent` - Recent notifications API
  - POST `/cms/notifications/{id}/mark-read` - Mark as read
  - POST `/cms/notifications/mark-all-read` - Mark all as read
  - DELETE `/cms/notifications/{id}` - Delete notification

### Notification Features:
- Type-based icons and colors (payment, invoice, job, inventory, expense, customer, system)
- Unread indicator (blue dot)
- Relative time formatting
- Filter by type and read status
- Bulk actions (mark all as read)
- Individual actions (mark as read, delete)
- Empty state handling

