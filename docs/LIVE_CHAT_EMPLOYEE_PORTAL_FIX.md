# Live Chat System - Employee Portal & Admin Support

**Last Updated:** November 29, 2025  
**Status:** ✅ Working

---

## Architecture Overview

The Live Chat system has two sides:

1. **Employee Side** - LiveChatWidget in Employee Portal (for employees to request support)
2. **Admin Side** - Admin Support Dashboard (for admins to respond to employee tickets)

### Key Design Decision: Admins Don't Need Employee Records

The `EnsureIsEmployee` middleware allows admins to access the employee portal **without** requiring an employee record:

```php
// Admins without employee record can still access (for support purposes)
if (!$employee && !$user->hasRole('admin') && !$user->hasRole('superadmin')) {
    // Only block non-admins without employee records
}
```

This is intentional - admins may need to view the employee portal for support/debugging purposes.

---

## How It Works

### For Employees (with employee records)
- Access: `/employee/portal/*`
- LiveChatWidget appears in the bottom-right corner
- Can create support tickets and chat in real-time
- Routes: `employee.portal.support.*`

### For Admins (with or without employee records)
- Access: `/admin/support/*`
- Use Admin Support Dashboard to view and respond to tickets
- Can use Live Chat feature to respond in real-time
- Routes: `admin.support.*`

**Important:** Admins should use the Admin Support Dashboard, NOT the employee portal, to respond to support tickets.

---

## Component Behavior

### LiveChatWidget (`resources/js/components/Employee/LiveChatWidget.vue`)
- Only renders when `employee` data exists (`v-if="employee"`)
- Props are optional with safe defaults
- Admins without employee records won't see the widget (correct behavior)

### EmployeePortalLayout
```vue
<LiveChatWidget
    v-if="employee"
    :employee-id="employee.id"
    :employee-name="employee.full_name"
/>
```

### Admin Support Controller
- Uses null-safe operators for admin employee records
- Falls back to user name if no employee record exists:
```php
$adminEmployee = Employee::where('user_id', Auth::id())->first();
$adminEmployee?->full_name ?? Auth::user()->name
```

---

## Routes

### Employee Portal Support Routes
```
GET  /employee/portal/support           - List tickets
GET  /employee/portal/support/create    - Create ticket form
POST /employee/portal/support           - Store new ticket
GET  /employee/portal/support/{ticket}  - View ticket (with chat)
POST /employee/portal/support/{ticket}/chat - Send chat message
POST /employee/portal/support/quick-chat    - Quick chat (creates ticket)
```

### Admin Support Routes
```
GET  /admin/support                     - List all tickets
GET  /admin/support/dashboard           - Live support dashboard
GET  /admin/support/{ticket}            - View ticket details
GET  /admin/support/{ticket}/live-chat  - Live chat interface
POST /admin/support/{ticket}/chat       - Send chat message
```

---

## Behavior Summary

| User Type | Employee Record | Can Access Employee Portal | Sees LiveChatWidget | Should Use |
|-----------|-----------------|---------------------------|---------------------|------------|
| Employee | ✅ Yes | ✅ Yes | ✅ Yes | Employee Portal |
| Admin | ✅ Yes | ✅ Yes | ✅ Yes | Admin Dashboard |
| Admin | ❌ No | ✅ Yes | ❌ No | Admin Dashboard |
| Regular User | ❌ No | ❌ No | N/A | N/A |

---

## Troubleshooting

### "500 Error" when accessing employee portal
- Check if user has correct role (`employee`, `admin`, or `superadmin`)
- Verify middleware is applied correctly

### LiveChatWidget not appearing
- Verify user has an active employee record
- Check `HandleInertiaRequests.php` is sharing `employee` data
- Admins without employee records won't see the widget (this is correct)

### Admin can't respond to tickets
- Use `/admin/support/dashboard` instead of employee portal
- Admin Support Controller handles missing employee records gracefully
