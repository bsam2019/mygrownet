# Live Chat Support System - Integration Complete

**Last Updated:** November 29, 2025  
**Status:** ✅ Production Ready

## Overview

The Live Chat Support System enables real-time communication between employees and support staff through WebSocket-powered chat. The system integrates seamlessly with the existing Employee Portal and Admin Dashboard.

---

## Architecture

### Real-Time Communication Stack
- **Broadcasting**: Laravel Echo + Pusher/Socket.io
- **Frontend**: Vue 3 with Composition API
- **Backend**: Laravel Broadcasting Events
- **Channels**: Private channels with authorization

### Key Components

#### Backend
1. **Controllers**
   - `Admin\SupportTicketController` - Admin support management
   - `Employee\PortalController` - Employee ticket management

2. **Events**
   - `LiveChatMessage` - Real-time message broadcasting
   - `SupportTicketCreated` - New ticket notifications

3. **Services**
   - `SupportTicketService` - Business logic for tickets
   - `NotificationService` - Employee notifications

4. **Models**
   - `EmployeeSupportTicket` - Ticket data
   - `EmployeeSupportTicketComment` - Messages/comments

#### Frontend
1. **Admin Pages**
   - `Admin/Support/Dashboard.vue` - Live support dashboard
   - `Admin/Support/LiveChat.vue` - Full-screen chat interface
   - `Admin/Support/Index.vue` - All tickets list
   - `Admin/Support/Show.vue` - Ticket details

2. **Employee Pages**
   - `Employee/Portal/Support/Index.vue` - Ticket list
   - `Employee/Portal/Support/Show.vue` - Ticket chat view
   - `Employee/Portal/Support/Create.vue` - New ticket form

3. **Components**
   - `Employee/LiveChatWidget.vue` - Floating chat widget
   - `AdminSidebar.vue` - Admin navigation with badge

---

## Features

### For Employees
✅ Create support tickets with categories and priorities  
✅ Real-time chat with support staff  
✅ View ticket history and status  
✅ Receive instant responses  
✅ Floating chat widget for quick access  
✅ Typing indicators  
✅ Message timestamps  

### For Admins/Support Staff
✅ Live support dashboard with active tickets  
✅ Real-time message notifications  
✅ Priority-based ticket sorting  
✅ Urgent ticket alerts  
✅ Full chat interface  
✅ Ticket assignment  
✅ Status management  
✅ Internal notes (not visible to employees)  
✅ Badge count in sidebar  

---

## Routes

### Admin Routes
```php
// Live Support Dashboard
GET  /admin/support/dashboard

// All Tickets List
GET  /admin/support

// Ticket Details
GET  /admin/support/{ticket}

// Live Chat Interface
GET  /admin/support/{ticket}/live-chat

// Send Message
POST /admin/support/{ticket}/chat

// Update Ticket
PATCH /admin/support/{ticket}

// Add Comment
POST /admin/support/{ticket}/comment
```

### Employee Routes
```php
// Ticket List
GET  /employee/portal/support

// Create Ticket
GET  /employee/portal/support/create
POST /employee/portal/support

// Ticket Details with Chat
GET  /employee/portal/support/{ticket}

// Send Message
POST /employee/portal/support/{ticket}/comment
POST /employee/portal/support/{ticket}/chat

// Quick Chat (creates ticket)
POST /employee/portal/support/quick-chat
```

---

## Broadcasting Channels

### Private Channels

#### `support.ticket.{ticketId}`
**Purpose:** Real-time chat for specific ticket  
**Authorization:** Employee who owns ticket OR support staff  
**Events:**
- `chat.message` - New message sent
- `whisper:typing` - User is typing

#### `support.admin`
**Purpose:** Admin notifications for new tickets  
**Authorization:** Users with admin/support/hr roles  
**Events:**
- `ticket.created` - New ticket notification

---

## Database Schema

### `employee_support_tickets`
```sql
- id
- employee_id (FK)
- ticket_number (unique)
- subject
- description
- category (enum)
- priority (enum: low, medium, high, urgent)
- status (enum: open, in_progress, pending, resolved, closed)
- assigned_to (FK employees, nullable)
- resolved_at (nullable)
- timestamps
```

### `employee_support_ticket_comments`
```sql
- id
- ticket_id (FK)
- author_id (FK employees, nullable)
- author_type (enum: employee, support)
- content (text)
- is_internal (boolean)
- attachments (json, nullable)
- timestamps
```

---

## Usage Examples

### Employee: Create and Chat
```typescript
// 1. Create ticket
router.post('/employee/portal/support', {
  subject: 'Login Issue',
  description: 'Cannot access my account',
  category: 'it',
  priority: 'high'
});

// 2. Send message in chat
router.post(`/employee/portal/support/${ticketId}/comment`, {
  content: 'I tried resetting my password but still cannot login'
});
```

### Admin: Respond to Ticket
```typescript
// 1. View live dashboard
router.get('/admin/support/dashboard');

// 2. Open live chat
router.get(`/admin/support/${ticketId}/live-chat`);

// 3. Send response
router.post(`/admin/support/${ticketId}/chat`, {
  message: 'Let me check your account status...'
});

// 4. Update ticket status
router.patch(`/admin/support/${ticketId}`, {
  status: 'in_progress',
  assigned_to: employeeId
});
```

---

## Real-Time Events

### Broadcasting a Message
```php
// Backend (automatic in SupportTicketService)
broadcast(new LiveChatMessage(
    ticketId: $ticket->id,
    senderId: $employee->id,
    senderName: $employee->full_name,
    senderType: 'employee', // or 'support'
    message: $content,
    sentAt: now()->toISOString()
))->toOthers();
```

