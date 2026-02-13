# CMS Approval Workflows System

**Last Updated:** February 10, 2026  
**Status:** Production Ready

## Overview

The Approval Workflows module provides a comprehensive multi-level approval system for expenses, quotations, and payments. It features configurable approval chains, role-based approvers, real-time notifications, and complete audit trails.

## Key Features

### 1. Multi-Level Approval Chains
- Define approval workflows with multiple sequential steps
- Each step can require approval from specific roles (owner, manager, accountant)
- Configurable number of approval levels (1-10+)
- Automatic progression through approval levels

### 2. Configurable Thresholds
- Amount-based approval routing
- Different chains for different amount ranges
- Priority-based chain selection
- Per-entity-type configuration (expense, quotation, payment)

### 3. Role-Based Approvers
- Assign approval steps to roles rather than individuals
- Automatic approver assignment based on role
- Flexible role hierarchy
- Support for multiple approvers per role

### 4. Real-Time Notifications
- Instant notifications to approvers via Reverb
- Email notifications for approval requests
- Notifications on approval/rejection
- Requester notifications on completion

### 5. Complete Audit Trail
- Track all approval actions
- Record approver comments
- Timestamp all actions
- Full history per request

## Database Schema

### Tables

**cms_approval_requests**
- Stores approval requests for entities
- Links to approvable entity (polymorphic)
- Tracks current approval level and status
- Records requester and submission time

**cms_approval_steps**
- Individual steps in an approval workflow
- Links to specific approver or role
- Records approval/rejection with comments
- Timestamps all actions

**cms_approval_chains**
- Defines approval workflow templates
- Configurable amount ranges
- JSON-stored approval steps
- Priority-based matching

## Implementation

### Backend Architecture

#### ApprovalWorkflowService
**Location:** `app/Domain/CMS/Core/Services/ApprovalWorkflowService.php`

**Key Methods:**
```php
// Create approval request
createApprovalRequest(
    int $companyId,
    string $entityType,
    int $entityId,
    float $amount,
    int $requestedBy,
    ?string $notes = null
): ApprovalRequestModel

// Approve current step
approveStep(
    int $requestId,
    int $approverId,
    ?string $comments = null
): ApprovalRequestModel

// Reject request
rejectStep(
    int $requestId,
    int $approverId,
    string $reason
): ApprovalRequestModel

// Cancel request
cancelRequest(
    int $requestId,
    int $userId,
    string $reason
): ApprovalRequestModel

// Get pending approvals for user
getPendingApprovalsForUser(int $userId): array

// Check if approval required
requiresApproval(
    int $companyId,
    string $entityType,
    float $amount
): bool
```

#### Models

**ApprovalRequestModel**
- Main approval request entity
- Polymorphic relationship to approvable entities
- Has many approval steps
- Status tracking (pending, approved, rejected, cancelled)

**ApprovalStepModel**
- Individual approval step
- Belongs to approval request
- Links to approver (user or role)
- Status tracking per step

**ApprovalChainModel**
- Approval workflow template
- Amount range matching
- JSON-stored step configuration
- Priority-based selection

### Frontend Implementation

#### Approvals Index Page
**Location:** `resources/js/Pages/CMS/Approvals/Index.vue`

**Features:**
- Pending approvals section (requires user action)
- All requests table (company-wide history)
- Quick approve/reject actions
- View details navigation
- Status badges and visual indicators

### Routes

```php
// Approval Management
GET    /cms/approvals                    - List approvals
GET    /cms/approvals/{id}               - View approval details
POST   /cms/approvals/{id}/approve       - Approve step
POST   /cms/approvals/{id}/reject        - Reject request
POST   /cms/approvals/{id}/cancel        - Cancel request

// Approval Chains Management
GET    /cms/approvals/chains/manage      - Manage chains
POST   /cms/approvals/chains             - Create chain
PUT    /cms/approvals/chains/{id}        - Update chain
DELETE /cms/approvals/chains/{id}        - Delete chain
```

## Approval Chain Configuration

### Chain Structure

```json
{
  "name": "Medium Expense Approval",
  "entity_type": "expense",
  "min_amount": 500.01,
  "max_amount": 5000.00,
  "approval_steps": [
    {"level": 1, "role": "manager"},
    {"level": 2, "role": "owner"}
  ],
  "is_active": true,
  "priority": 2
}
```

### Default Chains

The system includes pre-configured approval chains:

**Expenses:**
1. Small (K0 - K500): Manager only
2. Medium (K500 - K5,000): Manager → Owner
3. Large (K5,000+): Manager → Accountant → Owner

**Quotations:** (Disabled by default)
1. Standard (K0 - K10,000): Manager only
2. High-Value (K10,000+): Manager → Owner

**Payments:** (Disabled by default)
1. Standard (K0 - K20,000): Manager only
2. Large (K20,000+): Accountant → Owner

### Chain Matching Logic

1. Filter chains by entity type
2. Filter by active status
3. Check amount range match
4. Select highest priority match
5. Create approval request with matched chain

## Usage

### Creating an Approval Request

```php
use App\Domain\CMS\Core\Services\ApprovalWorkflowService;

$approvalService = app(ApprovalWorkflowService::class);

// Check if approval required
$requiresApproval = $approvalService->requiresApproval(
    companyId: $companyId,
    entityType: 'expense',
    amount: 750.00
);

if ($requiresApproval) {
    // Create approval request
    $request = $approvalService->createApprovalRequest(
        companyId: $companyId,
        entityType: 'expense',
        entityId: $expense->id,
        amount: $expense->amount,
        requestedBy: $userId,
        notes: 'Urgent office supplies purchase'
    );
}
```

### Approving a Request

