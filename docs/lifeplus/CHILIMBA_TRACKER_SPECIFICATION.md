# Chilimba (Village Banking) Tracker - Technical Specification

**Last Updated:** December 17, 2025  
**Status:** Development Complete  
**Version:** 1.1

---

## Implementation Status

✅ **IMPLEMENTED** - December 17, 2025

### Backend
- Database migration with 9 tables (groups, members, contributions, payouts, loans, loan_payments, meetings, audit_log, edit_requests)
- Eloquent models for all entities
- ChilimbaService with full business logic
- ChilimbaController with all endpoints
- Routes registered in `routes/lifeplus.php`

### Frontend
- `Index.vue` - Groups list with create modal
- `Show.vue` - Group detail with 6 tabs (Overview, Contributions, Payouts, Loans, Members, Audit)
- Modals for adding contributions, members, and loans
- Secretary-specific features for managing all members

### Files Created
- `database/migrations/2025_12_17_000001_create_lifeplus_chilimba_tables.php`
- `app/Infrastructure/Persistence/Eloquent/LifePlusChilimba*.php` (8 models)
- `app/Domain/LifePlus/Services/ChilimbaService.php`
- `app/Http/Controllers/LifePlus/ChilimbaController.php`
- `resources/js/pages/LifePlus/Money/Chilimba/Index.vue`
- `resources/js/pages/LifePlus/Money/Chilimba/Show.vue`

### Access
Navigate to: LifePlus → Money → Chilimba card

---

## 1. Overview

The Chilimba Tracker is a feature within LifePlus Money tab that helps users manage their participation in traditional Zambian village banking groups (chilimba). It tracks contributions, loans, payouts, and meeting schedules.

### What is Chilimba?

Chilimba (also called village banking or rotating savings) is a traditional Zambian savings system where:
- A group of people (typically 10-30 members) meet regularly (weekly/monthly)
- Each member contributes a fixed amount
- The total pot is given to one member each cycle
- Members take turns receiving the payout
- Sometimes includes a loan system with interest

**Example:**
- 20 members contribute K100 each per month
- Total pot = K2,000
- Each member receives K2,000 once during the 20-month cycle
- Some groups allow loans between cycles at 10-20% interest

---

## 2. User Stories

### As a Chilimba Member, I want to:
1. Track which groups I belong to
2. Record my monthly contributions
3. Know when it's my turn to receive the payout
4. Track loans I've taken from the group
5. Get reminders for meeting days
6. See my total contributions vs what I've received
7. Track interest on loans
8. View group member list
9. Record meeting decisions

### As a Group Secretary, I want to:
1. Manage the member list
2. Record all contributions (for all members)
3. Track who has received payouts
4. Manage loan applications
5. Send reminders to members
6. Generate reports for meetings
7. View contribution status for all members
8. See who is behind on payments
9. Record contributions on behalf of members
10. Verify and approve member-recorded contributions

---

## 3. Core Features

### 3.1 Group Management

#### Create/Join a Group
```
Fields:
- Group Name (e.g., "Chilenje Ladies Chilimba")
- Meeting Frequency (Weekly, Bi-weekly, Monthly)
- Meeting Day (e.g., "Every 1st Saturday")
- Meeting Time (e.g., "14:00")
- Meeting Location (e.g., "Mrs. Banda's house")
- Contribution Amount (e.g., K100)
- Total Members (e.g., 20)
- Start Date
- My Role (Member, Secretary, Treasurer)
```

#### Group Dashboard
Shows:
- Next meeting date and countdown
- My total contributions to date
- My position in payout queue
- Outstanding loans
- Group balance (if secretary)

### 3.2 Contribution Tracking

#### Record Contribution
```
Fields:
- Date (auto-filled with today)
- Amount (pre-filled with standard contribution)
- Payment Method (Cash, Mobile Money)
- Receipt Number (optional)
- Notes (optional)
```

#### Contribution History
- List of all my contributions
- Filter by date range
- Total contributed
- Missed contributions highlighted
- Export to PDF/Excel

