# GrowBiz SPA Enhancements

**Last Updated:** December 2, 2025
**Status:** Production

## Overview

GrowBiz uses Inertia.js which provides SPA-like experience out of the box. This document covers the additional enhancements implemented for a native app feel.

## What You Already Have

Inertia.js provides:
- ✅ No full page reloads (XHR requests swap page components)
- ✅ Client-side navigation with browser history
- ✅ Shared layouts that persist across pages
- ✅ Form handling without page refreshes
- ✅ Progress indicator during navigation

## Implemented Enhancements

### 1. Global Loading Bar

A smooth progress bar at the top during navigation.

```vue
<!-- Already included in GrowBizLayout.vue -->
<GlobalLoadingBar />
```

### 2. Page Transitions

Smooth slide animations between pages using `AppTransition`:

```vue
<AppTransition>
    <slot />
</AppTransition>
```

Available transition types:
- `slide` (default) - Slides left/right
- `fade` - Simple opacity fade
- `scale` - Scale up/down (good for modals)

### 3. Pull-to-Refresh

Mobile-style refresh gesture:

```vue
<PullToRefresh ref="pullRef" @refresh="handleRefresh">
    <YourContent />
</PullToRefresh>

<script setup>
const pullRef = ref(null);

const handleRefresh = () => {
    router.reload({
        onFinish: () => pullRef.value?.finishRefresh()
    });
};
</script>
```

### 4. Partial Reloads

Only fetch specific props to reduce data transfer:

```ts
import { usePartialReload } from '@/composables/useInertiaEnhancements';

const { reload } = usePartialReload();

// Only reload tasks, not the entire page
reload(['tasks'], { preserveScroll: true });
```

### 5. Optimistic UI Updates

Show changes immediately before server confirms:

```ts
import { useOptimisticSubmit } from '@/composables/useInertiaEnhancements';

const { isSubmitting, submit } = useOptimisticSubmit();

// Optimistically mark task as complete
submit('patch', route('tasks.update', task.id), 
    { status: 'completed' },
    {
        optimistic: { ...task, status: 'completed' },
        onSuccess: () => toast.success('Task completed!'),
        onError: () => toast.error('Failed to update'),
    }
);
```

### 6. Preserve Scroll Position

Keep scroll position during navigation:

```vue
<Link :href="route('tasks.show', task.id)" preserve-scroll>
    View Task
</Link>
```

Or programmatically:

```ts
router.visit(url, { preserveScroll: true });
```

### 7. Preserve State

Keep component state during navigation:

```ts
router.get(route('tasks.index'), filters, {
    preserveState: true,
    preserveScroll: true,
});
```

## Usage Examples

### Filter Without Page Reload

```ts
const applyFilters = () => {
    router.get(route('growbiz.tasks.index'), {
        status: filters.status || undefined,
        priority: filters.priority || undefined,
    }, { 
        preserveState: true, 
        preserveScroll: true 
    });
};
```

### Debounced Search

```ts
import { useDebounceFn } from '@vueuse/core';

const debouncedSearch = useDebounceFn(() => {
    router.get(route('tasks.index'), { search: query.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);
```

### Form Submission with Loading State

```ts
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    description: '',
});

const submit = () => {
    form.post(route('tasks.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
```

## Best Practices

1. **Always use `<Link>` instead of `<a>`** for internal navigation
2. **Use `preserveScroll`** when updating filters or partial content
3. **Use `preserveState`** to keep form data during filter changes
4. **Use partial reloads** when only specific data needs updating
5. **Add loading states** for better UX during async operations

## Components Reference

| Component | Purpose |
|-----------|---------|
| `GlobalLoadingBar` | Top progress bar during navigation |
| `AppTransition` | Page transition animations |
| `PullToRefresh` | Mobile pull-to-refresh gesture |
| `SkeletonLoader` | Loading placeholder UI |
| `BottomSheet` | Mobile-style modal from bottom |
| `SwipeableListItem` | Swipe actions on list items |
| `FloatingActionButton` | FAB for primary actions |
| `HapticButton` | Button with haptic feedback |

## Composables Reference

| Composable | Purpose |
|------------|---------|
| `useNavigationState()` | Global loading state |
| `usePartialReload()` | Reload specific props only |
| `useOptimisticSubmit()` | Optimistic UI updates |
| `usePrefetch()` | Prefetch links on hover |
| `useScrollPreservation()` | Save/restore scroll position |
| `useNavigate()` | Programmatic navigation |


## Error Handling

### Backend Exception Classes

GrowBiz uses domain-specific exceptions for clear error handling:

| Exception | HTTP Code | Use Case |
|-----------|-----------|----------|
| `EmployeeNotFoundException` | 404 | Employee not found |
| `TaskNotFoundException` | 404 | Task not found |
| `UnauthorizedAccessException` | 403 | Access denied |
| `DuplicateEmployeeException` | 422 | Email already exists |
| `EmployeeHasActiveTasksException` | 422 | Cannot delete with active tasks |
| `InvalidAssignmentException` | 422 | Invalid employee assignment |
| `InvalidStatusTransitionException` | 422 | Invalid status change |
| `OperationFailedException` | 500 | Generic operation failure |

### Exception Location

All exceptions are in `app/Domain/GrowBiz/Exceptions/`:

```
app/Domain/GrowBiz/Exceptions/
├── GrowBizException.php          # Base exception
├── EmployeeNotFoundException.php
├── TaskNotFoundException.php
├── UnauthorizedAccessException.php
├── DuplicateEmployeeException.php
├── EmployeeHasActiveTasksException.php
├── InvalidAssignmentException.php
├── InvalidStatusTransitionException.php
├── OperationFailedException.php
└── Handler.php                   # Exception handler
```

### Using Exceptions in Services

```php
use App\Domain\GrowBiz\Exceptions\EmployeeNotFoundException;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;

public function getEmployeeById(int $employeeId): Employee
{
    $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
    
    if (!$employee) {
        throw new EmployeeNotFoundException($employeeId);
    }

    return $employee;
}
```

### Handling in Controllers

```php
public function show(int $id)
{
    try {
        $employee = $this->employeeService->getEmployeeById($id);
        $this->authorizeEmployee($employee);

        return Inertia::render('GrowBiz/Employees/Show', [
            'employee' => $employee->toArray(),
        ]);
    } catch (EmployeeNotFoundException $e) {
        abort(404, $e->getMessage());
    } catch (UnauthorizedAccessException $e) {
        abort(403, $e->getMessage());
    }
}
```

### Frontend Error Display

Errors are passed via Inertia flash messages:

```vue
<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const error = computed(() => page.props.flash?.error);
</script>

<template>
    <div v-if="error" class="bg-red-50 text-red-700 p-4 rounded-lg">
        {{ error }}
    </div>
</template>
```

### Toast Notifications

Use the toast composable for user feedback:

```ts
import { useToast } from '@/composables/useToast';

const toast = useToast();

// Success
toast.success('Employee created successfully');

// Error
toast.error('Failed to create employee');

// Warning
toast.warning('Employee has active tasks');
```

### Logging

GrowBiz errors are logged to a dedicated channel:

```php
// Logs to storage/logs/growbiz.log
Log::channel('growbiz')->error('Operation failed', [
    'error_code' => $exception->getErrorCode(),
    'context' => $exception->getContext(),
]);
```

### Best Practices

1. **Always catch specific exceptions first**, then generic ones
2. **Use appropriate HTTP codes** (404 for not found, 403 for unauthorized, 422 for validation)
3. **Log errors with context** for debugging
4. **Show user-friendly messages** in the UI
5. **Don't expose internal errors** to users in production
