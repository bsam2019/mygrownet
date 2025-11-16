# Support Ticket System

**Status**: ✅ Production Ready  
**Completion Date**: November 15, 2025  
**Architecture**: Domain-Driven Design (DDD)

## Overview

A comprehensive support ticket system that allows members to submit support requests and enables admins to manage, assign, and resolve tickets efficiently.

## Features

### Member Features
- ✅ Create support tickets with categories and priorities
- ✅ View all submitted tickets with status tracking
- ✅ Add comments to tickets
- ✅ Real-time status updates
- ✅ Mobile-responsive interface

### Admin Features
- ✅ View all support tickets in dashboard
- ✅ Assign tickets to support staff
- ✅ Update ticket status (Open → In Progress → Waiting → Resolved → Closed)
- ✅ Add public and internal comments
- ✅ Priority and category management
- ✅ Ticket detail view with full history

## Ticket Categories

1. **Technical Support** - Technical issues, bugs, errors
2. **Financial Issue** - Payment, withdrawal, commission issues
3. **Account Management** - Profile, login, password issues
4. **General Inquiry** - General questions and inquiries

## Priority Levels

| Priority | SLA Hours | Color | Use Case |
|----------|-----------|-------|----------|
| Low | 72 | Gray | Non-urgent inquiries |
| Medium | 48 | Blue | Standard issues |
| High | 24 | Amber | Important issues |
| Urgent | 4 | Red | Critical issues requiring immediate attention |

## Ticket Status Workflow

```
Open → In Progress → Waiting → Resolved → Closed
  ↓                                ↑
  └────────────── Reopen ──────────┘
```

### Status Descriptions

- **Open**: New ticket, not yet assigned
- **In Progress**: Ticket assigned and being worked on
- **Waiting**: Waiting for member response
- **Resolved**: Issue resolved, awaiting confirmation
- **Closed**: Ticket closed, no further action needed

## Routes

### Member Routes
```
GET  /mygrownet/support          - List all tickets (add ?mobile=1 for PWA)
GET  /mygrownet/support/create   - Create ticket form (add ?mobile=1 for PWA)
POST /mygrownet/support          - Submit new ticket
GET  /mygrownet/support/{id}     - View ticket details (add ?mobile=1 for PWA)
POST /mygrownet/support/{id}/comment - Add comment
```

### Admin Routes
```
GET  /admin/support              - Ticket dashboard
GET  /admin/support/{id}         - View ticket details
POST /admin/support/{id}/assign  - Assign ticket
POST /admin/support/{id}/status  - Update status
POST /admin/support/{id}/comment - Add comment (public or internal)
```

## Domain Architecture

### Entities
- **Ticket** - Core ticket entity with business logic
- **TicketComment** - Comment entity

### Value Objects
- **TicketId** - Type-safe ticket identifier
- **UserId** - Type-safe user identifier
- **TicketCategory** - Category enum
- **TicketPriority** - Priority enum with SLA hours
- **TicketStatus** - Status enum with workflow logic
- **TicketContent** - Self-validating content (10-5000 chars)

### Domain Services
- **TicketService** - Handles ticket operations and comment management

### Use Cases
- **CreateTicketUseCase** - Create new ticket
- **GetUserTicketsUseCase** - Get tickets for a user
- **GetTicketWithCommentsUseCase** - Get ticket with comments
- **AssignTicketUseCase** - Assign ticket to admin
- **UpdateTicketStatusUseCase** - Update ticket status
- **AddCommentUseCase** - Add comment to ticket

## Business Rules

1. **Ticket Creation**
   - Subject must be 5-200 characters
   - Description must be 10-5000 characters
   - Category is required
   - Priority defaults to "medium" if not specified

2. **Ticket Assignment**
   - Cannot assign closed tickets
   - Assigning a ticket automatically changes status to "In Progress"

3. **Status Updates**
   - Cannot change status of closed tickets (except to reopen)
   - Resolving a ticket records the resolved timestamp
   - Closing requires ticket to be resolved first
   - Reopening clears resolved and closed timestamps

4. **Comments**
   - Cannot add comments to closed tickets
   - Comments must be 1-2000 characters
   - Admins can add internal notes (not visible to members)
   - Member comments on "Waiting" tickets move status to "In Progress"

5. **Ratings**
   - Can only rate resolved or closed tickets
   - Rating must be 1-5 stars

## Database Schema

### support_tickets
```sql
id, user_id, category, priority, status, subject, description,
assigned_to, resolved_at, closed_at, satisfaction_rating,
created_at, updated_at
```

### ticket_comments
```sql
id, ticket_id, user_id, comment, is_internal,
created_at, updated_at
```

## UI Components

### Member Interface
- **Index.vue** - Desktop ticket list with status badges
- **MobileIndex.vue** - Mobile/PWA ticket list with filters
- **Create.vue** - Desktop ticket creation form
- **MobileCreate.vue** - Mobile/PWA ticket creation form
- **Show.vue** - Desktop ticket detail with comments
- **MobileShow.vue** - Mobile/PWA ticket detail with comments

### Admin Interface
- **Index.vue** - Ticket dashboard table
- **Show.vue** - Ticket management with assignment and status controls

## Testing

Run domain tests:
```bash
php scripts/test-support-system.php
```

All tests passing ✅

## Mobile/PWA Support

The support ticket system is fully compatible with the MyGrowNet PWA:

- ✅ Mobile-optimized layouts with touch-friendly interfaces
- ✅ Responsive design for all screen sizes
- ✅ Bottom navigation integration
- ✅ Gradient headers matching app design
- ✅ Smooth transitions and animations
- ✅ Automatic view detection via `?mobile=1` query parameter

## Future Enhancements

- [ ] File attachments for tickets
- [ ] Email notifications for ticket updates
- [ ] Advanced analytics and reporting
- [ ] Bulk ticket actions
- [ ] Satisfaction rating UI
- [ ] Knowledge base integration
- [ ] Auto-responses and templates
- [ ] Ticket escalation rules
- [ ] SLA violation alerts
- [ ] Push notifications for mobile

## Dashboard Integration

### Admin Dashboard
The support ticket system is fully integrated into the admin dashboard with real-time statistics:
- Total tickets count
- Open tickets requiring attention
- In-progress tickets being handled
- Urgent/overdue tickets alert

Stats are displayed in the top section alongside other key metrics.

### Mobile Dashboard
Support tickets are accessible from the mobile dashboard Profile tab:
- Direct link to support ticket list
- Located between Messages and Help & Support
- Touch-optimized navigation

## Integration Points

- ✅ **Admin Dashboard**: Real-time ticket statistics
- ✅ **Mobile Dashboard**: Profile tab navigation
- ✅ **Sidebars**: Both admin and member sidebars
- **Notifications**: Can be integrated with notification system
- **Email**: Can send email updates on status changes
- **User Management**: Links to user profiles
- **Permissions**: Role-based access control

---

**Last Updated**: November 15, 2025
