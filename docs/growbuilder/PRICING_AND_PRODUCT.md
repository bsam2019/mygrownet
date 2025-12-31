# GrowBuilder – Product & Pricing Document

**Last Updated:** December 28, 2025
**Status:** Production
**For:** Internal development and planning

## 1. Platform Overview

GrowBuilder is a website and e-commerce builder under MyGrowNet. It provides tools for businesses, freelancers, and agencies to create websites, connect payments, and operate online shops using a subscription model.

- **Primary Domain:** mygrownet.com
- **Default Subdomain for users:** username.mygrownet.com
- **Payment integrations:** Flutterwave, MTN MoMo Online, Airtel Money, Zamtel (as supported)
- **SMS & Email:** Communication available based on plan level

## 2. Pricing & Subscription Plans

### 2.1 Plan Structure & Features

| Plan | Monthly Price | Notes |
|------|---------------|-------|
| **Free** | ZMW 0 | Testing plan |
| **Starter** | ZMW 120 | For small businesses |
| **Business** | ZMW 350 | Free domain after 3 months prepayment |
| **Agency/Reseller** | ZMW 900 | Build/manage 10–20 sites |
| **Done-For-You Setup** | ZMW 1,500–3,500 once | Not shown as card; optional service |

### 2.2 Price in Ngwee (for database)

| Plan | Monthly (ZMW) | Monthly (Ngwee) | Annual (ZMW) | Annual (Ngwee) |
|------|---------------|-----------------|--------------|----------------|
| Free | 0 | 0 | 0 | 0 |
| Starter | 120 | 12000 | 1,200 | 120000 |
| Business | 350 | 35000 | 3,500 | 350000 |
| Agency | 900 | 90000 | 9,000 | 900000 |

### 2.3 Features Per Plan

#### Free Plan
- 1 website
- Subdomain: username.mygrownet.com
- Limited templates
- 500MB storage
- No custom domain
- No payment integration
- No e-commerce
- **AI: 5 prompts/month (limited)**

#### Starter Plan (K120/month)
- 1 website
- Custom domain (user buys or brings their own)
- Up to 20 products (basic e-commerce)
- Basic email support
- Manual/offline payments only
- Shared SMTP email (200 emails/month)
- **AI: 100 prompts/month + section generator**

#### Business Plan (K350/month) - RECOMMENDED
- 1 website
- **Free domain after 3 months prepayment**
- Unlimited store products
- Full payment integrations (MTN, Airtel, Visa/Flutterwave)
- Marketing tools (email campaigns & automations)
- Priority support
- Remove MyGrowNet branding
- Connect own SMTP
- **AI: Unlimited prompts + SEO assistant**

#### Agency/Reseller Plan (K900/month)
- Build/manage 10–20 websites
- White-label option (their branding)
- Monthly or annual billing
- Reseller chooses: they pay or client pays directly
- Commission integrated with MyGrowNet
- Discounted SMS credits (10-15%)
- All Business plan features
- Dedicated onboarding
- **AI: Unlimited + priority processing + early access to new features**

## 3. AI Features & Limits

### 3.1 AI Access by Tier

| Tier | AI Prompts/Month | Features |
|------|------------------|----------|
| **Free** | 5 | Basic content generation |
| **Starter** | 100 | Content generation + Section generator |
| **Business** | Unlimited | Full AI + Section generator + SEO assistant |
| **Agency** | Unlimited | Full AI + Priority processing + Early access |

### 3.2 Current AI Features
- **Content Generation**: Headlines, descriptions, paragraphs
- **Section Generator**: AI-powered section creation (Business+)
- **SEO Assistant**: Meta tags, keywords, optimization tips (Business+)

### 3.3 Upcoming AI Enhancements (Roadmap)

The following features will roll out to Business and Agency plans when ready:

| Feature | Description | Target Plans |
|---------|-------------|--------------|
| **AI Chatbot** | Automated customer support for user websites | Business, Agency |
| **Social Media Generator** | Auto-generate social posts from website content | Business, Agency |
| **Email Marketing Assistant** | AI-powered email campaign creation | Business, Agency |
| **Team AI** | Collaborative AI features for agencies | Agency only |
| **AI Analytics Insights** | Smart recommendations based on site performance | Business, Agency |

### 3.4 AI Usage Tracking
- Usage resets on the 1st of each month
- Users can view remaining prompts in dashboard
- Upgrade prompts shown when limit reached
- Agency users get priority queue for AI requests

## 4. Domain Handling Policy

### 4.1 Domain Activation Rules

- **Business plan** offers 1 free domain after 3 months pre-payment
- If user cancels before 12 months:
  - Domain ownership remains with MyGrowNet until outstanding months are covered or transfer fee is paid
- **Starter users** must buy or connect their own domain
- **Done-For-You customers** may include domain cost depending on the package

### 4.2 Domain Modal Message
When a user selects Business Plan, show:
> "Free domain will be activated after 3 months prepayment to protect MyGrowNet from early cancellations. If cancelled before 12 months, domain remains under MyGrowNet until renewal fees are settled."

### 4.3 Domain Renewal
- Renewed annually only if subscription is active
- Non-active subscriptions: domain held until renewal cost is settled

## 5. Dashboard Access & Permissions

