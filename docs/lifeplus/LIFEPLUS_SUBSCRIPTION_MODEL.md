# LifePlus Subscription Model

**Last Updated:** December 17, 2025
**Status:** Active (Using Existing Module Infrastructure)

## Overview

LifePlus uses the **existing MyGrowNet module subscription system** (`ModuleAccessService`). Each app/module has its own subscription tiers, and LifePlus is configured as a freemium product that serves as a core starter kit value for MLM members.

## Existing Infrastructure Used

- `ModuleAccessService` - Handles access control and tier checking
- `module_subscriptions` table - Stores user subscriptions
- `modules` table - Stores module configuration including tiers
- `CheckModuleAccess` middleware - Route-level access control

## Subscription Tiers (Configured in ModuleSeeder)

### 1. Free Tier
**Price:** K0/month
**Target:** Anyone (guests, clients, business users)
**Account Types:** guest, client, business

**Features:**
- ✅ Tasks (up to 10 active)
- ✅ Expense tracking (current month only)
- ✅ 1 habit tracker
- ✅ Community posts (view only)
- ✅ Gig finder (view only)
- ❌ No Chilimba tracker
- ❌ No budget planning
- ❌ No analytics/reports
- ❌ No gig posting
- ❌ No data export
- ❌ 1 device only

**Purpose:** Lead generation, app discovery, conversion funnel

### 2. Premium Tier
**Price:** K25/month (K250/year)
**Target:** Non-MLM users who want full features
**Account Types:** guest, client, business

**Features:**
- ✅ Unlimited tasks
- ✅ Full expense history
- ✅ Unlimited habits
- ✅ Budget planning
- ✅ Chilimba tracker (up to 2 groups)
- ✅ Community posts (create & view)
- ✅ Gig posting
- ✅ Analytics & reports
- ✅ Data export
- ✅ Up to 3 devices
- ✅ Offline mode
- ✅ Priority support
- ❌ No MyGrowNet integration

### 3. Member Tier (FREE for MLM Members)
**Price:** K0 (included with MyGrowNet membership)
**Target:** Active MyGrowNet members
**Account Types:** member
**Requirement:** Must have active MyGrowNet subscription

**Features:**
- ✅ ALL Premium features
- ✅ Unlimited Chilimba groups
- ✅ Unlimited devices
- ✅ MyGrowNet wallet integration
- ✅ Earnings tracking
- ✅ Referral tracking

**Purpose:** Core value proposition for MLM membership

### 4. Elite Tier (Professional+ Members)
**Price:** K0 (included with Professional+ level)
**Target:** Professional, Senior, Manager, Director, Executive, Ambassador
**Account Types:** member
**Requirement:** Professional level or above

**Features:**
- ✅ ALL Member features
- ✅ AI-powered analytics
- ✅ Team collaboration tools
- ✅ Custom categories
- ✅ API access

## Feature Access Matrix

| Feature | Free | Premium | MLM Member | MLM Elite |
|---------|------|---------|------------|-----------|
| Tasks | 10 max | Unlimited | Unlimited | Unlimited |
| Expense History | 1 month | Unlimited | Unlimited | Unlimited |
| Habits | 1 | Unlimited | Unlimited | Unlimited |
| Chilimba Groups | 0 | 2 | Unlimited | Unlimited |
| Budget Planning | ❌ | ✅ | ✅ | ✅ |
| Analytics | ❌ | Basic | Advanced | AI-Powered |
| Gig Posting | ❌ | ✅ | ✅ | ✅ |
| Data Export | ❌ | ✅ | ✅ | ✅ |
| Devices | 1 | 3 | Unlimited | Unlimited |
| MLM Integration | ❌ | ❌ | ✅ | ✅ |
| Team Tools | ❌ | ❌ | ❌ | ✅ |
| API Access | ❌ | ❌ | ❌ | ✅ |

## Implementation Strategy

### Phase 1: Access Control System
1. Create subscription status tracking
2. Implement feature gates
3. Add upgrade prompts
4. Build subscription management UI

### Phase 2: Payment Integration
1. Mobile money integration (MTN, Airtel)
2. Subscription billing system
3. Auto-renewal logic
4. Grace period handling

### Phase 3: MLM Integration
1. Auto-provision for MLM members
2. Sync with membership status
3. Track usage for MLM benefits
4. Referral tracking

