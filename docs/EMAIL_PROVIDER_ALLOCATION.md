# MyGrowNet Email Provider Allocation Strategy

## Provider Allocation by Feature

### 🚀 **RESEND** (3,000/month) - Critical & Time-Sensitive
**Use for instant delivery of important transactional emails**

#### **Authentication & Security**
- ✅ Password reset emails
- ✅ Email verification
- ✅ Two-factor authentication codes
- ✅ Login alerts (suspicious activity)
- ✅ Account security notifications

#### **Financial Transactions**
- ✅ Payment confirmations
- ✅ Withdrawal requests
- ✅ Wallet transactions (deposits, transfers)
- ✅ Subscription renewals
- ✅ Payment failures
- ✅ Refund notifications

#### **Critical Notifications**
- ✅ Level advancement (Associate → Professional → etc.)
- ✅ Milestone achievements
- ✅ Referral bonuses earned
- ✅ Commission payments
- ✅ Profit-sharing distributions

#### **Starter Kit & Orders**
- ✅ Starter kit purchase confirmation
- ✅ Order receipts
- ✅ Delivery notifications

#### **Quick Invoice**
- ✅ Invoice generated notifications
- ✅ Invoice payment received
- ✅ Invoice reminders (urgent)

---

### 📧 **BREVO** (9,000/month = 300/day) - Marketing & Engagement
**Use for promotional and scheduled communications**

#### **Marketing & Campaigns**
- ✅ Weekly newsletters
- ✅ Monthly platform updates
- ✅ New feature announcements
- ✅ Promotional campaigns
- ✅ Special offers
- ✅ Event invitations

#### **Educational Content**
- ✅ Learning pack updates
- ✅ Workshop announcements
- ✅ Training session reminders
- ✅ Skill-building tips
- ✅ Success stories

#### **Community Engagement**
- ✅ Community updates
- ✅ Member spotlights
- ✅ Network growth tips
- ✅ Referral program promotions

#### **Scheduled Notifications**
- ✅ Monthly activity summaries
- ✅ Quarterly profit-sharing announcements
- ✅ Level progression reminders
- ✅ Subscription renewal reminders (non-urgent)

#### **GrowBuilder**
- ✅ New business plan templates
- ✅ AI feature updates
- ✅ Business tips & resources

---

### 📊 **AMAZON SES** (62,000/month) - Bulk & Reports
**Use for high-volume, less time-sensitive emails**

#### **Bulk Reports**
- ✅ Monthly financial reports
- ✅ Network performance reports
- ✅ Commission statements
- ✅ Tax documents
- ✅ Annual summaries

#### **System Notifications**
- ✅ System maintenance notices
- ✅ Platform updates (non-critical)
- ✅ Policy changes
- ✅ Terms of service updates

#### **Admin & Internal**
- ✅ Admin alerts
- ✅ System health reports
- ✅ Error notifications
- ✅ Backup confirmations

#### **Venture Builder**
- ✅ Project update digests
- ✅ Investment opportunity newsletters
- ✅ Quarterly dividend reports

#### **CMS (Employee Portal)**
- ✅ Bulk employee notifications
- ✅ Payroll reports
- ✅ Company-wide announcements

---

## Implementation Map

### **Module → Provider Mapping**