#### Contribution Reminders
- Notification 2 days before meeting
- SMS reminder on meeting day
- Mark as paid to stop reminders

### 3.3 Payout Management

#### Payout Queue
Shows:
- List of all members
- Who has received payout (✓)
- Who is next in line
- My position in queue
- Expected payout date

#### Record Payout Received
```
Fields:
- Date Received
- Amount Received
- Received From (Group name)
- Notes
```

#### Payout History
- All payouts I've received
- Date and amount
- Total received from all groups

### 3.4 Loan System

#### Request Loan
```
Fields:
- Loan Amount
- Purpose (optional)
- Requested Date
- Proposed Repayment Date
- Interest Rate (group standard)
```

#### Loan Tracking
Shows:
- Active loans
- Loan amount
- Interest accrued
- Total to repay
- Due date
- Payment schedule

#### Loan Repayment
```
Fields:
- Payment Date
- Amount Paid
- Remaining Balance
- Payment Method
```

#### Loan Calculator
- Input: Loan amount, interest rate, duration
- Output: Total repayment, monthly installments

### 3.5 Meeting Management

#### Meeting Schedule
- Calendar view of all meetings
- Upcoming meetings highlighted
- Past meetings marked
- Sync with phone calendar

#### Meeting Minutes
```
Fields:
- Meeting Date
- Attendees (checklist)
- Contributions Collected
- Payout Given To
- Loans Approved
- Decisions Made
- Next Meeting Date
```

#### Attendance Tracking
- Mark who attended
- Track attendance rate
- Penalties for missed meetings (if applicable)

---

## 4. User Interface Design

### 4.1 Navigation
```
LifePlus → Money Tab → Chilimba
```

### 4.2 Main Screen (Chilimba Dashboard)

```
┌─────────────────────────────────────┐
│  Chilimba Groups                    │
├─────────────────────────────────────┤
│                                     │
│  [+ Add New Group]                  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Chilenje Ladies Chilimba    │   │
│  │ Next Meeting: Dec 20, 2:00pm│   │
│  │ My Contributions: K1,200    │   │
│  │ Position in Queue: 5 of 20  │   │
│  │ [View Details]              │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Kanyama Workers Group       │   │
│  │ Next Meeting: Dec 25, 10:00am│  │
│  │ My Contributions: K800      │   │
│  │ Active Loan: K500           │   │
│  │ [View Details]              │   │
│  └─────────────────────────────┘   │
│                                     │
└─────────────────────────────────────┘
```

### 4.3 Group Detail Screen

```
┌─────────────────────────────────────┐
│  ← Chilenje Ladies Chilimba         │
├─────────────────────────────────────┤
│  Tabs: [Overview] [Contributions]   │
│        [Payouts] [Loans] [Members]  │
├─────────────────────────────────────┤
│  OVERVIEW                           │
│                                     │
│  Next Meeting                       │
│  📅 Saturday, Dec 20, 2025          │
│  🕐 2:00 PM                         │
│  📍 Mrs. Banda's house              │
│  [Add to Calendar] [Get Directions] │
│                                     │
│  My Summary                         │
│  💰 Total Contributed: K1,200       │
│  📊 Position in Queue: 5 of 20      │
│  📅 Expected Payout: March 2026     │
│  💵 Expected Amount: K2,000         │
│                                     │
│  Quick Actions                      │
│  [Record Contribution]              │
│  [Request Loan]                     │
│  [View Meeting Minutes]             │
│                                     │
└─────────────────────────────────────┘
```

### 4.4 Record Contribution Screen

```
┌─────────────────────────────────────┐
│  ← Record Contribution              │
├─────────────────────────────────────┤
│                                     │
│  Group: Chilenje Ladies Chilimba   │
│                                     │
│  Date                               │
│  [Dec 17, 2025        ▼]           │
│                                     │
│  Amount (K)                         │
│  [100.00                ]           │
│  Standard: K100                     │
│                                     │
│  Payment Method                     │
│  ○ Cash                             │
│  ○ Mobile Money                     │
│                                     │
│  Receipt Number (Optional)          │
│  [                      ]           │
│                                     │
│  Notes (Optional)                   │
│  [                      ]           │
│                                     │
│  [Cancel]  [Save Contribution]      │
│                                     │
└─────────────────────────────────────┘
```

