# Live Chat Widget Fix

## Issue
The LiveChatWidget component was imported in EmployeePortalLayout but wasn't working because:
1. Employee data wasn't being shared globally via Inertia
2. The widget needs employee ID and name to function

## Solution

### 1. Share Employee Data Globally
Updated `app/Http/Middleware/HandleInertiaRequests.php` to share employee data on all employee portal routes:

```php
// Get employee data for employee portal routes
$employee = null;
if ($user && $request->is('employee/portal*')) {
    $employee = \App\Models\Employee::where('user_id', $user->id)
        ->where('employment_status', 'active')
        ->first();
}

return [
    // ... other shared data
    'employee' => $employee,
];
```

### 2. Widget Integration
The LiveChatWidget is already integrated in `EmployeePortalLayout.vue`:

```vue
<LiveChatWidget
    v-if="employee"
    :employee-id="employee.id"
    :employee-name="employee.full_name"
/>
```

## How It Works

### Employee Side:
1. Employee clicks the floating chat button (bottom-right)
2. Chat widget opens with welcome message
3. Employee types a message and sends
4. If no ticket exists, creates a new "Quick Chat Support" ticket
5. If ticket exists, adds comment to existing ticket
6. Real-time updates via Laravel Echo

### Admin Side:
1. Admin sees new ticket notification in sidebar badge
2. Admin opens "Live Support" dashboard
3. Admin clicks on ticket to open live chat