# Messaging & Support System Specification

## Overview
Two integrated systems to improve member communication and support:
1. **Direct Messaging** - Admin-to-member and member-to-member communication
2. **Support Tickets** - Issue tracking with status updates and resolution workflow

---

## 1. Direct Messaging System ✅ FULLY COMPLETED

**Status**: Production Ready  
**Completion Date**: November 14, 2025

### Features
- ✅ **Admin → Member**: Send direct messages to any member
- ✅ **Member → Member**: Members can message their upline/downline
- ✅ **Member → Admin**: Members can contact support
- ✅ **Notifications**: In-app notifications (notification center integration)
- ✅ **Message History**: View conversation threads
- ✅ **Read Receipts**: Track if message was read
- ✅ **Mobile Support**: Full mobile interface with modals
- ⚠️ **Known Issue**: Clicking notification doesn't open message modal (low priority)

### Database Schema

#### `messages` Table
```sql
- id
- sender_id (user who sent)
- recipient_id (user who receives)
- subject
- body (text)
- is_read (boolean)
- read_at (timestamp)
- parent_id (for threading/replies)
- created_at
- updated_at
```

### User Interface

#### For Members: ✅
- ✅ **Inbox** - View received messages
- ✅ **Sent** - View sent messages
- ✅ **Compose** - Send new message
- ✅ **Reply** - Respond to messages
- ✅ **Mobile Interface** - Full mobile modal experience

#### For Admins: ✅
- ✅ **Individual** - Send to specific member
- ✅ **Message List** - View all messages
- ✅ **Reply** - Respond to member messages
- ❌ **Broadcast** - Send to all members (not implemented)
- ❌ **Targeted** - Send to specific groups (not implemented)
- ❌ **Templates** - Pre-written message templates (not implemented)

### Use Cases
1. ❌ Admin announces platform updates (broadcast not implemented)
2. ✅ Admin contacts member about account issue
3. ✅ Member asks upline for guidance
4. ✅ Member contacts support for help

### Implementation Notes
- **Architecture**: Domain-Driven Design with proper separation of concerns
- **Notification Integration**: Uses SendNotificationUseCase for extensibility
- **Mobile-First**: Full mobile interface with MessagesModal and MessageViewModal
- **Read Tracking**: Automatic read status updates
- **Conversation Threading**: Parent-child message relationships
- **Routes**: Both admin and member routes implemented
- **Security**: Proper authorization checks for message access

---

## 2. Support Ticket System

### Features
- **Submit Ticket**: Members create support requests
- **Categories**: Technical, Financial, Account, General
- **Priority Levels**: Low, Medium, High, Urgent
- **Status Tracking**: Open, In Progress, Waiting, Resolved, Closed
- **Assignment**: Tickets assigned to support staff
- **Comments**: Thread of updates and responses
- **Attachments**: Upload screenshots/documents
- **SLA Tracking**: Response time monitoring
- **Satisfaction Rating**: Members rate resolution

### Database Schema

#### `support_tickets` Table
```sql
- id
- user_id (who submitted)
- category (enum: technical, financial, account, general)
- priority (enum: low, medium, high, urgent)
- status (enum: open, in_progress, waiting, resolved, closed)
- subject
- description (text)
- assigned_to (admin user_id)
- resolved_at
- closed_at
- satisfaction_rating (1-5)
- created_at
- updated_at
```

#### `ticket_comments` Table
```sql
- id
- ticket_id
- user_id (who commented)
- comment (text)
- is_internal (boolean - only visible to admins)
- created_at
- updated_at
```

#### `ticket_attachments` Table
```sql
- id
- ticket_id
- comment_id (optional)
- file_name
- file_path
- file_size
- mime_type
- uploaded_by
- created_at
```

### Workflow

#### Member Side:
1. **Submit Ticket**
   - Choose category
   - Set priority (or auto-assigned)
   - Describe issue
   - Attach files (optional)

2. **Track Progress**
   - View ticket status
   - See admin responses
   - Add comments/updates
   - Upload additional files

3. **Resolution**
   - Receive notification when resolved
   - Rate satisfaction
   - Close ticket or reopen

#### Admin Side:
1. **Ticket Dashboard**
   - View all tickets
   - Filter by status, category, priority
   - Sort by date, priority
   - Search tickets

2. **Ticket Management**
   - Assign to staff member
   - Change status
   - Set priority
   - Add internal notes
   - Respond to member