All users get dashboard access, but features are controlled by plan level.

| Feature | Free | Starter | Business | Agency |
|---------|------|---------|----------|--------|
| Dashboard Login | ✔️ | ✔️ | ✔️ | ✔️ |
| Builder Access | ✔️ (limited) | ✔️ | ✔️ | ✔️ (multi-site) |
| Custom Domain | ❌ | ✔️ | ✔️ | ✔️ |
| Payment Integration | ❌ | Basic/Manual | ✔️ | ✔️ |
| Marketing Tools | ❌ | ❌ | ✔️ | ✔️ |
| Multi-Site Management | ❌ | ❌ | ❌ | ✔️ (10–20 sites) |
| Remove Branding | ❌ | ❌ | ✔️ | ✔️ |
| AI Content (5/mo) | ✔️ | — | — | — |
| AI Content (100/mo) | — | ✔️ | — | — |
| AI Unlimited | ❌ | ❌ | ✔️ | ✔️ |
| Section Generator | ❌ | ✔️ | ✔️ | ✔️ |
| SEO Assistant | ❌ | ❌ | ✔️ | ✔️ |
| Priority AI | ❌ | ❌ | ❌ | ✔️ |

## 6. Email System

### 6.1 System Emails (Platform Level)
- Password resets, subscription alerts, invoices
- Sent using MyGrowNet SMTP

### 6.2 User Website Emails

| Plan | Email Handling |
|------|----------------|
| Free | Up to 50/month via shared SMTP |
| Starter | Up to 200/month via shared SMTP |
| Business & Agency | Connect own SMTP (Gmail, Zoho, Outlook, Brevo) – unlimited by provider |

### 6.3 Marketing Emails
Only for Business & Agency:
- Campaigns and newsletters via integrations (Brevo/Mailchimp)

## 7. SMS Functionality

SMS is billed by credit system:

| Bundle | Price |
|--------|-------|
| 50 SMS | ZMW 15 |
| 200 SMS | ZMW 50 |
| 500 SMS | ZMW 100 |
| 1000 SMS | ZMW 180 |

- **Free Plan:** No SMS
- **Starter & Business:** Buy credits at standard price
- **Agency:** Credit discounts (10–15%)

## 8. Payment Gateway Handling

GrowBuilder integrates payment gateways; users connect their own merchant accounts.

- MyGrowNet does not handle merchant onboarding for users
- Direct payouts go to user accounts
- A fallback "Collect via MyGrowNet Wallet" can exist but must be structured as:
  - Collection on behalf of merchant
  - With service fee (5–10%)
  - Not advertised as guaranteed returns

## 9. Agency / Reseller Rules

### 9.1 Allowed
- Build up to 10–20 sites
- Bill clients privately
- White-label options (branding hide MyGrowNet)

### 9.2 Restrictions
- Limit: 20 active websites
- No illegal product websites
- Failure to pay subscription → dashboards lock, sites remain viewable

### 9.3 Billing Models

| Model | Who Pays Monthly | Notes |
|-------|------------------|-------|
| Reseller Managed | Reseller pays MyGrowNet | Reseller keeps client payments |
| Direct Billing | Client pays MyGrowNet | Reseller earns commission |

## 10. Done-For-You Setup

- **Fee:** ZMW 1,500–3,500 one-time
- **Includes:** Professional site setup, template customization, basic copywriting, 1 training session
- **Monthly plan required after setup**

## 11. Storage Limits by Plan

| Plan | Storage Limit |
|------|---------------|
| Free | 500 MB |
| Starter | 1 GB (1024 MB) |
| Business | 2 GB (2048 MB) |
| Agency | 10 GB (10240 MB) |

## 12. Technical Implementation

### 12.1 AI Usage Tracking Table
```sql
growbuilder_ai_usage (
    id, user_id, site_id, prompt_type, 
    tokens_used, month_year, created_at
)
```

### 12.2 Tier Limit Checks
- Check `ai_prompts_limit` feature before AI calls
- Return upgrade prompt if limit exceeded
- Agency users bypass queue for priority processing

## 13. Changelog

### December 28, 2025 (Tier Restrictions Implementation)
- **TierRestrictionService**: Created centralized service for all tier restrictions
- **Frontend Integration**: 
  - Editor/Index.vue now receives aiUsage and tierRestrictions props
  - AIFloatingButton shows remaining prompts and disables when limit reached
  - AIHeader shows usage badge and upgrade banner
  - Products/Index.vue shows product limits and upgrade prompts
  - Dashboard.vue shows AI usage stats card
- **Backend Enforcement**:
  - AIController checks access before all AI operations
  - ProductController checks product limits before creation
  - MediaController checks storage limits before upload
- **AI Limits**:
  - Free: 5 prompts/month
  - Starter: 100 prompts/month + section generator
  - Business: Unlimited + SEO assistant
  - Agency: Unlimited + priority + early access
- **Storage Limits**:
  - Free: 500 MB
  - Starter: 1 GB
  - Business: 2 GB
  - Agency: 10 GB
- **Product Limits**:
  - Free: 0 (no e-commerce)
  - Starter: 20 products
  - Business: Unlimited
  - Agency: Unlimited
- **Site Limits**:
  - Free/Starter/Business: 1 site
  - Agency: 20 sites