---

## 5. Database Schema

### 5.1 Tables

#### `lifeplus_chilimba_groups`
```sql
CREATE TABLE lifeplus_chilimba_groups (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    meeting_frequency ENUM('weekly', 'bi-weekly', 'monthly') NOT NULL,
    meeting_day VARCHAR(50),
    meeting_time TIME,
    meeting_location TEXT,
    contribution_amount DECIMAL(10,2) NOT NULL,
    total_members INT NOT NULL,
    start_date DATE NOT NULL,
    user_role ENUM('member', 'secretary', 'treasurer') DEFAULT 'member',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active)
);
```

#### `lifeplus_chilimba_contributions`
```sql
CREATE TABLE lifeplus_chilimba_contributions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    contribution_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'mobile_money') DEFAULT 'cash',
    receipt_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES lifeplus_chilimba_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_group_date (group_id, contribution_date),
    INDEX idx_user_group (user_id, group_id)
);
```

#### `lifeplus_chilimba_payouts`
```sql
CREATE TABLE lifeplus_chilimba_payouts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    payout_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    cycle_number INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES lifeplus_chilimba_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_group_cycle (group_id, cycle_number)
);
```

#### `lifeplus_chilimba_loans`
```sql
CREATE TABLE lifeplus_chilimba_loans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    loan_amount DECIMAL(10,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    purpose TEXT,
    status ENUM('pending', 'approved', 'active', 'paid', 'defaulted') DEFAULT 'pending',
    total_repaid DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES lifeplus_chilimba_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_group_status (group_id, status)
);
```

#### `lifeplus_chilimba_loan_payments`
```sql
CREATE TABLE lifeplus_chilimba_loan_payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    loan_id BIGINT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'mobile_money') DEFAULT 'cash',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (loan_id) REFERENCES lifeplus_chilimba_loans(id) ON DELETE CASCADE,
    INDEX idx_loan_date (loan_id, payment_date)
);
```

#### `lifeplus_chilimba_members`
```sql
CREATE TABLE lifeplus_chilimba_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    position_in_queue INT,
    has_received_payout BOOLEAN DEFAULT FALSE,
    payout_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES lifeplus_chilimba_groups(id) ON DELETE CASCADE,
    INDEX idx_group_queue (group_id, position_in_queue)
);
```

#### `lifeplus_chilimba_meetings`
```sql
CREATE TABLE lifeplus_chilimba_meetings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    meeting_date DATE NOT NULL,
    attendees JSON,
    total_collected DECIMAL(10,2),
    payout_given_to VARCHAR(255),
    loans_approved JSON,
    decisions TEXT,
    next_meeting_date DATE,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES lifeplus_chilimba_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_group_date (group_id, meeting_date)
);
```

---

## 6. Business Logic

### 6.1 Contribution Calculations

```php
// Total contributed by user in a group
$totalContributed = ChilimbaContribution::where('group_id', $groupId)
    ->where('user_id', $userId)
    ->sum('amount');

// Expected contribution (based on meetings held)
$meetingsHeld = calculateMeetingsHeld($group->start_date, $group->meeting_frequency);
$expectedContribution = $meetingsHeld * $group->contribution_amount;

// Contribution status
$contributionStatus = $totalContributed >= $expectedContribution ? 'up-to-date' : 'behind';
```

### 6.2 Payout Queue Position

```php
// Get user's position in payout queue
$member = ChilimbaMember::where('group_id', $groupId)
    ->where('name', $user->name)
    ->first();

$position = $member->position_in_queue;
$totalMembers = ChilimbaMember::where('group_id', $groupId)->count();

// Calculate expected payout date
$meetingsSinceStart = calculateMeetingsHeld($group->start_date, $group->meeting_frequency);
$meetingsUntilPayout = $position - $meetingsSinceStart;
$expectedPayoutDate = calculateFutureDate($group->meeting_frequency, $meetingsUntilPayout);
```