3. **Analytics**
   - Average resolution time
   - Tickets by category
   - Staff performance
   - Satisfaction ratings

### Status Flow
```
Open → In Progress → Waiting (for member) → Resolved → Closed
                  ↓
              Reopened (if needed)
```

---

## Implementation Plan

### Phase 1: Database & Models (Week 1) ✅ COMPLETED
- [x] Create migrations for messages
- [x] Create Eloquent models (MessageModel)
- [x] Set up relationships
- [x] Domain entities and value objects
- [ ] Create migrations for tickets, comments, attachments (Support System - Next Phase)

### Phase 2: Backend API (Week 1-2) ✅ MESSAGING COMPLETED
- [x] Message domain layer (DDD approach)
- [x] Message repositories and services
- [x] Message use cases (send, receive, list, read)
- [x] Message controllers (send, receive, list, read)
- [x] Form request validation
- [ ] Ticket controllers (create, update, list, assign) - Next Phase
- [ ] Comment controllers (add, list) - Next Phase
- [ ] Attachment handling (upload, download) - Next Phase
- [ ] Notification system integration - Next Phase

### Phase 3: Admin Interface (Week 2)
- [x] Message management dashboard - ✅ COMPLETED (admin can view/send individual messages)
- [x] Broadcast messaging - ✅ COMPLETED (send to all members with optional filters)
- [x] Targeted group messaging - ✅ COMPLETED (filters available in broadcast)
- [x] Message templates - ✅ COMPLETED (CRUD operations for reusable templates)
- [ ] Ticket management dashboard - Next Phase
- [ ] Ticket assignment interface - Next Phase
- [ ] Analytics/reporting - Next Phase
- [ ] Bulk actions - Next Phase

### Phase 4: Member Interface (Week 2-3) ✅ MESSAGING COMPLETED
- [x] Inbox/messaging interface (Index.vue)
- [x] Message viewing interface (Show.vue)
- [x] Reply functionality
- [x] Read/unread status
- [ ] Compose new message modal - Enhancement
- [ ] Ticket submission form - Next Phase
- [ ] Ticket tracking page - Next Phase
- [ ] Mobile-responsive design - Enhancement needed

### Phase 5: Notifications (Week 3)
- [ ] Email notifications
- [ ] In-app notifications
- [ ] SMS notifications (optional)
- [ ] Push notifications (PWA)

### Phase 6: Testing & Launch (Week 3-4)
- [ ] Unit tests
- [ ] Integration tests
- [ ] User acceptance testing
- [ ] Documentation
- [ ] Training materials
- [ ] Production deployment

---

## Technical Architecture

### Messaging System

#### Routes
```php
// Member routes
Route::get('/messages', 'MessageController@index');
Route::get('/messages/{id}', 'MessageController@show');
Route::post('/messages', 'MessageController@store');
Route::post('/messages/{id}/reply', 'MessageController@reply');
Route::patch('/messages/{id}/read', 'MessageController@markAsRead');

// Admin routes
Route::post('/admin/messages/broadcast', 'AdminMessageController@broadcast');
Route::post('/admin/messages/targeted', 'AdminMessageController@targeted');
```

#### Models
```php
class Message extends Model
{
    public function sender(): BelongsTo
    public function recipient(): BelongsTo
    public function parent(): BelongsTo
    public function replies(): HasMany
    public function markAsRead(): void
}
```

### Support Ticket System

#### Routes
```php
// Member routes
Route::get('/support/tickets', 'TicketController@index');
Route::post('/support/tickets', 'TicketController@store');
Route::get('/support/tickets/{id}', 'TicketController@show');
Route::post('/support/tickets/{id}/comments', 'TicketCommentController@store');
Route::post('/support/tickets/{id}/attachments', 'TicketAttachmentController@store');
Route::patch('/support/tickets/{id}/close', 'TicketController@close');
Route::post('/support/tickets/{id}/rate', 'TicketController@rate');

// Admin routes
Route::get('/admin/support/tickets', 'AdminTicketController@index');
Route::patch('/admin/support/tickets/{id}/assign', 'AdminTicketController@assign');
Route::patch('/admin/support/tickets/{id}/status', 'AdminTicketController@updateStatus');
Route::post('/admin/support/tickets/{id}/internal-note', 'AdminTicketController@addInternalNote');
Route::get('/admin/support/analytics', 'AdminTicketController@analytics');
```