### Listening for Messages
```typescript
// Frontend (automatic in chat components)
echo.private(`support.ticket.${ticketId}`)
    .listen('.chat.message', (data) => {
        messages.value.push({
            content: data.message,
            author_type: data.sender_type,
            created_at: data.sent_at,
            author: { 
                id: data.sender_id, 
                full_name: data.sender_name 
            }
        });
    });
```

---

## Sidebar Integration

### Admin Sidebar Badge
The Communication section shows a badge with the count of open tickets:

```vue
<span v-if="item.title === 'Live Support' && page.props.supportStats?.open > 0"
    class="ml-auto inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
    {{ page.props.supportStats.open }}
</span>
```

### Data Source
```php
// HandleInertiaRequests.php
$supportStats = cache()->remember('admin_support_stats', 60, function () {
    return [
        'open' => EmployeeSupportTicket::where('status', 'open')->count(),
        'in_progress' => EmployeeSupportTicket::where('status', 'in_progress')->count(),
        'urgent' => EmployeeSupportTicket::where('priority', 'urgent')
            ->whereIn('status', ['open', 'in_progress'])->count(),
    ];
});
```

---

## Configuration

### Broadcasting Setup
Ensure your `.env` has broadcasting configured:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

### Queue Configuration
For production, use a queue driver:

```env
QUEUE_CONNECTION=redis
```

Run queue worker:
```bash
php artisan queue:work --tries=3
```

---

## Testing

### Manual Testing Checklist

#### Employee Side
- [ ] Create a new support ticket
- [ ] View ticket list
- [ ] Open ticket and see chat interface
- [ ] Send a message
- [ ] Receive real-time response from admin
- [ ] See typing indicator when admin is typing
- [ ] View message timestamps

#### Admin Side
- [ ] View live support dashboard
- [ ] See new ticket notification
- [ ] Open ticket in live chat
- [ ] Send message to employee
- [ ] See employee's messages in real-time
- [ ] Update ticket status
- [ ] Assign ticket to support staff
- [ ] Add internal note
- [ ] See badge count in sidebar

### Test Scenarios

**Scenario 1: New Ticket Flow**
1. Employee creates ticket
2. Admin receives notification on dashboard
3. Admin opens live chat
4. Both parties exchange messages in real-time
5. Admin resolves ticket

**Scenario 2: Multiple Tickets**
1. Multiple employees create tickets
2. Dashboard shows all active tickets
3. Urgent tickets appear at top
4. Admin can switch between chats
5. Each chat maintains separate state

**Scenario 3: Offline Handling**
1. Employee sends message while admin offline
2. Message is stored in database
3. Admin sees message when they come online
4. Admin responds
5. Employee receives response

---

## Performance Considerations

### Caching
- Support stats cached for 60 seconds
- Cache cleared when new ticket created
- Reduces database queries on sidebar render

### Broadcasting
- Uses `toOthers()` to prevent echo
- Optimistic UI updates for instant feedback
- Messages queued for reliability

### Database
- Indexes on `status`, `priority`, `employee_id`
- Eager loading of relationships
- Pagination for ticket lists

---

## Security

### Authorization
- Private channels require authentication
- Employees can only access their own tickets
- Support staff can access all tickets
- Channel authorization in `routes/channels.php`

### Input Validation
- Message content max 2000 characters
- XSS protection via Vue escaping
- CSRF token on all POST requests

### Data Privacy
- Internal notes not visible to employees
- Attachments stored securely
- Audit trail of all actions

---

## Troubleshooting

### Messages Not Appearing
1. Check broadcasting driver is configured
2. Verify queue worker is running
3. Check browser console for WebSocket errors
4. Verify channel authorization

### Badge Count Not Updating
1. Clear cache: `php artisan cache:clear`
2. Check `supportStats` in Inertia props
3. Verify middleware is sharing data

### Typing Indicator Not Working
1. Check `listenForWhisper` is set up
2. Verify private channel connection
3. Check Echo configuration

---

## Future Enhancements

### Planned Features
- [ ] File attachments in chat
- [ ] Emoji support
- [ ] Message reactions
- [ ] Ticket templates
- [ ] Canned responses
- [ ] Chat transcripts export
- [ ] Multi-language support
- [ ] Mobile app integration
- [ ] Video/voice call integration
- [ ] AI-powered auto-responses

### Performance Improvements
- [ ] Message pagination in chat
- [ ] Lazy loading of old messages
- [ ] WebSocket connection pooling
- [ ] Redis for presence channels

---

## Maintenance

### Regular Tasks
- Monitor queue worker health
- Review ticket resolution times
- Archive old closed tickets
- Update canned responses
- Train support staff

### Monitoring
- Track average response time
- Monitor open ticket count
- Alert on urgent tickets
- Log WebSocket errors

---

## Related Documentation
- `EMPLOYEE_PORTAL_IMPLEMENTATION.md` - Employee portal overview
- `docs/POINTS_SYSTEM_SPECIFICATION.md` - Points system integration
- Laravel Broadcasting: https://laravel.com/docs/broadcasting
- Laravel Echo: https://laravel.com/docs/broadcasting#client-side-installation

---

## Support

For issues or questions:
- Check troubleshooting section above
- Review Laravel logs: `storage/logs/laravel.log`
- Check browser console for errors
- Contact development team

---

**Integration Status:** ✅ Complete and Production Ready