### 6.3 Loan Calculations

```php
// Calculate total amount to repay
$principal = $loan->loan_amount;
$interestRate = $loan->interest_rate / 100;
$interest = $principal * $interestRate;
$totalToRepay = $principal + $interest;

// Calculate remaining balance
$totalPaid = LoanPayment::where('loan_id', $loan->id)->sum('amount');
$remainingBalance = $totalToRepay - $totalPaid;

// Update loan status
if ($remainingBalance <= 0) {
    $loan->status = 'paid';
} elseif (now() > $loan->due_date) {
    $loan->status = 'defaulted';
}
```

### 6.4 Meeting Reminders

```php
// Send reminders 2 days before meeting
$upcomingMeetings = ChilimbaGroup::where('user_id', $userId)
    ->where('is_active', true)
    ->get()
    ->filter(function($group) {
        $nextMeeting = calculateNextMeeting($group);
        return $nextMeeting->diffInDays(now()) == 2;
    });

foreach ($upcomingMeetings as $group) {
    // Send notification
    Notification::send($user, new ChilimbaMeetingReminder($group));
    
    // Send SMS if enabled
    if ($user->sms_notifications_enabled) {
        SMS::send($user->phone, "Reminder: {$group->name} meeting on {$nextMeeting->format('D, M j')} at {$group->meeting_time}");
    }
}
```

---

## 7. API Endpoints

### Groups
```
GET    /api/lifeplus/chilimba/groups              - List user's groups
POST   /api/lifeplus/chilimba/groups              - Create new group
GET    /api/lifeplus/chilimba/groups/{id}         - Get group details
PUT    /api/lifeplus/chilimba/groups/{id}         - Update group
DELETE /api/lifeplus/chilimba/groups/{id}         - Delete group
```

### Contributions
```
GET    /api/lifeplus/chilimba/groups/{id}/contributions        - List contributions
POST   /api/lifeplus/chilimba/groups/{id}/contributions        - Record contribution
GET    /api/lifeplus/chilimba/contributions/{id}               - Get contribution
DELETE /api/lifeplus/chilimba/contributions/{id}               - Delete contribution
GET    /api/lifeplus/chilimba/groups/{id}/contributions/summary - Get summary
```

### Payouts
```
GET    /api/lifeplus/chilimba/groups/{id}/payouts         - List payouts
POST   /api/lifeplus/chilimba/groups/{id}/payouts         - Record payout
GET    /api/lifeplus/chilimba/groups/{id}/payout-queue    - Get payout queue
```

### Loans
```
GET    /api/lifeplus/chilimba/groups/{id}/loans           - List loans
POST   /api/lifeplus/chilimba/groups/{id}/loans           - Request loan
GET    /api/lifeplus/chilimba/loans/{id}                  - Get loan details
POST   /api/lifeplus/chilimba/loans/{id}/payments         - Record payment
GET    /api/lifeplus/chilimba/loans/{id}/calculate        - Calculate repayment
```

### Members
```
GET    /api/lifeplus/chilimba/groups/{id}/members         - List members
POST   /api/lifeplus/chilimba/groups/{id}/members         - Add member
PUT    /api/lifeplus/chilimba/members/{id}                - Update member
DELETE /api/lifeplus/chilimba/members/{id}                - Remove member
```

### Meetings
```
GET    /api/lifeplus/chilimba/groups/{id}/meetings        - List meetings
POST   /api/lifeplus/chilimba/groups/{id}/meetings        - Record meeting
GET    /api/lifeplus/chilimba/meetings/{id}               - Get meeting details
```

---

## 8. Notifications & Reminders

### Meeting Reminders
- **2 days before:** "Reminder: Chilenje Ladies Chilimba meeting on Saturday, Dec 20 at 2:00 PM"
- **Morning of meeting:** "Today: Chilimba meeting at 2:00 PM. Don't forget your K100 contribution!"