#### Models
```php
class SupportTicket extends Model
{
    public function user(): BelongsTo
    public function assignedTo(): BelongsTo
    public function comments(): HasMany
    public function attachments(): HasMany
    public function updateStatus(string $status): void
    public function assignTo(User $admin): void
    public function resolve(): void
    public function close(): void
    public function reopen(): void
}

class TicketComment extends Model
{
    public function ticket(): BelongsTo
    public function user(): BelongsTo
    public function isInternal(): bool
}

class TicketAttachment extends Model
{
    public function ticket(): BelongsTo
    public function comment(): BelongsTo
    public function uploadedBy(): BelongsTo
}
```

---

## UI/UX Design

### Messaging Interface

#### Member Inbox
```
┌─────────────────────────────────────┐
│ Messages                    [Compose]│
├─────────────────────────────────────┤
│ 📧 Admin Support                    │
│    Welcome to MyGrowNet!            │
│    2 hours ago                   ●  │
├─────────────────────────────────────┤
│ 👤 John Doe (Upline)                │
│    Congratulations on your...       │
│    1 day ago                        │
├─────────────────────────────────────┤
│ 📧 System Notification              │
│    Your payment has been...         │
│    3 days ago                       │
└─────────────────────────────────────┘
```

### Support Ticket Interface

#### Member Ticket List
```
┌─────────────────────────────────────┐
│ My Support Tickets      [New Ticket]│
├─────────────────────────────────────┤
│ #1234 - Payment Issue          🟡   │
│ In Progress • Updated 2h ago        │
│ Last response: Admin John           │
├─────────────────────────────────────┤
│ #1233 - Account Question       🟢   │
│ Resolved • Closed 1 day ago         │
│ Rating: ⭐⭐⭐⭐⭐                    │
└─────────────────────────────────────┘
```

#### Admin Ticket Dashboard
```
┌─────────────────────────────────────┐
│ Support Tickets                     │
│ [All] [Open] [In Progress] [Urgent]│
├─────────────────────────────────────┤
│ Filters: Category ▼ Priority ▼     │
├─────────────────────────────────────┤
│ #1234 🔴 Payment not reflecting     │
│ John Doe • 2h ago • Unassigned      │
│ [Assign to me] [View]               │
├─────────────────────────────────────┤
│ #1233 🟡 Cannot login               │
│ Jane Smith • 5h ago • Assigned: You │
│ [Update Status] [View]              │
└─────────────────────────────────────┘
```

---

## Integration Points

### With Existing Systems
1. **Notifications** - Use existing announcement system
2. **User Management** - Link to user profiles
3. **Permissions** - Role-based access (admin, support, member)
4. **Email** - Use existing email service
5. **File Storage** - Use Laravel storage for attachments

### New Dependencies
- File upload handling (already have)
- Rich text editor (TinyMCE or Quill)
- Real-time updates (Laravel Echo - optional)

---

## Security Considerations

1. **Authorization**
   - Members can only view their own messages/tickets
   - Admins can view all messages/tickets
   - Support staff can only view assigned tickets

2. **File Uploads**
   - Validate file types
   - Scan for malware
   - Limit file sizes
   - Store securely

3. **Data Privacy**
   - Encrypt sensitive messages
   - GDPR compliance
   - Data retention policies

4. **Rate Limiting**
   - Prevent spam
   - Limit ticket creation
   - Throttle API requests

---

## Success Metrics

### Messaging
- Message delivery rate
- Response time
- Read rate
- User engagement

### Support Tickets
- Average resolution time
- First response time
- Ticket volume by category
- Satisfaction ratings
- Reopen rate
- Staff performance

---

## Future Enhancements

### Messaging
- [ ] Group messaging
- [ ] Message templates
- [ ] Scheduled messages
- [ ] Message search
- [ ] Archive/delete messages
- [ ] Block users

### Support Tickets
- [ ] Knowledge base integration
- [ ] Auto-responses
- [ ] Ticket escalation
- [ ] SLA automation
- [ ] Customer portal
- [ ] Live chat integration
- [ ] AI-powered suggestions

---

## Cost Estimate

### Development Time
- **Messaging System**: 2-3 weeks
- **Support Tickets**: 2-3 weeks
- **Testing & Polish**: 1 week
- **Total**: 5-7 weeks