### Phase 4: Monetization
1. Add upgrade CTAs strategically
2. Implement trial periods
3. Create conversion funnels
4. Analytics for optimization

## Database Schema

### lifeplus_subscriptions table
```sql
- id
- user_id
- tier (free, premium, mlm_member, mlm_elite)
- status (active, expired, cancelled, grace_period)
- started_at
- expires_at
- auto_renew (boolean)
- payment_method
- last_payment_at
- next_billing_date
- cancelled_at
- cancellation_reason
- created_at
- updated_at
```

### lifeplus_usage_limits table
```sql
- id
- user_id
- tasks_count
- habits_count
- chilimba_groups_count
- devices_count
- last_reset_at
- created_at
- updated_at
```

### lifeplus_payments table
```sql
- id
- user_id
- subscription_id
- amount
- currency
- payment_method
- transaction_id
- status (pending, completed, failed, refunded)
- paid_at
- created_at
- updated_at
```

## Upgrade Prompts Strategy

### Free → Premium
**Trigger Points:**
- When user reaches 10 tasks limit
- After 7 days of active use
- When trying to access premium features
- When viewing analytics (show preview)

**Message:** "Unlock unlimited tasks, budget planning, and Chilimba tracker for just K25/month!"

### Premium → MLM Member
**Trigger Points:**
- After 30 days of premium use
- When viewing earnings/referral features
- In community section

**Message:** "Join MyGrowNet and get LifePlus Premium FREE + earn from referrals!"

### Non-Member → MLM Member
**Trigger Points:**
- When viewing locked MLM features
- In wallet/earnings section
- After 14 days of use

**Message:** "Join MyGrowNet to unlock earnings tracking, unlimited Chilimba groups, and start earning!"

## Revenue Projections

### Standalone Subscriptions
- Target: 1,000 premium users
- Monthly: K25 × 1,000 = K25,000
- Annual: K300,000

### MLM Member Value
- LifePlus as starter kit justifies K20/month platform fee
- Increases perceived value of membership
- Reduces churn by providing tangible daily value

### Total Potential
- 5,000 MLM members × K20/month = K100,000/month
- 1,000 premium users × K25/month = K25,000/month
- **Total: K125,000/month (K1.5M/year)**

## Marketing Strategy

### For Non-MLM Users
1. **App Store Presence:** List on Google Play, iOS App Store
2. **Social Media:** Facebook, Instagram, TikTok demos
3. **Content Marketing:** Blog posts on budgeting, productivity
4. **Referral Program:** Free month for referrals
5. **Partnerships:** Local businesses, churches, schools

### For MLM Members
1. **Onboarding:** Auto-provision on registration
2. **Training:** How to use LifePlus effectively
3. **Testimonials:** Success stories from members
4. **Competitions:** Best usage, most organized member
5. **Integration:** Deep integration with MLM features

## Compliance & Legal

### Subscription Terms
- Clear pricing display
- Easy cancellation process
- Pro-rated refunds (first 7 days)
- Data retention policy
- Privacy policy compliance

### MLM Separation
- Clear distinction between app subscription and MLM membership
- No investment promises
- Product-based value proposition
- Compliance with Zambian regulations

## Success Metrics

### User Acquisition
- Free signups per month
- Free → Premium conversion rate (target: 5%)
- Premium → MLM conversion rate (target: 10%)
- MLM member activation rate (target: 80%)

### Engagement
- Daily active users (DAU)
- Monthly active users (MAU)
- Feature usage rates
- Session duration

### Revenue
- Monthly recurring revenue (MRR)
- Annual recurring revenue (ARR)
- Customer lifetime value (CLV)
- Churn rate (target: <5%)

## Roadmap

### Q1 2026
- ✅ Complete feature set
- ✅ Implement access control
- ✅ Build subscription UI
- ✅ Payment integration

### Q2 2026
- Launch free tier publicly
- Marketing campaign
- App store listings
- Referral program

### Q3 2026
- MLM integration complete
- Auto-provisioning live
- Team features for Elite
- API access

### Q4 2026
- AI-powered insights
- Advanced analytics
- White-label option
- International expansion

## Changelog

### December 17, 2025
- Initial subscription model design
- Feature tier definitions
- Revenue projections
- Implementation strategy