### Contribution Reminders
- **If missed:** "You missed the last Chilimba meeting. Please catch up on your K100 contribution."

### Payout Notifications
- **When it's your turn:** "Good news! You're next in line for the Chilimba payout (K2,000) at the next meeting."
- **After receiving:** "Payout received: K2,000 from Chilenje Ladies Chilimba"

### Loan Reminders
- **7 days before due:** "Reminder: Your Chilimba loan of K500 is due in 7 days"
- **On due date:** "Your Chilimba loan payment of K600 is due today"
- **Overdue:** "Your Chilimba loan is overdue. Please make payment to avoid penalties"

---

## 9. Reports & Analytics

### Personal Reports
- Total contributed across all groups
- Total received in payouts
- Net position (received - contributed)
- Active loans summary
- Contribution history chart
- Export to PDF/Excel

### Group Reports (for Secretary)
- Member contribution status
- Attendance records
- Loan portfolio
- Group balance
- Payout schedule
- Meeting minutes archive

---

## 10. Group-Wide Contribution Tracking

### Secretary Dashboard

The secretary has a comprehensive view of all member contributions:

```
┌─────────────────────────────────────────────────┐
│  Chilenje Ladies Chilimba - Secretary View      │
├─────────────────────────────────────────────────┤
│  Tabs: [Overview] [All Contributions]           │
│        [Members] [Payouts] [Loans] [Reports]    │
├─────────────────────────────────────────────────┤
│  ALL CONTRIBUTIONS                              │
│                                                 │
│  Meeting: December 2025                         │
│  Expected: K2,000 (20 members × K100)          │
│  Collected: K1,800                              │
│  Outstanding: K200 (2 members)                  │
│                                                 │
│  ┌───────────────────────────────────────────┐ │
│  │ Member          Status      Amount  Action│ │
│  ├───────────────────────────────────────────┤ │
│  │ Mary Banda      ✓ Paid     K100    [View]│ │
│  │ Jane Mwale      ✓ Paid     K100    [View]│ │
│  │ Ruth Phiri      ⚠ Pending  K0      [Add] │ │
│  │ Grace Tembo     ✓ Paid     K100    [View]│ │
│  │ Sarah Zulu      ⚠ Pending  K0      [Add] │ │
│  │ ...                                       │ │
│  └───────────────────────────────────────────┘ │
│                                                 │
│  [Record Multiple] [Export Report] [Send SMS]  │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Member Contribution History

Secretary can view detailed contribution history for each member:

```
┌─────────────────────────────────────────────────┐
│  ← Mary Banda - Contribution History            │
├─────────────────────────────────────────────────┤
│                                                 │
│  Total Contributed: K1,200                      │
│  Expected: K1,200 (12 meetings)                 │
│  Status: ✓ Up to date                          │
│                                                 │
│  ┌───────────────────────────────────────────┐ │
│  │ Date         Amount  Method    Recorded By│ │
│  ├───────────────────────────────────────────┤ │
│  │ Dec 7, 2025  K100   Cash      Self       │ │
│  │ Nov 2, 2025  K100   MoMo      Secretary  │ │
│  │ Oct 5, 2025  K100   Cash      Self       │ │
│  │ Sep 7, 2025  K100   Cash      Self       │ │
│  │ ...                                       │ │
│  └───────────────────────────────────────────┘ │
│                                                 │
│  [Add Contribution] [Export PDF]                │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Recording Contributions for Members

Secretary can record contributions on behalf of members:

