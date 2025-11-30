# Live Chat Widget Integration Guide

**Last Updated:** November 30, 2025

## Overview

The Live Chat Widget provides real-time support chat functionality that can be integrated into multiple portals:
- Employee Portal ✅
- Investor Portal ✅ (Integrated in InvestorLayout)
- Member Portal ✅ (Integrated in AppSidebarLayout)
- Mobile App ✅ (Integrated in MobileDashboard)

## Components

### Unified Live Chat Widget (Recommended)
`resources/js/components/Support/UnifiedLiveChatWidget.vue`
- Single component for all user types (employee, investor, member)
- Responsive design (hides floating button on mobile < 1024px)
- Exposes `openChat()` method for programmatic opening
- Full-screen mode on mobile devices

### Legacy Widget (Deprecated)
`resources/js/components/Support/LiveChatWidget.vue`
- Still works but use UnifiedLiveChatWidget for new integrations

**Note:** The old `resources/js/components/Employee/LiveChatWidget.vue` has been removed. Use `UnifiedLiveChatWidget` with `user-type="employee"` instead.

## Current Integrations

### 1. Mobile Dashboard
**File:** `resources/js/pages/MyGrowNet/MobileDashboard.vue`

The widget is automatically included for all mobile users:
```vue
<LiveChatWidget
  user-type="member"
  :user-id="user?.id || 0"
  :user-name="user?.name || 'Member'"
  @ticket-created="handleTicketCreated"
/>
```

### 2. Investor Portal
**File:** `resources/js/layouts/InvestorLayout.vue`

The widget is automatically included for all investor pages:
```vue
<LiveChatWidget
  user-type="investor"
  :user-id="investor.id"
  :user-name="investor.name"
  @ticket-created="handleTicketCreated"
/>
```

### 3. Classic Member Dashboard
**File:** `resources/js/layouts/app/AppSidebarLayout.vue`

The widget is automatically included for all member pages using the sidebar layout:
```vue
<LiveChatWidget
  user-type="member"
  :user-id="currentUserId"
  :user-name="currentUserName"
  @ticket-created="handleTicketCreated"
/>
```

## Manual Integration

### Employee Portal

```vue
<template>
  <EmployeePortalLayout>
    <!-- Your page content -->
    
    <!-- Live Chat Widget -->
    <UnifiedLiveChatWidget
      user-type="employee"
      :user-id="employee.id"
      :user-name="employee.full_name"
    />
  </EmployeePortalLayout>
</template>

<script setup>
import UnifiedLiveChatWidget from '@/components/Support/UnifiedLiveChatWidget.vue';
</script>
```

### Custom Integration

```vue
<template>
  <YourLayout>
    <SupportChatWidget
      user-type="member"
      :user-id="user.id"
      :user-name="user.name"
      @ticket-created="onTicketCreated"
      @open-full-view="goToTicket"
    />
  </YourLayout>
</template>

<script setup>
import SupportChatWidget from '@/components/Support/LiveChatWidget.vue';

const onTicketCreated = (ticketId) => {
  console.log('New ticket created:', ticketId);
};

const goToTicket = (ticketId) => {
  router.visit(route('mygrownet.support.show', ticketId));
};
</script>
```

## Props

| Prop | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| userType | 'employee' \| 'investor' \| 'member' | Yes | - | Type of user |
| userId | number | Yes | 0 | User's ID |
| userName | string | Yes | 'Guest' | User's display name |
| ticketId | number | No | undefined | Open specific ticket |
| maxHistoryMessages | number | No | 50 | Max messages to show |
| apiBaseUrl | string | No | '' | Custom API base URL |

## Events

| Event | Payload | Description |
|-------|---------|-------------|
| ticketCreated | ticketId: number | Emitted when new ticket is created |
| openFullView | ticketId: number | User wants to see full ticket page |

## Backend Requirements

Each portal needs these API endpoints:

### Current Route Configuration

**Member Portal (MyGrowNet):**
```
GET  /mygrownet/api/support/tickets          - List tickets (JSON)
GET  /mygrownet/api/support/tickets/{id}     - Show ticket (JSON)
POST /mygrownet/support/quick-chat           - Create new ticket
POST /mygrownet/support/{id}/chat            - Send message
```