```php
// Approve current step
$approvalService->approveStep(
    requestId: $requestId,
    approverId: $approverId,
    comments: 'Approved - within budget'
);
```

### Rejecting a Request

```php
// Reject request
$approvalService->rejectStep(
    requestId: $requestId,
    approverId: $approverId,
    reason: 'Exceeds monthly budget allocation'
);
```

### Getting Pending Approvals

```php
// Get approvals pending for current user
$pendingApprovals = $approvalService->getPendingApprovalsForUser($userId);
```

## Integration with Other Modules

### Expense Module

Expenses automatically create approval requests when:
- Approval is enabled in settings
- Amount exceeds auto-approve threshold
- Matching approval chain exists

**Integration Points:**
- `ExpenseController::store()` - Creates approval request
- `ExpenseModel` - Polymorphic relationship to approval requests
- Expense status updated on approval/rejection

### Quotation Module

Quotations can require approval based on:
- Settings configuration
- Amount thresholds
- Approval chain configuration

**Integration Points:**
- `QuotationController::store()` - Creates approval request
- `QuotationModel` - Polymorphic relationship
- Quotation status updated on approval

### Payment Module

Payments can require approval for:
- Large payment amounts
- Specific payment methods
- Configurable thresholds

**Integration Points:**
- `PaymentController::store()` - Creates approval request
- `PaymentModel` - Polymorphic relationship
- Payment processing on approval

## Notifications

### Approval Requested Notification
**Trigger:** When approval request is created or moves to next level  
**Recipients:** Users with required role for current step  
**Channels:** Database, Broadcast, Email

**Data:**
- Request type and amount
- Requester name
- Approval level
- Link to review

### Approval Action Notification
**Trigger:** When request is approved or rejected  
**Recipients:** Original requester  
**Channels:** Database, Broadcast, Email

**Data:**
- Action taken (approved/rejected)
- Final status
- Rejection reason (if applicable)
- Link to view details

## Workflow States

### Request Status Flow

```
pending → approved (all steps approved)
pending → rejected (any step rejected)
pending → cancelled (requester cancels)
```

### Step Status Flow

```
pending → approved (approver approves)
pending → rejected (approver rejects)
pending → skipped (step bypassed)
```

## Security & Permissions

### Approver Verification
- Only assigned approvers can action steps
- Role-based access control
- Company-scoped requests
- Audit trail for all actions

### Requester Rights
- Can view own requests
- Can cancel pending requests
- Cannot approve own requests
- Notified of all status changes

## Best Practices

### Approval Chain Design

1. **Keep chains simple** - 2-3 levels maximum for most workflows
2. **Use amount thresholds** - Different chains for different amounts
3. **Role-based assignment** - Assign to roles, not individuals
4. **Set priorities** - Higher priority for more specific chains
5. **Test thoroughly** - Verify chain matching logic

### Approver Management

1. **Assign backup approvers** - Multiple users per role
2. **Set clear expectations** - Define approval timeframes
3. **Provide context** - Include notes with requests
4. **Monitor pending** - Regular review of pending approvals
5. **Audit regularly** - Review approval history

### Performance Optimization

1. **Index approval tables** - Ensure proper database indexes
2. **Cache chain lookups** - Cache active chains per company
3. **Batch notifications** - Group notifications when possible
4. **Archive old requests** - Move completed requests to archive
5. **Monitor queue** - Ensure notification queue is processing

## Troubleshooting

### Approval Not Required

**Problem:** Entity doesn't require approval when expected

**Solutions:**
1. Check settings - Verify approval is enabled
2. Check thresholds - Verify amount exceeds threshold
3. Check chains - Ensure active chain exists for amount
4. Check entity type - Verify entity type is supported

### Approver Not Notified

**Problem:** Approver doesn't receive notification

**Solutions:**
1. Check role assignment - Verify user has required role
2. Check notification settings - Verify email notifications enabled
3. Check queue - Ensure queue worker is running
4. Check Reverb - Verify WebSocket connection
5. Check user email - Verify valid email address

### Cannot Approve

**Problem:** User cannot approve assigned request

**Solutions:**
1. Check assignment - Verify user is assigned approver
2. Check status - Verify request is still pending
3. Check level - Verify it's the current approval level
4. Check permissions - Verify user has approval permission

### Chain Not Matching

**Problem:** No approval chain found for request

**Solutions:**
1. Check amount range - Verify amount falls within chain range
2. Check entity type - Verify chain exists for entity type
3. Check active status - Verify chain is active
4. Check priority - Verify chain priority is set correctly
5. Create chain - Add missing approval chain

## Future Enhancements

### Potential Additions

1. **Parallel Approvals** - Multiple approvers at same level
2. **Conditional Routing** - Dynamic approval paths based on conditions
3. **Delegation** - Temporary approval delegation
4. **Escalation** - Auto-escalate overdue approvals
5. **Approval Templates** - Pre-defined approval templates
6. **Bulk Actions** - Approve/reject multiple requests
7. **Mobile App** - Mobile approval interface
8. **Approval Analytics** - Approval time metrics and reporting
9. **Custom Fields** - Additional fields per entity type
10. **Integration API** - External system integration

## Related Documentation

- [CMS Implementation Progress](./IMPLEMENTATION_PROGRESS.md)
- [Settings & Configuration](./SETTINGS_CONFIGURATION.md)
- [Notifications System](./NOTIFICATIONS.md)
- [Expense Module](./COMPLETE_FEATURE_SPECIFICATION.md)

## Changelog

### February 10, 2026
- Initial implementation of approval workflows
- Multi-level approval chains
- Role-based approvers
- Real-time notifications
- Approval request management UI
- Default approval chains seeder
- Complete documentation created