```
┌─────────────────────────────────────────────────┐
│  Record Contribution for Member                 │
├─────────────────────────────────────────────────┤
│                                                 │
│  Member                                         │
│  [Ruth Phiri                    ▼]             │
│                                                 │
│  Date                                           │
│  [Dec 17, 2025                  ▼]             │
│                                                 │
│  Amount (K)                                     │
│  [100.00                          ]             │
│                                                 │
│  Payment Method                                 │
│  ○ Cash  ○ Mobile Money                        │
│                                                 │
│  Receipt Number (Optional)                      │
│  [                                ]             │
│                                                 │
│  Notes                                          │
│  [Paid at meeting                 ]             │
│                                                 │
│  Recorded By: Secretary (You)                   │
│                                                 │
│  [Cancel]  [Save Contribution]                  │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Bulk Recording

Secretary can record multiple contributions at once (after a meeting):

```
┌─────────────────────────────────────────────────┐
│  Record Multiple Contributions                  │
├─────────────────────────────────────────────────┤
│                                                 │
│  Meeting Date: [Dec 17, 2025    ▼]             │
│                                                 │
│  ☑ Select All                                   │
│                                                 │
│  ☑ Mary Banda        K100  [Cash ▼]            │
│  ☑ Jane Mwale        K100  [Cash ▼]            │
│  ☐ Ruth Phiri        K0    [     ▼]  (Absent)  │
│  ☑ Grace Tembo       K100  [MoMo ▼]            │
│  ☐ Sarah Zulu        K0    [     ▼]  (Absent)  │
│  ☑ ...                                          │
│                                                 │
│  Total: K1,800 (18 members)                     │
│                                                 │
│  [Cancel]  [Save All Contributions]             │
│                                                 │
└─────────────────────────────────────────────────┘
```

## 11. Security & Data Integrity

### Role-Based Access Control

#### Member Permissions
- ✅ View own contributions
- ✅ Record own contributions
- ✅ View own loans
- ✅ View group summary (total collected, next meeting)
- ✅ View payout queue
- ❌ Cannot view other members' detailed contributions
- ❌ Cannot edit other members' data
- ❌ Cannot delete contributions

#### Secretary Permissions
- ✅ View all members' contributions
- ✅ Record contributions for any member
- ✅ View all loans
- ✅ Approve/reject loans
- ✅ Manage member list
- ✅ Record meeting minutes
- ✅ Generate reports
- ⚠️ Can edit contributions (with audit trail)
- ⚠️ Can delete contributions (with approval + audit trail)

#### Treasurer Permissions (Optional)
- Same as Secretary
- ✅ Manage group finances
- ✅ Record payouts
- ✅ View financial reports

### Audit Trail System

Every action is logged with complete details:

#### `lifeplus_chilimba_audit_log`
```sql
CREATE TABLE lifeplus_chilimba_audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    action_type ENUM('create', 'update', 'delete', 'approve', 'reject') NOT NULL,
    entity_type ENUM('contribution', 'payout', 'loan', 'member', 'meeting') NOT NULL,
    entity_id BIGINT NOT NULL,
    old_values JSON,
    new_values JSON,
    reason TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (group_id) REFERENCES lifeplus_chilimba_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_group_entity (group_id, entity_type, entity_id),
    INDEX idx_user_action (user_id, action_type, created_at)
);
```

#### Audit Log Example
```json
{
  "action": "update",
  "entity": "contribution",
  "entity_id": 123,
  "performed_by": "Jane Mwale (Secretary)",
  "timestamp": "2025-12-17 14:30:00",
  "old_values": {
    "amount": 100.00,
    "payment_method": "cash"
  },
  "new_values": {
    "amount": 150.00,
    "payment_method": "mobile_money"
  },
  "reason": "Member paid extra K50 for missed meeting",
  "ip_address": "41.77.xxx.xxx"
}
```

### Edit Protection Mechanisms

#### 1. Time-Based Edit Restrictions
```php
// Contributions can only be edited within 24 hours
if ($contribution->created_at->diffInHours(now()) > 24) {
    throw new Exception('Cannot edit contributions older than 24 hours');
}

