# Live Chat System - Complete Documentation

**Last Updated:** November 29, 2025  
**Status:** ✅ Production Ready  
**Version:** 2.0.0

---

## Overview

The Live Chat System provides real-time communication between users (employees, investors, and members) and support staff through WebSocket-powered chat. The system integrates seamlessly with:
- **Employee Portal** - Internal employee support
- **Investor Portal** - VBIF investor support
- **MyGrowNet Member Portal** - Platform member support
- **Admin Dashboard** - Unified support management

---

## Table of Contents

1. [Architecture](#architecture)
2. [Features](#features)
3. [Backend Components](#backend-components)
4. [Frontend Components](#frontend-components)
5. [Routes](#routes)
6. [Database Schema](#database-schema)
7. [Broadcasting](#broadcasting)
8. [Real-Time Flow](#real-time-flow)
9. [Configuration](#configuration)
10. [Usage Guide](#usage-guide)
11. [Testing](#testing)
12. [Troubleshooting](#troubleshooting)
13. [Security](#security)
14. [Performance](#performance)
15. [Deployment](#deployment)

---

## Architecture

### Technology Stack
- **Backend:** Laravel 12, Laravel Broadcasting
- **Frontend:** Vue 3 + TypeScript, Inertia.js
- **Real-time:** Laravel Echo, Pusher (or Laravel WebSockets)
- **Database:** MySQL/SQLite

### Communication Flow
```
┌─────────────────────────────────────────────────────────────────┐
│                        User Portals                              │
├─────────────────┬─────────────────┬─────────────────────────────┤
│ Employee Portal │ Investor Portal │ MyGrowNet Member Portal     │
└────────┬────────┴────────┬────────┴────────────┬────────────────┘
         │                 │                      │
         ▼                 ▼                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel Echo / WebSocket                      │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│                 Unified Admin Support Dashboard                  │
│  - Employee Tickets (employee_support_tickets)                   │
│  - Investor Tickets (support_tickets with source='investor')     │
│  - Member Tickets (support_tickets with source='member')         │
└─────────────────────────────────────────────────────────────────┘
```

---

## Features

### For All Users (Employees, Investors, Members)
✅ Create support tickets with categories and priorities  
✅ Real-time chat with support staff  
✅ View ticket history and status  
✅ Receive instant responses  
✅ Typing indicators  
✅ Connection status display  
✅ Floating chat widget  
✅ Message timestamps  

### For Investors (VBIF Portal)
✅ Investment-specific categories (Investment, Withdrawal, Returns)  
✅ Session-based authentication support  
✅ Dedicated investor support channel  

### For Members (MyGrowNet Portal)
✅ Platform-specific categories (Subscription, Learning, Commission)  
✅ Laravel auth integration  
✅ Dedicated member support channel  

### For Admins/Support Staff
✅ **Unified Support Dashboard** - All tickets from all sources  
✅ Real-time message notifications  
✅ Priority-based ticket sorting  
✅ Urgent ticket alerts  
✅ Full-screen chat interface  
✅ Badge count in sidebar for open tickets  
✅ Ticket assignment and status management  
✅ Internal notes (private comments)  
✅ Source filtering (Employee/Investor/Member)  
✅ User information sidebar  

---

## Backend Components

### 1. Controllers

#### Unified Admin Support Controller (NEW)
**File:** `app/Http/Controllers/Admin/UnifiedSupportController.php`

**Methods:**
- `index()` - Unified dashboard showing all tickets from all sources
- `show()` - Individual ticket details (handles employee/investor/member)
- `update()` - Update ticket status/priority
- `addReply()` - Add reply to ticket (broadcasts to appropriate channel)
- `liveChat()` - Full-screen chat interface

#### Admin Support Controller (Employee-specific)
**File:** `app/Http/Controllers/Admin/SupportTicketController.php`

**Methods:**
- `dashboard()` - Live support dashboard with active tickets
- `index()` - All tickets list with filters
- `show()` - Individual ticket details
- `liveChat()` - Full-screen chat interface
- `sendChatMessage()` - API endpoint for sending messages
- `update()` - Update ticket status/priority/assignment
- `addComment()` - Add comment to ticket

#### Investor Support Controller
**File:** `app/Http/Controllers/Investor/SupportController.php`

**Methods:**
- `listJson()` - Get investor's tickets (JSON)
- `showJson()` - Get ticket details (JSON)
- `quickChat()` - Create new ticket from chat widget
- `chat()` - Send message to existing ticket

#### MyGrowNet Support Controller
**File:** `app/Http/Controllers/MyGrowNet/SupportTicketController.php`

**Methods:**
- `index()` - Member's ticket list
- `create()` - Create new ticket form
- `store()` - Store new ticket
- `show()` - View ticket with live chat
- `addComment()` - Add comment to ticket
- `quickChat()` - Quick chat widget support
- `chat()` - Live chat API endpoint
- `listJson()` - Get tickets (JSON)
- `showJson()` - Get ticket details (JSON)

#### Employee Portal Controller
**File:** `app/Http/Controllers/Employee/PortalController.php`

**Methods:**
- `supportTickets()` - Employee's ticket list
- `supportTicketCreate()` - Create new ticket form
- `supportTicketStore()` - Store new ticket
- `supportTicketShow()` - View ticket with live chat
- `supportTicketAddComment()` - Add comment to ticket
- `supportTicketChat()` - Live chat API endpoint
- `supportQuickChat()` - Quick chat widget support

### 2. Services

#### SupportTicketService
**File:** `app/Domain/Employee/Services/SupportTicketService.php`

**Methods:**
- `createTicket()` - Creates ticket and broadcasts to admin
- `addComment()` - Adds comment and broadcasts to chat
- `getTicketsForEmployee()` - Get employee's tickets
- `getTicketWithComments()` - Get ticket with comments
- `getTicketStats()` - Get ticket statistics
- `getCategories()` - Get ticket categories

### 3. Broadcasting Events

#### LiveChatMessage (Employee)
**File:** `app/Events/Employee/LiveChatMessage.php`

```php
public function __construct(
    public int $ticketId,
    public int $senderId,
    public string $senderName,
    public string $senderType, // 'employee' or 'support'
    public string $message,
    public string $sentAt
)
```

- **Channel:** `support.ticket.{ticketId}`
- **Event:** `chat.message`
- **Purpose:** Real-time message delivery for employee tickets

#### InvestorSupportMessage
**File:** `app/Events/Investor/InvestorSupportMessage.php`

```php
public function __construct(
    public int $ticketId,
    public int $senderId,
    public string $senderName,
    public string $senderType, // 'investor' or 'support'
    public string $message,
    public string $sentAt
)
```

- **Channel:** `investor.support.{ticketId}`
- **Event:** `chat.message`
- **Purpose:** Real-time message delivery for investor tickets

#### MemberSupportMessage
**File:** `app/Events/Member/MemberSupportMessage.php`

```php
public function __construct(
    public int $ticketId,
    public int $senderId,
    public string $senderName,
    public string $senderType, // 'member' or 'support'
    public string $message,
    public string $sentAt
)
```

- **Channel:** `member.support.{ticketId}`
- **Event:** `chat.message`
- **Purpose:** Real-time message delivery for member tickets

#### SupportTicketCreated
**File:** `app/Events/Employee/SupportTicketCreated.php`

```php
public function __construct(
    public EmployeeSupportTicket $ticket
)
```

- **Channel:** `support.admin`
- **Event:** `ticket.created`
- **Purpose:** Notify admins of new tickets

### 4. Models

#### EmployeeSupportTicket
**File:** `app/Models/EmployeeSupportTicket.php`

**Relationships:**
- `employee()` - Belongs to Employee
- `assignedTo()` - Belongs to Employee (nullable)
- `comments()` - Has many EmployeeSupportTicketComment

#### EmployeeSupportTicketComment
**File:** `app/Models/EmployeeSupportTicketComment.php`

**Relationships:**
- `ticket()` - Belongs to EmployeeSupportTicket
- `author()` - Belongs to Employee (nullable)

#### SupportTicketModel (Investor/Member)
**File:** `app/Infrastructure/Persistence/Eloquent/Support/SupportTicketModel.php`

**Fields:**
- `user_id` - For member tickets
- `investor_account_id` - For investor tickets
- `source` - 'investor' or 'member'
- `category`, `priority`, `status`, `subject`, `description`

**Relationships:**
- `user()` - Belongs to User
- `investorAccount()` - Belongs to InvestorAccount
- `comments()` - Has many TicketCommentModel

#### TicketCommentModel
**File:** `app/Infrastructure/Persistence/Eloquent/Support/TicketCommentModel.php`

**Fields:**
- `user_id` - For member/support comments
- `investor_account_id` - For investor comments
- `author_type` - 'investor', 'member', or 'support'
- `author_name` - Display name

**Relationships:**
- `ticket()` - Belongs to SupportTicketModel
- `user()` - Belongs to User
- `investorAccount()` - Belongs to InvestorAccount

### 5. Middleware

#### HandleInertiaRequests
**File:** `app/Http/Middleware/HandleInertiaRequests.php`

**Shared Data:**
```php
'supportStats' => [
    'open' => count of open tickets,
    'in_progress' => count of in-progress tickets,
    'urgent' => count of urgent tickets
]
```

- Cached for 60 seconds
- Only loaded for admin users
- Cache cleared when new tickets are created

---

## Frontend Components

### 1. Admin Pages

#### Unified Support Index (NEW)
**File:** `resources/js/pages/Admin/Support/UnifiedIndex.vue`

**Features:**
- All tickets from all sources (Employee, Investor, Member)
- Source filtering tabs
- Stats by source
- Search, status, priority filters
- Click ticket to open unified show/chat

#### Unified Support Show (NEW)
**File:** `resources/js/pages/Admin/Support/UnifiedShow.vue`

**Features:**
- Ticket details from any source
- Comment history
- Status/priority management
- Link to live chat

#### Unified Live Chat (NEW)
**File:** `resources/js/pages/Admin/Support/UnifiedLiveChat.vue`

**Features:**
- Full-screen chat interface
- Handles all ticket sources
- Broadcasts to appropriate channel based on source
- Real-time message updates
- Typing indicators

#### Live Support Dashboard (Employee-specific)
**File:** `resources/js/pages/Admin/Support/Dashboard.vue`

**Features:**
- Real-time dashboard showing active employee tickets
- Stats cards: Open, In Progress, Urgent, Avg Response Time
- Urgent tickets alert section
- Two-column layout: Open Tickets | In Progress
- Auto-refreshes when new tickets arrive
- Click ticket to open live chat

#### Live Chat Interface (Employee-specific)
**File:** `resources/js/pages/Admin/Support/LiveChat.vue`

**Features:**
- Full-screen chat interface
- Real-time message updates via Laravel Echo
- Typing indicators
- Employee info sidebar
- Message history
- Send messages with Enter key
- Optimistic UI updates
- Connection status indicator

#### Support Tickets Index (Employee-specific)
**File:** `resources/js/pages/Admin/Support/Index.vue`

**Features:**
- Complete ticket list with filters
- Search, status, priority, category filters
- Pagination
- Stats overview

#### Ticket Details
**File:** `resources/js/pages/Admin/Support/Show.vue`

**Features:**
- Ticket information
- Comment history
- Status/priority/assignment management
- Link to live chat

### 2. Employee Pages

#### Support Ticket Show
**File:** `resources/js/pages/Employee/Portal/Support/Show.vue`

**Features:**
- Ticket details with live chat interface
- Real-time message updates
- Typing indicators
- Original request display
- Send messages
- Status indicators
- Disabled input for closed tickets

#### Support Tickets Index
**File:** `resources/js/pages/Employee/Portal/Support/Index.vue`

**Features:**
- Employee's ticket list
- Stats cards
- Filter by status/category
- Create new ticket button
- Quick help section

#### Create Ticket
**File:** `resources/js/pages/Employee/Portal/Support/Create.vue`

**Features:**
- Create new support ticket
- Category selection
- Priority selection
- Description field
- File attachments (planned)

### 3. Shared Components

#### UnifiedLiveChatWidget (NEW - Recommended)
**File:** `resources/js/components/Support/UnifiedLiveChatWidget.vue`

**Features:**
- Works for Employee, Investor, and Member portals
- Adapts categories based on userType
- Floating chat button
- Minimizable chat window
- Quick chat support
- Real-time messaging
- Connection status indicator

**Usage:**
```vue
<!-- For Investors -->
<UnifiedLiveChatWidget
  user-type="investor"
  :user-id="investor.id"
  :user-name="investor.name"
/>

<!-- For Members -->
<UnifiedLiveChatWidget
  user-type="member"
  :user-id="user.id"
  :user-name="user.name"
/>

<!-- For Employees -->
<UnifiedLiveChatWidget
  user-type="employee"
  :user-id="employee.id"
  :user-name="employee.full_name"
/>
```

#### LiveChatWidget (Generic)
**File:** `resources/js/components/Support/LiveChatWidget.vue`

**Features:**
- Same as UnifiedLiveChatWidget
- Supports all user types
- Full-featured chat widget

#### Employee LiveChatWidget (Legacy)
**File:** `resources/js/components/Employee/LiveChatWidget.vue`

**Features:**
- Floating chat button
- Minimizable chat window
- Quick chat support
- Real-time messaging
- Welcome messages
- Connection status indicator

**Usage:**
```vue
<LiveChatWidget
  :employee-id="employee.id"
  :employee-name="employee.full_name"
  :ticket-id="ticket?.id"
/>
```

#### CustomAdminSidebar
**File:** `resources/js/components/CustomAdminSidebar.vue`

**Communication Section:**
- Messages
- Compose Message
- Support Tickets
- **Live Support** (with badge showing open ticket count)
- Email Campaigns
- Telegram Bot

**Badge Display:**
```vue
<span v-if="item.title === 'Live Support' && page.props.supportStats?.open > 0"
    class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full 
           text-xs font-medium bg-green-100 text-green-800">
    {{ page.props.supportStats.open }}
</span>
```

---

## Routes

### Unified Admin Routes (NEW)
```php
Route::middleware(['admin'])->prefix('admin/unified-support')->name('admin.unified-support.')->group(function () {
    Route::get('/', 'index');                                    // All tickets from all sources
    Route::get('/{source}/{id}', 'show');                        // Ticket details
    Route::patch('/{source}/{id}', 'update');                    // Update ticket
    Route::post('/{source}/{id}/reply', 'addReply');             // Add reply
    Route::get('/{source}/{id}/live-chat', 'liveChat');          // Live chat view
});
```

### Admin Routes (Employee-specific)
```php
Route::middleware(['admin'])->prefix('admin/support')->name('admin.support.')->group(function () {
    Route::get('/', 'index');                          // All tickets
    Route::get('/dashboard', 'dashboard');             // Live dashboard
    Route::get('/{ticket}', 'show');                   // Ticket details
    Route::patch('/{ticket}', 'update');               // Update ticket
    Route::post('/{ticket}/comment', 'addComment');    // Add comment
    Route::get('/{ticket}/live-chat', 'liveChat');     // Live chat view
    Route::post('/{ticket}/chat', 'sendChatMessage');  // Send message
});
```

### Investor Routes
```php
Route::prefix('investor')->name('investor.support.')->group(function () {
    Route::get('/api/support/tickets', 'listJson');              // List tickets (JSON)
    Route::get('/api/support/tickets/{id}', 'showJson');         // Ticket details (JSON)
    Route::post('/support/quick-chat', 'quickChat');             // Create ticket
    Route::post('/support/{id}/chat', 'chat');                   // Send message
});
```

### MyGrowNet Member Routes
```php
Route::prefix('mygrownet/support')->name('mygrownet.support.')->group(function () {
    Route::get('/', 'index');                                    // Tickets list
    Route::get('/create', 'create');                             // Create form
    Route::post('/', 'store');                                   // Store ticket
    Route::get('/{id}', 'show');                                 // Ticket view
    Route::post('/{id}/comment', 'addComment');                  // Add comment
    Route::post('/{id}/chat', 'chat');                           // Send message
    Route::post('/quick-chat', 'quickChat');                     // Quick chat
});

Route::prefix('mygrownet/api/support')->name('mygrownet.support.')->group(function () {
    Route::get('/tickets', 'listJson');                          // List tickets (JSON)
    Route::get('/tickets/{id}', 'showJson');                     // Ticket details (JSON)
    Route::get('/tickets/{id}/comments', 'getComments');         // Get comments (JSON)
});
```

### Employee Routes
```php
Route::prefix('employee/portal/support')->name('employee.portal.support.')->group(function () {
    Route::get('/', 'supportTickets');                      // Tickets list
    Route::get('/create', 'supportTicketCreate');           // Create form
    Route::post('/', 'supportTicketStore');                 // Store ticket
    Route::get('/{ticket}', 'supportTicketShow');           // Ticket view
    Route::post('/{ticket}/comment', 'supportTicketAddComment'); // Add comment
    Route::post('/{ticket}/chat', 'supportTicketChat');     // Send message
    Route::post('/quick-chat', 'supportQuickChat');         // Quick chat
});
```

---

## Database Schema

### employee_support_tickets (Employee Portal)
```sql
- id (bigint, primary key)
- ticket_number (string, unique)
- employee_id (foreign key → employees)
- subject (string)
- description (text)
- category (enum: it, hr, facilities, payroll, equipment, access, other)
- priority (enum: low, medium, high, urgent)
- status (enum: open, in_progress, pending, resolved, closed)
- assigned_to (foreign key → employees, nullable)
- resolved_at (timestamp, nullable)
- attachments (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### employee_support_ticket_comments
```sql
- id (bigint, primary key)
- ticket_id (foreign key → employee_support_tickets)
- author_id (foreign key → employees, nullable)
- author_type (enum: employee, support)
- content (text)
- is_internal (boolean, default: false)
- attachments (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### support_tickets (Investor/Member Portal)
```sql
- id (bigint, primary key)
- user_id (foreign key → users, nullable)
- investor_account_id (foreign key → investor_accounts, nullable)
- category (string)
- source (enum: investor, member)
- priority (enum: low, medium, high, urgent)
- status (enum: open, in_progress, pending, resolved, closed)
- subject (string)
- description (text)
- assigned_to (foreign key → users, nullable)
- resolved_at (timestamp, nullable)
- closed_at (timestamp, nullable)
- satisfaction_rating (integer, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### ticket_comments (Investor/Member)
```sql
- id (bigint, primary key)
- ticket_id (foreign key → support_tickets)
- user_id (foreign key → users, nullable)
- investor_account_id (foreign key → investor_accounts, nullable)
- author_type (enum: investor, member, support)
- author_name (string, nullable)
- comment (text)
- is_internal (boolean, default: false)
- created_at (timestamp)
- updated_at (timestamp)
```

### Migrations
- `2025_11_29_100000_create_employee_portal_phase2_tables.php`
- `2025_11_29_150000_add_author_fields_to_support_ticket_comments.php`
- `2025_11_29_200000_modify_support_tickets_for_investors.php`

---

## Broadcasting

### Private Channels

#### `support.ticket.{ticketId}` (Employee)
**Purpose:** Real-time chat for employee tickets  
**Authorization:**
- Employee who owns the ticket
- Support staff (admin, hr, support roles)

**Events:**
- `chat.message` - New message sent
- `whisper:typing` - User is typing

#### `investor.support.{ticketId}` (Investor)
**Purpose:** Real-time chat for investor tickets  
**Authorization:**
- Investor who owns the ticket (session-based auth)
- Support staff (admin roles)

**Events:**
- `chat.message` - New message sent
- `whisper:typing` - User is typing

#### `member.support.{ticketId}` (Member)
**Purpose:** Real-time chat for member tickets  
**Authorization:**
- Member who owns the ticket (Laravel auth)
- Support staff (admin roles)

**Events:**
- `chat.message` - New message sent
- `whisper:typing` - User is typing

#### `support.admin`
**Purpose:** Admin notifications for new tickets  
**Authorization:** Admin, HR, Support roles  
**Events:**
- `ticket.created` - New ticket created

### Channel Authorization
**File:** `routes/channels.php`

```php
// Employee ticket chat
Broadcast::channel('support.ticket.{ticketId}', function ($user, $ticketId) {
    $ticket = EmployeeSupportTicket::find($ticketId);
    if (!$ticket) return false;
    
    // Employee can access their own tickets
    $employee = Employee::where('user_id', $user->id)->first();
    if ($employee && $ticket->employee_id === $employee->id) {
        return true;
    }
    
    // Support staff can access all tickets
    return $user->hasRole('support') || $user->hasRole('admin') || $user->hasRole('hr');
});

// Investor ticket chat (supports session-based auth)
Broadcast::channel('investor.support.{ticketId}', function ($user, $ticketId) {
    // Check session-based investor auth
    $investorId = session('investor_id');
    if ($investorId) {
        $investor = InvestorAccount::find($investorId);
        $ticket = SupportTicketModel::find($ticketId);
        if ($investor && $ticket && $ticket->investor_account_id === $investor->id) {
            return ['id' => $investor->id, 'name' => $investor->name, 'type' => 'investor'];
        }
    }
    
    // Support staff can access all tickets
    if ($user && ($user->hasRole('admin') || $user->is_admin)) {
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'support'];
    }
    
    return false;
});

// Member ticket chat
Broadcast::channel('member.support.{ticketId}', function ($user, $ticketId) {
    if (!$user) return false;
    
    $ticket = SupportTicketModel::find($ticketId);
    if (!$ticket) return false;
    
    // Member can access their own tickets
    if ($ticket->user_id === $user->id) {
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'member'];
    }
    
    // Support staff can access all tickets
    if ($user->hasRole('admin') || $user->is_admin) {
        return ['id' => $user->id, 'name' => $user->name, 'type' => 'support'];
    }
    
    return false;
});

// Admin notifications
Broadcast::channel('support.admin', function ($user) {
    return $user->hasRole('support') || $user->hasRole('admin') || $user->hasRole('hr');
});
```

---

## Real-Time Flow

### Employee Sends Message
1. Employee types message in chat interface
2. Message added optimistically to UI
3. POST request to `/employee/portal/support/{ticket}/chat`
4. `SupportTicketService::addComment()` creates comment
5. `LiveChatMessage` event broadcast to `support.ticket.{id}`
6. Admin receives message in real-time via Echo
7. Admin UI updates automatically

### Admin Sends Message
1. Admin types message in live chat
2. Message added optimistically to UI
3. POST request to `/admin/support/{ticket}/chat`
4. `SupportTicketController::sendChatMessage()` creates comment
5. `LiveChatMessage` event broadcast to `support.ticket.{id}`
6. Employee receives message in real-time via Echo
7. Employee UI updates automatically

### New Ticket Created
1. Employee creates new ticket
2. `SupportTicketService::createTicket()` saves ticket
3. `SupportTicketCreated` event broadcast to `support.admin`
4. All online admins receive notification
5. Admin dashboard shows new ticket alert
6. Support stats cache cleared

### Typing Indicators
1. User types in chat input
2. Whisper event sent to ticket channel
3. Other party receives typing event
4. "Typing..." indicator shows for 3 seconds
5. Indicator disappears after timeout

---

## Configuration

### Broadcasting Setup

#### Environment Variables
```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

#### Echo Configuration
**File:** `resources/js/bootstrap.ts`

```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
});
```

#### Queue Worker
```bash
php artisan queue:work --tries=3
```

For production, use Supervisor to keep the queue worker running.

---

## Usage Guide

### For Employees

#### Creating a Support Ticket
1. Navigate to Employee Portal → Support
2. Click "New Ticket"
3. Fill in:
   - Subject
   - Description
   - Category (IT, HR, etc.)
   - Priority (optional)
4. Submit ticket

#### Using Live Chat
1. Open your ticket from the support list
2. Type message in the chat input
3. Press Enter or click Send
4. See real-time responses from support staff
5. View typing indicators when support is responding

### For Admins

#### Accessing Live Support Dashboard
1. Navigate to Admin → Communication → Live Support
2. View active tickets sorted by priority
3. See urgent tickets highlighted at the top
4. Click any ticket to open live chat

#### Responding to Tickets
1. Click on a ticket from the dashboard
2. Use the live chat interface to respond
3. Update ticket status/priority as needed
4. Add internal notes if required
5. Assign to other support staff if needed

#### Monitoring
- Badge count in sidebar shows open tickets
- Dashboard auto-refreshes when new tickets arrive
- Real-time message delivery
- Connection status indicator

---

## Testing

### Manual Testing Steps

#### 1. Employee Side
- [ ] Login as employee
- [ ] Navigate to `/employee/portal/support`
- [ ] Click "New Ticket"
- [ ] Fill form and submit
- [ ] Open ticket to view chat
- [ ] Send a message
- [ ] Verify message appears

#### 2. Admin Side
- [ ] Login as admin
- [ ] Navigate to `/admin/support/dashboard`
- [ ] Verify new ticket appears
- [ ] Click ticket to open live chat
- [ ] Send a response
- [ ] Verify employee receives it

#### 3. Real-time Testing
- [ ] Open employee chat in one browser
- [ ] Open admin chat in another browser (or incognito)
- [ ] Send messages from both sides
- [ ] Verify instant delivery
- [ ] Check typing indicators

### Test Files
- `public/test-live-chat-realtime.html` - Real-time messaging test
- `public/test-support-chat.html` - Support chat API test
- `public/test-chat-api.html` - API endpoint test
- `test-live-chat-messaging.php` - Backend messaging test

### Automated Testing
```php
// Feature test example
public function test_employee_can_send_chat_message()
{
    $employee = Employee::factory()->create();
    $ticket = EmployeeSupportTicket::factory()->create([
        'employee_id' => $employee->id,
    ]);

    $this->actingAs($employee->user)
        ->post(route('employee.portal.support.chat', $ticket), [
            'message' => 'Test message',
        ])
        ->assertSuccessful();

    $this->assertDatabaseHas('employee_support_ticket_comments', [
        'ticket_id' => $ticket->id,
        'content' => 'Test message',
        'author_type' => 'employee',
    ]);
}
```

---

## Troubleshooting

### Messages Not Appearing in Real-time

**Check:**
1. Broadcasting driver configured correctly
2. Pusher credentials valid
3. Queue worker running
4. Echo initialized in frontend
5. Channel authorization working

**Debug:**
```bash
# Check queue jobs
php artisan queue:work --verbose

# Check broadcasting
php artisan tinker
>>> broadcast(new \App\Events\Employee\LiveChatMessage(...));

# Check Echo connection (in browser console)
Echo.connector.pusher.connection.state
```

### Badge Count Not Updating

**Check:**
1. `supportStats` in `HandleInertiaRequests`
2. Cache cleared: `php artisan cache:clear`
3. Sidebar component using `page.props.supportStats`

**Fix:**
```php
// Clear cache manually
cache()->forget('admin_support_stats');
```

### Channel Authorization Failing

**Check:**
1. User has correct role
2. Channel authorization in `routes/channels.php`
3. CSRF token valid
4. Session active

**Debug:**
```bash
# Check route
php artisan route:list | grep broadcasting

# Test authorization
curl -X POST http://localhost/broadcasting/auth \
  -H "Cookie: laravel_session=..." \
  -d "channel_name=private-support.ticket.1"
```

### Page Refreshing on Message Send

**Check:**
1. Button has `type="button"` attribute
2. Event handler has `.prevent` modifier
3. No form wrapping the button
4. JavaScript not throwing errors

**Fix:**
```vue
<!-- Correct implementation -->
<button type="button" @click.prevent="sendMessage">
  Send
</button>
```

---

## Security

### Authorization
✅ Channel authorization prevents unauthorized access  
✅ Employees can only view their own tickets  
✅ Admins can view all tickets  
✅ CSRF protection on all POST requests  
✅ Role-based access control  

### Data Validation
✅ Input validation on all forms  
✅ XSS protection via Vue escaping  
✅ SQL injection prevention via Eloquent  
✅ Message content max 2000 characters  

### Privacy
✅ Internal notes not visible to employees  
✅ Ticket data isolated per employee  
✅ Secure WebSocket connections (TLS)  
✅ Audit trail maintained  

---

## Performance

### Caching
- Support stats cached for 60 seconds
- User auth data cached for 5 minutes
- Reduces database queries

### Broadcasting
- Use queue for event broadcasting
- Prevents blocking HTTP requests
- Improves response time

### Database
- Index on `ticket_number`, `status`, `priority`
- Eager load relationships
- Paginate ticket lists

### Optimizations
- Optimistic UI updates
- Lazy loading of comments
- Connection pooling for WebSockets
- Message batching (planned)

---

## Deployment

### Pre-deployment Checklist
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear caches: `php artisan optimize:clear`
- [ ] Build assets: `npm run build`
- [ ] Test broadcasting in staging
- [ ] Verify queue worker running
- [ ] Configure Supervisor for queue worker
- [ ] Set up SSL for WebSocket connections
- [ ] Configure rate limiting

### Post-deployment
- [ ] Monitor queue jobs
- [ ] Check error logs
- [ ] Test live chat functionality
- [ ] Verify badge counts
- [ ] Monitor Pusher usage

### Monitoring
- [ ] Queue job failures
- [ ] Broadcasting errors
- [ ] Response times
- [ ] User feedback
- [ ] Ticket resolution times

---

## Future Enhancements

### Planned Features
- [ ] File attachments in chat
- [ ] Emoji support
- [ ] Message reactions
- [ ] Canned responses for admins
- [ ] Ticket templates
- [ ] SLA tracking and alerts
- [ ] Customer satisfaction ratings
- [ ] Chat transcripts export
- [ ] Multi-language support
- [ ] Voice/video call integration
- [ ] AI-powered chatbot
- [ ] Screen sharing
- [ ] Co-browsing

### Technical Improvements
- [ ] Redis for broadcasting (instead of Pusher)
- [ ] Message read receipts
- [ ] Message editing/deletion
- [ ] Rich text formatting
- [ ] Message search
- [ ] Message pagination
- [ ] Offline message queuing
- [ ] Push notifications

---

## Related Documentation

- [Employee Portal Implementation](EMPLOYEE_PORTAL_IMPLEMENTATION.md)
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting)
- [Laravel Echo](https://github.com/laravel/echo)
- [Pusher Documentation](https://pusher.com/docs)
- [Inertia.js](https://inertiajs.com)
- [Vue 3 Composition API](https://vuejs.org/guide/extras/composition-api-faq.html)

---

## Support

For questions or issues with the live chat system:
1. Check this documentation first
2. Review troubleshooting section
3. Check Laravel logs: `storage/logs/laravel.log`
4. Check browser console for errors
5. Review broadcasting configuration
6. Test with standalone widget first

---

**End of Documentation**
