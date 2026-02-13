# CMS SPA Architecture Guide

**Last Updated:** February 8, 2026  
**Status:** Implementation Complete - Testing Required

## Overview

The CMS has been refactored to use a modern Single Page Application (SPA) architecture with slide-over panels for forms, following international standards used by Linear, Notion, and Monday.com.

## Architecture Components

### 1. Persistent Layout Shell
**File:** `resources/js/Layouts/CMSLayoutNew.vue`

The layout never unmounts and provides:
- Collapsible sidebar navigation
- Top header with breadcrumbs
- Slide-over panel system
- Mobile responsive drawer
- User profile menu

### 2. Slide-Over Panel System
**File:** `resources/js/components/CMS/SlideOver.vue`

Reusable slide-over component that:
- Slides in from the right
- Supports multiple sizes (sm, md, lg, xl)
- Has smooth animations
- Manages focus and accessibility
- Closes on backdrop click or ESC key

### 3. Form Components
**Files:**
- `resources/js/components/CMS/Forms/JobForm.vue`
- `resources/js/components/CMS/Forms/CustomerForm.vue`
- `resources/js/components/CMS/Forms/InvoiceForm.vue`

Complete forms with:
- Full field validation
- Error handling
- Success callbacks
- Cancel functionality
- Inertia form submission

### 4. Navigation Component
**File:** `resources/js/components/CMS/NavItem.vue`

Reusable navigation item with:
- Active state highlighting
- Tooltip on collapsed sidebar
- Icon support
- Click handling

### 5. Composable
**File:** `resources/js/composables/useCMSSlideOver.ts`

Manages slide-over state:
- Open/close functionality
- Current form type tracking
- Configuration management
- Smooth transitions

## How It Works

### User Flow

1. **User clicks "Create Job"** on Dashboard
   - `slideOver.open('job')` is called
   - Slide-over panel opens from right
   - JobForm component is rendered inside

2. **User fills form and submits**
   - Form validates inputs
   - Submits via Inertia `useForm()`
   - Shows loading state

3. **On success**
   - Form emits `@success` event
   - Slide-over closes automatically
   - Data refreshes (stats, recent jobs)
   - No page reload occurs

4. **User navigates via sidebar**
   - Click triggers `navigateTo(route)`
   - Uses `router.visit()` with `preserveState: true`
   - Only fetches changed data
   - Layout stays mounted
   - Smooth transition

## Implementation Details

### Page Setup

Pages must use the new layout:

```vue
<script setup lang="ts">
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})

// Get slideOver from layout
const slideOver: any = inject('slideOver')
</script>

<template>
  <div>
    <!-- Page content -->
    <button @click="slideOver?.open('job')">
      Create Job
    </button>
  </div>
</template>
```

### SPA Navigation

```typescript
const navigateTo = (routeName: string) => {
  router.visit(route(routeName), {
    preserveState: true,      // Keep layout mounted
    preserveScroll: false,     // Scroll to top
    only: ['stats', 'data'],   // Only fetch what changed
  })
}
```

### Form Success Handling

```typescript
const handleFormSuccess = () => {
  slideOver.close()
  router.reload({
    only: ['stats', 'recentJobs', 'customers'],
    preserveScroll: true,
  })
}
```

## Files Created/Modified

### New Files
1. `resources/js/Layouts/CMSLayoutNew.vue` - Persistent layout shell
2. `resources/js/components/CMS/SlideOver.vue` - Slide-over panel
3. `resources/js/components/CMS/Forms/JobForm.vue` - Job creation form
4. `resources/js/components/CMS/Forms/CustomerForm.vue` - Customer form
5. `resources/js/components/CMS/Forms/InvoiceForm.vue` - Invoice form
6. `resources/js/components/CMS/NavItem.vue` - Navigation item
7. `resources/js/composables/useCMSSlideOver.ts` - Slide-over state management

### Modified Files
1. `resources/js/Pages/CMS/Dashboard.vue` - Uses new layout, removed modals
2. `app/Http/Controllers/CMS/DashboardController.php` - Passes customers data

## Testing Checklist