// After 24 hours, requires approval
if ($contribution->created_at->diffInHours(now()) > 24 && !$user->isSecretary()) {
    // Request approval from secretary
    $this->requestEditApproval($contribution, $newData);
}
```

#### 2. Approval Workflow for Sensitive Changes

```
Member requests edit → Secretary reviews → Approve/Reject
```

**Example: Member wants to edit old contribution**

```
┌─────────────────────────────────────────────────┐
│  Edit Request                                   │
├─────────────────────────────────────────────────┤
│                                                 │
│  This contribution is older than 24 hours.      │
│  Your edit request will be sent to the          │
│  secretary for approval.                        │
│                                                 │
│  Current: K100 (Cash) - Nov 2, 2025            │
│  Proposed: K150 (Mobile Money)                  │
│                                                 │
│  Reason for change:                             │
│  [I actually paid K150 that day but recorded   │
│   K100 by mistake                              ]│
│                                                 │
│  [Cancel]  [Submit Request]                     │
│                                                 │
└─────────────────────────────────────────────────┘
```

**Secretary Approval Screen**

```
┌─────────────────────────────────────────────────┐
│  Pending Edit Requests (3)                      │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌───────────────────────────────────────────┐ │
│  │ Mary Banda - Contribution Edit            │ │
│  │ Requested: 2 hours ago                    │ │
│  │                                           │ │
│  │ Current: K100 (Cash) - Nov 2, 2025       │ │
│  │ Proposed: K150 (Mobile Money)            │ │
│  │                                           │ │
│  │ Reason: "I actually paid K150 that day   │ │
│  │ but recorded K100 by mistake"            │ │
│  │                                           │ │
│  │ [Reject]  [Approve]                      │ │
│  └───────────────────────────────────────────┘ │
│                                                 │
└─────────────────────────────────────────────────┘
```

#### 3. Deletion Protection

```php
// Soft delete with reason required
public function deleteContribution($id, $reason)
{
    if (empty($reason)) {
        throw new Exception('Reason required for deletion');
    }
    
    $contribution = Contribution::findOrFail($id);
    
    // Log before deletion
    AuditLog::create([
        'action_type' => 'delete',
        'entity_type' => 'contribution',
        'entity_id' => $id,
        'old_values' => $contribution->toArray(),
        'reason' => $reason,
        'user_id' => auth()->id(),
    ]);
    
    // Soft delete (keeps record in database)
    $contribution->delete();
}
```

#### 4. Multi-Signature for Critical Actions

For high-value transactions (e.g., payouts > K5,000), require approval from both Secretary and Treasurer:

```
┌─────────────────────────────────────────────────┐
│  Record Payout - Approval Required              │
├─────────────────────────────────────────────────┤
│                                                 │
│  Amount: K10,000                                │
│  Recipient: Mary Banda                          │
│                                                 │
│  ⚠️ This payout requires dual approval          │
│                                                 │
│  Secretary: ✓ Approved (Jane Mwale)            │
│  Treasurer: ⏳ Pending (Grace Tembo)            │
│                                                 │
│  Status: Waiting for treasurer approval        │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Data Validation Rules

```php
// Contribution validation
$rules = [
    'amount' => 'required|numeric|min:0|max:100000',
    'contribution_date' => 'required|date|before_or_equal:today',
    'payment_method' => 'required|in:cash,mobile_money',
    'group_id' => 'required|exists:lifeplus_chilimba_groups,id',
];

// Prevent backdating beyond group start date
if ($contributionDate < $group->start_date) {
    throw new Exception('Cannot record contribution before group start date');
}

// Prevent future dating
if ($contributionDate > now()) {
    throw new Exception('Cannot record future contributions');
}

// Prevent duplicate contributions on same date
$existing = Contribution::where('user_id', $userId)
    ->where('group_id', $groupId)
    ->whereDate('contribution_date', $contributionDate)
    ->exists();
    
if ($existing) {
    throw new Exception('Contribution already recorded for this date');
}
```

### Fraud Prevention

#### 1. Contribution Limits
```php
// Maximum contribution per meeting (prevent inflated amounts)
$maxContribution = $group->contribution_amount * 2; // Allow up to 2x standard

if ($amount > $maxContribution) {
    throw new Exception("Amount exceeds maximum allowed (K{$maxContribution})");
}
```