### Resources Needed
- 1 Backend Developer
- 1 Frontend Developer
- 1 QA Tester
- 1 UI/UX Designer (part-time)

---

## Next Steps

1. **Review & Approve** this specification
2. **Prioritize Features** - Which to build first?
3. **Create Detailed Tasks** - Break down into tickets
4. **Assign Resources** - Who will build what?
5. **Set Timeline** - When do you need this?
6. **Start Development** - Begin with Phase 1

---

**Questions to Answer:**
1. Should we build messaging or support tickets first?
2. Do you want real-time messaging or async is fine?
3. Should members be able to message each other freely?
4. What categories do you need for support tickets?
5. How many support staff will handle tickets?
6. Do you need SMS notifications or email is enough?

---

**Status:** � Pphase 1 Complete - Messaging System Implemented (DDD)
**Created:** November 12, 2025
**Last Updated:** November 12, 2025
**Version:** 1.1

---

## Implementation Status

### ✅ Completed (Messaging System)

**Domain Layer (DDD Architecture):**
- `app/Domain/Messaging/Entities/Message.php` - Rich domain entity with business logic
- `app/Domain/Messaging/ValueObjects/MessageId.php` - Type-safe message identifier
- `app/Domain/Messaging/ValueObjects/UserId.php` - Type-safe user identifier
- `app/Domain/Messaging/ValueObjects/MessageContent.php` - Self-validating content object
- `app/Domain/Messaging/Services/MessagingService.php` - Domain service for messaging operations
- `app/Domain/Messaging/Repositories/MessageRepository.php` - Repository interface
- `app/Domain/Messaging/Events/MessageSent.php` - Domain event
- `app/Domain/Messaging/Events/MessageRead.php` - Domain event

**Infrastructure Layer:**
- `app/Infrastructure/Persistence/Eloquent/Messaging/MessageModel.php` - Eloquent model
- `app/Infrastructure/Persistence/Eloquent/Messaging/EloquentMessageRepository.php` - Repository implementation
- `database/migrations/2025_11_12_144649_create_messages_table.php` - Database schema

**Application Layer:**
- `app/Application/Messaging/UseCases/SendMessageUseCase.php` - Send message use case
- `app/Application/Messaging/UseCases/GetInboxUseCase.php` - Get inbox use case
- `app/Application/Messaging/UseCases/GetSentMessagesUseCase.php` - Get sent messages use case
- `app/Application/Messaging/UseCases/GetConversationUseCase.php` - Get conversation use case
- `app/Application/Messaging/UseCases/MarkMessageAsReadUseCase.php` - Mark as read use case
- `app/Application/Messaging/DTOs/MessageDTO.php` - Data transfer object
- `app/Application/Messaging/DTOs/SendMessageDTO.php` - Send message DTO

**Presentation Layer:**
- `app/Http/Controllers/MyGrowNet/MessageController.php` - Message controller
- `app/Http/Requests/MyGrowNet/SendMessageRequest.php` - Form request validation
- `resources/js/Pages/MyGrowNet/Messages/Index.vue` - Inbox/Sent interface
- `resources/js/Pages/MyGrowNet/Messages/Show.vue` - Conversation view with reply
- `routes/web.php` - Routes configured

**Configuration:**
- `app/Providers/MessagingServiceProvider.php` - Service provider for DI
- `bootstrap/providers.php` - Provider registered

**Testing:**
- `scripts/test-messaging-system.php` - Domain and use case tests (all tests passing ✅)
- `scripts/test-messaging-integration.php` - Full integration tests (all tests passing ✅)

**Dashboard Integration:**
- `app/Http/Controllers/MyGrowNet/DashboardController.php` - Added messaging data to member dashboard
- `app/Http/Controllers/Admin/DashboardController.php` - Added messaging data to admin dashboard
- `resources/js/Components/Dashboard/MessagingWidget.vue` - Dashboard widget for recent messages
- `resources/js/components/MyGrowNetSidebar.vue` - Added Messages link with unread count badge (member sidebar)
- `resources/js/components/CustomAdminSidebar.vue` - Added Communication section with Messages, Broadcast, and Support Tickets (admin sidebar)

### 📋 Next Phase (Support Ticket System)
- Support ticket domain layer
- Ticket categories and priorities
- Ticket status workflow
- Comment system
- Attachment handling
- Admin ticket management
- Analytics and reporting
