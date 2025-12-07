# BizBoost Styling Guide

**Last Updated:** December 6, 2025
**Status:** Active

## Overview

This guide documents the consistent styling patterns used across all BizBoost pages. All components should follow these patterns to ensure a cohesive user experience with proper dark mode support.

## Color Scheme

### Primary Colors
- **Primary Action**: `bg-violet-600 hover:bg-violet-700` (buttons, links)
- **Primary Text**: `text-violet-600 dark:text-violet-400`
- **Primary Background**: `bg-violet-100 dark:bg-violet-900/30`

### Status Colors
```
Success: bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400
Warning: bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400
Error:   bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
Info:    bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400
Neutral: bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
```

## Component Patterns

### Page Header
```vue
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Page Title</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Page description</p>
    </div>
    <Link
        href="/action"
        class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
    >
        <PlusIcon class="h-5 w-5" aria-hidden="true" />
        Action Button
    </Link>
</div>
```

### Cards/Containers
```vue
<div class="rounded-xl bg-white dark:bg-gray-800 p-6 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
    <!-- Content -->
</div>
```

### Form Inputs
```vue
<input
    class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-violet-500 focus:ring-violet-500"
/>
```

### Select Dropdowns
```vue
<select
    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500"
>
```

### Labels
```vue
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Label</label>
```

### Error Messages
```vue
<p class="mt-1 text-sm text-red-600 dark:text-red-400">Error message</p>
```

### Helper Text
```vue
<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Helper text</p>
```

### Primary Button
```vue
<button
    class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50"
>
    Button Text
</button>
```

### Secondary Button
```vue
<button
    class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"
>
    Cancel
</button>
```

### Back Link
```vue
<Link
    href="/back"
    class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6"
>
    <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
    Back to List
</Link>
```

### Tables
```vue
<div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700/50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Header</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">Cell</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Empty State
```vue
<div class="rounded-xl bg-white dark:bg-gray-800 p-12 text-center shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
    <IconComponent class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" aria-hidden="true" />
    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No items yet</h3>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Description text.</p>
    <Link
        href="/create"
        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
    >
        <PlusIcon class="h-5 w-5" aria-hidden="true" />
        Create Item
    </Link>
</div>
```

### Status Badges
```vue
<span
    :class="[
        'text-xs px-2 py-1 rounded-full',
        status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
        status === 'pending' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' :
        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
    ]"
>
    {{ status }}
</span>
```

### Checkboxes
```vue
<input
    type="checkbox"
    class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-violet-600 focus:ring-violet-500"
/>
```

### Icon Buttons
```vue
<button
    class="p-2 text-gray-400 hover:text-violet-600 dark:hover:text-violet-400"
    aria-label="Action description"
>
    <IconComponent class="h-5 w-5" aria-hidden="true" />
</button>
```

## Reusable Components

Located in `resources/js/Components/BizBoost/`:

### Form Components (`Form/`)
- `FormInput.vue` - Text inputs with label and error handling
- `FormTextarea.vue` - Textarea with label and error handling
- `FormSelect.vue` - Select dropdown with label and error handling
- `FormCheckbox.vue` - Checkbox with label

### UI Components (`UI/`)
- `Card.vue` - Container card with consistent styling
- `Button.vue` - Primary/secondary buttons
- `Modal.vue` - Modal dialog
- `PageHeader.vue` - Page header with title and back link
- `Badge.vue` - Status badges
- `EmptyState.vue` - Empty state placeholder

## Accessibility

1. Always use `aria-hidden="true"` on decorative icons
2. Always use `aria-label` on icon-only buttons
3. Use semantic HTML elements
4. Ensure sufficient color contrast
5. Support keyboard navigation

## Dark Mode

All components must support dark mode using Tailwind's `dark:` prefix. Common patterns:

- Background: `bg-white dark:bg-gray-800`
- Text: `text-gray-900 dark:text-white`
- Secondary text: `text-gray-500 dark:text-gray-400`
- Borders: `border-gray-200 dark:border-gray-700`
- Ring: `ring-gray-200 dark:ring-gray-700`

## Migration Checklist

When updating existing pages:

- [ ] Replace `bg-blue-600` with `bg-violet-600`
- [ ] Add dark mode classes to all elements
- [ ] Use `rounded-xl` for cards (not `rounded-lg`)
- [ ] Use `ring-1 ring-gray-200 dark:ring-gray-700` for card borders
- [ ] Use consistent spacing (`space-y-6` for page sections)
- [ ] Add `aria-hidden="true"` to decorative icons
- [ ] Add `aria-label` to icon-only buttons