#### 2. Frequency Checks
```php
// Prevent recording multiple contributions too quickly
$lastContribution = Contribution::where('user_id', $userId)
    ->where('group_id', $groupId)
    ->latest()
    ->first();

if ($lastContribution && $lastContribution->created_at->diffInDays(now()) < 7) {
    // Warn if contributing more frequently than meeting schedule
    $this->sendWarning('Unusual contribution frequency detected');
}
```

#### 3. Anomaly Detection
```php
// Flag unusual patterns
if ($amount > $group->contribution_amount * 1.5) {
    AuditLog::create([
        'action_type' => 'flag',
        'entity_type' => 'contribution',
        'reason' => 'Amount significantly higher than standard',
    ]);
}
```

### Transparency Features

#### 1. Public Audit Log (for members)
Members can view a simplified audit log:

```
┌─────────────────────────────────────────────────┐
│  Recent Activity                                │
├─────────────────────────────────────────────────┤
│                                                 │
│  Dec 17, 14:30 - Secretary recorded             │
│  contribution for Ruth Phiri (K100)             │
│                                                 │
│  Dec 17, 14:25 - Mary Banda recorded own        │
│  contribution (K100)                            │
│                                                 │
│  Dec 17, 14:20 - Secretary updated meeting      │
│  minutes                                        │
│                                                 │
│  Dec 10, 15:00 - Payout given to Jane Mwale    │
│  (K2,000)                                       │
│                                                 │
└─────────────────────────────────────────────────┘
```

#### 2. Monthly Reconciliation Report
Auto-generated report sent to all members:

```
Chilenje Ladies Chilimba - December 2025 Report

Total Expected: K2,000 (20 members × K100)
Total Collected: K1,900
Outstanding: K100 (1 member)

Contributions Recorded:
- By Members: 18 (90%)
- By Secretary: 2 (10%)

Edits Made: 1
- Mary Banda updated amount (K100 → K150)
- Approved by: Secretary
- Reason: Paid extra for missed meeting

Payouts: 1
- Jane Mwale: K2,000

Next Meeting: Jan 4, 2026
```

### Data Privacy

```php
// Members can only see:
// 1. Their own detailed contributions
// 2. Group summary (total collected)
// 3. Payout queue (who's next)
// 4. Other members' names and status (paid/pending)

// Members CANNOT see:
// - Other members' contribution amounts
// - Other members' payment methods
// - Other members' loan details
// - Detailed financial history of others
```

---

## 12. Future Enhancements (Phase 3)

1. **Group Chat** - In-app messaging for group members
2. **Automated Payout Queue** - System suggests next recipient based on contributions
3. **Penalty System** - Track and calculate penalties for missed meetings
4. **Multi-Currency** - Support USD, ZMW
5. **Bank Integration** - Direct bank transfers for contributions
6. **Group Analytics** - Performance metrics, trends
7. **Insurance Integration** - Link to funeral/medical insurance
8. **Dispute Resolution** - Track and resolve member disputes
9. **Group Voting** - Vote on decisions within the app
10. **External Sharing** - Share group reports with non-members

---

## 13. Success Metrics

### User Engagement
- Number of active groups
- Contributions recorded per month
- Meeting attendance rate
- Loan repayment rate

### User Satisfaction
- Feature usage rate
- User retention
- Feedback ratings
- Support tickets

### Business Impact
- Increased LifePlus daily active users
- Longer session times
- Higher app retention
- Cross-feature usage (Chilimba → other Money features)

---

## Changelog

### Version 1.1 - December 17, 2025
- Added group-wide contribution tracking for secretaries
- Added secretary dashboard with all members' contributions
- Added bulk contribution recording
- Added comprehensive audit trail system
- Added edit protection mechanisms (time-based, approval workflow)
- Added deletion protection with reason requirement
- Added multi-signature approval for high-value transactions
- Added fraud prevention measures
- Added anomaly detection
- Added transparency features (public audit log, monthly reports)
- Added data validation rules
- Enhanced security and data integrity

### Version 1.0 - December 17, 2025
- Initial specification
- Core features defined
- Database schema designed
- UI mockups created
- API endpoints specified