**Investor Portal:**
```
GET  /investor/api/support/tickets           - List tickets (JSON)
GET  /investor/api/support/tickets/{id}      - Show ticket (JSON)
POST /investor/support/quick-chat            - Create new ticket
POST /investor/support/{id}/chat             - Send message
```

**Employee Portal:**
```
GET  /employee/portal/support                - List tickets
GET  /employee/portal/support/{id}           - Show ticket
POST /employee/portal/support/quick-chat     - Create new ticket
POST /employee/portal/support/{id}/chat      - Send message
```

### Required Routes (Generic)

```php
// List user's tickets (JSON)
GET /[portal]/support

// Show ticket details (JSON)
GET /[portal]/support/{ticket}

// Create new ticket via chat
POST /[portal]/support/quick-chat

// Send message to existing ticket
POST /[portal]/support/{ticket}/chat
```

### Example Controller Methods

```php
// List tickets (with JSON support)
public function index(Request $request)
{
    $tickets = $this->getTicketsForUser($request->user());
    
    if ($request->wantsJson()) {
        return response()->json([
            'tickets' => $tickets->map(fn($t) => [
                'id' => $t->id,
                'ticket_number' => $t->ticket_number,
                'subject' => $t->subject,
                'status' => $t->status,
                'updated_at' => $t->updated_at->toISOString(),
                'comments_count' => $t->comments()->count(),
            ]),
        ]);
    }
    
    return Inertia::render('Support/Index', ['tickets' => $tickets]);
}

// Quick chat (create ticket)
public function quickChat(Request $request)
{
    $request->validate([
        'message' => 'required|string|max:2000',
        'category' => 'nullable|string',
    ]);
    
    $ticket = $this->createTicket([
        'user_id' => $request->user()->id,
        'subject' => $this->getCategorySubject($request->category),
        'description' => $request->message,
        'category' => $request->category ?? 'general',
    ]);
    
    return response()->json([
        'success' => true,
        'ticket_id' => $ticket->id,
        'ticket_number' => $ticket->ticket_number,
    ]);
}
```

## WebSocket Channel Authorization

Add channel authorization for each user type:

```php
// routes/channels.php

// Investor support channel
Broadcast::channel('investor.support.{ticketId}', function ($user, $ticketId) {
    $ticket = InvestorSupportTicket::find($ticketId);
    if (!$ticket) return false;
    
    // Investor can access their own tickets
    if ($ticket->investor_id === $user->investor?->id) {
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'investor'];
    }
    
    // Support staff can access all
    if ($user->hasRole('support') || $user->hasRole('admin')) {
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'support'];
    }
    
    return false;
});
```

## Handling Long Conversation History

The widget automatically handles long histories:

1. **Initial Load**: Shows only the last `maxHistoryMessages` (default 50)
2. **Load More**: "Load earlier messages" button appears if there are more
3. **Full View**: "View full conversation history" link opens the full ticket page

## Mobile App Integration

For mobile apps using WebView:

```javascript
// In your mobile app WebView
const webView = new WebView({
  url: 'https://yourapp.com/mobile/support',
  // Inject user data
  injectedJavaScript: `
    window.MOBILE_USER = {
      type: 'member',
      id: ${userId},
      name: '${userName}'
    };
  `
});
```

Then in your Vue component:
```vue
<script setup>
const mobileUser = (window as any).MOBILE_USER;

// Use mobileUser data if available
const userType = mobileUser?.type || 'member';
const userId = mobileUser?.id || 0;
const userName = mobileUser?.name || 'Guest';
</script>
```

## Categories by User Type

The widget shows different categories based on user type:

**Employee:**
- General Inquiry
- Technical Issue
- HR Related
- Payroll Question
- IT Support

**Investor:**
- Investment Question
- Withdrawal Help
- Account Issue
- Returns & Dividends
- General Inquiry

**Member:**
- Subscription Help
- Learning Content
- Commissions & Earnings
- Account Issue
- General Inquiry