### ✅ Layout & Navigation
- [ ] Sidebar appears on desktop
- [ ] Sidebar collapses/expands with hamburger button
- [ ] Tooltips show on collapsed sidebar items
- [ ] Mobile menu opens on mobile devices
- [ ] Navigation works without page reload
- [ ] Active route is highlighted
- [ ] Breadcrumbs update correctly

### ✅ Slide-Over Panel
- [ ] Opens when clicking "Create Job"
- [ ] Opens when clicking "Add Customer"
- [ ] Opens when clicking "Create Invoice"
- [ ] Closes when clicking backdrop
- [ ] Closes when clicking X button
- [ ] Closes when pressing ESC key
- [ ] Smooth slide-in animation
- [ ] Smooth slide-out animation

### ✅ Job Form
- [ ] Customer dropdown populates
- [ ] All fields are editable
- [ ] Validation works (required fields)
- [ ] Error messages display correctly
- [ ] Submit button shows loading state
- [ ] Success closes slide-over
- [ ] Dashboard data refreshes
- [ ] No page reload occurs

### ✅ Customer Form
- [ ] Name field works
- [ ] Phone field works
- [ ] Email field works
- [ ] Validation works
- [ ] Submit creates customer
- [ ] Success closes slide-over
- [ ] Customer list updates

### ✅ Invoice Form
- [ ] Customer dropdown works
- [ ] Due date picker works
- [ ] Line item fields work
- [ ] Total calculates correctly
- [ ] Can add multiple items
- [ ] Can remove items
- [ ] Submit creates invoice
- [ ] Success closes slide-over

### ✅ SPA Behavior
- [ ] No full page reloads
- [ ] URL updates on navigation
- [ ] Browser back/forward works
- [ ] State persists during navigation
- [ ] Fast perceived performance
- [ ] Smooth transitions

## Troubleshooting

### Issue: Sidebar links don't navigate
**Cause:** Page is not using CMSLayoutNew layout  
**Solution:** Add `defineOptions({ layout: CMSLayoutNew })` to the page's script section and remove any layout wrapper from the template.

**Example Fix:**
```vue
<script setup lang="ts">
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})
</script>

<template>
  <!-- Remove MobileLayout or any other layout wrapper -->
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Your content here -->
  </div>
</template>
```

### Issue: Slide-over doesn't open
**Solution:** Check that:
1. Page is using `CMSLayoutNew` layout
2. `slideOver` is injected: `const slideOver = inject('slideOver')`
3. Optional chaining is used: `slideOver?.open('job')`

### Issue: Form doesn't submit
**Solution:** Check that:
1. Form component has `@success` handler
2. Route exists in `routes/cms.php`
3. Controller method exists
4. Validation rules are correct

### Issue: Navigation causes full page reload
**Solution:** Check that:
1. Using `router.visit()` not `<Link>`
2. `preserveState: true` is set
3. `only` array includes necessary data

### Issue: Data doesn't refresh after form submit
**Solution:** Check that:
1. `handleFormSuccess` calls `router.reload()`
2. `only` array includes updated data keys
3. Controller returns updated data

## Next Steps

### Immediate
1. **Test all functionality** using checklist above
2. **Fix any bugs** discovered during testing
3. **Update other CMS pages** to use CMSLayoutNew
4. **Convert all navigation** to SPA style

### Future Enhancements
1. **Optimistic updates** - Show changes immediately
2. **Smart caching** - Cache frequently accessed data
3. **Keyboard shortcuts** - Add keyboard navigation
4. **Search functionality** - Implement global search
5. **Notifications** - Real-time notifications system

## Benefits

✅ **No Page Reloads** - True SPA experience  
✅ **Fast Performance** - Only fetch what changed  
✅ **Modern UX** - Slide-over panels like Linear/Notion  
✅ **Consistent Forms** - Same form everywhere  
✅ **Easy to Maintain** - Reusable components  
✅ **Mobile Friendly** - Responsive design  
✅ **Accessible** - Proper ARIA labels and focus management  

## Changelog

### February 8, 2026
- Created complete SPA architecture
- Implemented slide-over panel system
- Created all form components
- Updated Dashboard to use new layout
- Created comprehensive testing guide