| Module | Feature | Provider | Priority |
|--------|---------|----------|----------|
| **Auth** | Password Reset | Resend | Critical |
| **Auth** | Email Verification | Resend | Critical |
| **Wallet** | Transaction Confirmation | Resend | Critical |
| **Wallet** | Monthly Statement | SES | Low |
| **Subscriptions** | Payment Success | Resend | Critical |
| **Subscriptions** | Renewal Reminder | Brevo | Medium |
| **Levels** | Level Advancement | Resend | Critical |
| **Levels** | Progress Update | Brevo | Medium |
| **Referrals** | Bonus Earned | Resend | Critical |
| **Referrals** | Network Tips | Brevo | Low |
| **Profit Sharing** | Distribution Notice | Resend | Critical |
| **Profit Sharing** | Quarterly Summary | SES | Low |
| **Starter Kit** | Purchase Confirmation | Resend | Critical |
| **Quick Invoice** | Invoice Generated | Resend | Critical |
| **Quick Invoice** | Payment Reminder | Brevo | Medium |
| **GrowBuilder** | Plan Generated | Resend | Medium |
| **GrowBuilder** | Tips & Updates | Brevo | Low |
| **BizDocs** | Document Generated | Resend | Medium |
| **BizDocs** | Template Updates | Brevo | Low |
| **Venture Builder** | Investment Confirmed | Resend | Critical |
| **Venture Builder** | Project Updates | Brevo | Medium |
| **Learning** | Course Enrollment | Resend | Medium |
| **Learning** | New Content | Brevo | Low |
| **Workshops** | Registration Confirmed | Resend | Critical |
| **Workshops** | Reminder | Brevo | Medium |
| **CMS** | Employee Onboarding | Resend | Critical |
| **CMS** | Bulk Announcements | SES | Low |
| **Messaging** | Direct Messages | Resend | High |
| **Notifications** | System Alerts | Resend | High |
| **Notifications** | Digests | Brevo | Low |

---

## Usage Estimates

### **Monthly Volume Projection**

#### **Resend (3,000/month limit)**
- Auth & Security: ~500 emails
- Financial Transactions: ~800 emails
- Critical Notifications: ~600 emails
- Orders & Invoices: ~400 emails
- Other Critical: ~700 emails
- **Total: ~3,000 emails** ✅ Within limit

#### **Brevo (9,000/month limit)**
- Marketing Campaigns: ~2,000 emails
- Educational Content: ~1,500 emails
- Community Engagement: ~1,000 emails
- Scheduled Notifications: ~2,500 emails
- Feature Updates: ~1,000 emails
- **Total: ~8,000 emails** ✅ Within limit

#### **SES (62,000/month limit)**
- Bulk Reports: ~5,000 emails
- System Notifications: ~2,000 emails
- Admin Alerts: ~1,000 emails
- **Total: ~8,000 emails** ✅ Well within limit

---

## Decision Rules

### **Use RESEND when:**
1. User expects immediate delivery (password reset, payment confirmation)
2. Financial transaction involved
3. Security-related notification
4. Time-sensitive action required
5. Critical milestone or achievement

### **Use BREVO when:**
1. Marketing or promotional content
2. Scheduled/batched communications
3. Educational or informational content
4. Non-urgent reminders
5. Community engagement

### **Use SES when:**
1. Bulk reports or statements
2. System-wide announcements
3. Non-time-sensitive notifications
4. High-volume communications
5. Admin/internal emails

---

## Failover Strategy

If a provider reaches its limit:

1. **Resend exhausted** → Critical emails switch to Brevo temporarily
2. **Brevo exhausted** → Marketing emails queue for next day
3. **SES exhausted** → Reports queue for next day (unlikely to hit limit)

---

## Monitoring & Alerts

Set up alerts when usage reaches:
- **70%** of limit → Warning notification
- **85%** of limit → Critical alert
- **95%** of limit → Activate failover

Track usage in database:
```sql
CREATE TABLE email_usage (
    provider VARCHAR(20),
    date DATE,
    count INT,
    PRIMARY KEY (provider, date)
);
```

---

## Configuration Priority

**Default Mailer:** Resend (for critical transactional emails)

```env
MAIL_MAILER=resend
```

**Explicit Selection:** Use `EmailService` helper or specify mailer:

```php
// Critical - Resend
EmailService::sendTransactional($mailable, $email);

// Marketing - Brevo
EmailService::sendMarketing($mailable, $email);

// Bulk - SES
EmailService::sendBulk($mailable, $emails);
```
