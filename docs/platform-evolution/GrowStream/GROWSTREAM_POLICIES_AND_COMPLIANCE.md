# GrowStream & Creator Hub — Legal, Financial, Copyright & Data Protection Policies

**Document Version**: 1.0  
**Effective Date**: August 2026  
**Jurisdiction**: Republic of Zambia (Primary) & International Cross-Border Standards  
**Applicability**: Public Streaming Consumers, Channel Creators, Creator Hub Platform Tenants (`*.mygrownet.com`), and Custom Domain Operators (`www.tenantdomain.com`).

---

## SECTION 1: MASTER CREATOR HUB PLATFORM TERMS OF SERVICE (B2B TENANT AGREEMENT)

### 1.1 Acceptance & Scope
By activating a Creator Platform Hub on MyGrowNet (`/hub` or `/creator/platform`), the subscribing entity ("Tenant", "Academy", or "Creator") agrees to these Terms of Service. These Terms govern the operation of hosted subdomains (e.g. `mrbanda.mygrownet.com`) and connected custom domains (e.g. `www.mymathstuition.com`).

### 1.2 Tenant Portal & Domain Allocation
1. **Hosted Subdomains**: GrowStream provisions subdomains under `mygrownet.com`. GrowStream retains ownership of `mygrownet.com` root domains. Subdomain allocation is non-exclusive, non-transferable, and revocable upon material breach.
2. **Custom Domains**: Tenants connecting custom CNAME domains carry primary domain brand association. The Tenant is solely responsible for acquiring, maintaining, and renewing its custom domain names.

### 1.3 Service Level Agreement (SLA) & Availability
- **Platform Target Uptime**: 99.5% monthly availability for video streaming and checkout endpoints, excluding scheduled maintenance windows.
- **Quota Enforcements**: Video storage allowances (Cloudflare Stream) and streaming delivery limits are metered by `TenantUsageMeter.php`. Exceeding 100% quota triggers streaming throttles unless upgraded.

### 1.4 Acceptable Use Policy (AUP)
Tenants shall **NOT** publish, stream, or store:
- Copyright-infringing video, audio, or broadcast material.
- Content advocating hate speech, violence, illegal gambling, or illegal financial schemes.
- Malicious code, phishing scripts, or unauthorized data collection forms.

---

## SECTION 2: CREATOR FINANCIAL & BYOP TREASURY POLICY

### 2.1 Dual Payment Architecture
BizStream and Creator Hub operate under two distinct money-flow modes:

```text
                                  PAYMENT MODE RESOLUTION
                                             │
             ┌───────────────────────────────┴───────────────────────────────┐
             ▼                                                               ▼
   GROWSTREAM-MANAGED (Default)                                  BRING YOUR OWN PAYMENT (BYOP)
 (Payments flow through MyGrowNet Rails)                     (Payments flow directly to Tenant Gateway)
             │                                                               │
 ├─ Revenue Collection: MTN, Airtel, Zamtel, Cards            ├─ Tenant Gateway: Paystack / Flutterwave / PawaPay
 ├─ Platform Fee Deducted: Configurable % (e.g. 15%)          ├─ Customer Money: Land direct in Tenant Account
 └─ Net Payout: Remitted to Tenant Payout Account             └─ Platform Fee: Billed via Platform Subscription
```

### 2.2 GrowStream-Managed Escrow & Payout Schedule
1. **Escrow Position**: Funds collected by GrowStream on behalf of Tenants are held in dedicated merchant escrow accounts.
2. **Payout Threshold**: Minimum disbursement threshold is **K250** (Zambian Kwacha).
3. **Disbursement Rails**: Payouts are executed via MTN Mobile Money, Airtel Money, Zamtel Kwacha, or Zambian Commercial Bank EFT / RTGS.
4. **Payout Schedule**: Weekly disbursements (processed every Monday) or monthly on the 1st business day.

### 2.3 Bring Your Own Payment (BYOP) Framework
- **Direct Merchant Settlement**: When BYOP is enabled, end-customer payments pass directly from the customer to the Tenant's connected gateway account (Paystack, Flutterwave, PawaPay). **Funds never pass through GrowStream.**
- **Platform Fee Collection**: In BYOP mode, GrowStream collects its platform fee via flat monthly/annual subscription plans or metered usage billing.
- **Refunds & Chargebacks**: In BYOP mode, refunds and disputes are handled directly between the Tenant and its payment provider.

### 2.4 Tax Compliance & Deductions (Zambia Revenue Authority - ZRA)
1. **Value Added Tax (VAT)**: Where applicable under Zambian tax law, GrowStream collects 16% VAT on platform subscriptions.
2. **Withholding Tax (WHT)**: Creator earnings disbursements under the public 70% model are subject to Zambian Withholding Tax (15% on royalties/creator fees) where statutory mandates apply.

---

## SECTION 3: COPYRIGHT, PIRACY & CONTENT MODERATION POLICY

### 3.1 Statutory Alignment
This policy complies with the **Zambian Copyright and Performance Rights Act (Cap 406)** and international **Notice-and-Takedown (DMCA / EU Copyright Directive)** standards.

### 3.2 Automated Cloudflare Stream Piracy Mitigation
1. **Ingest Fingerprinting**: All uploaded video assets undergo automated hash matching against Cloudflare Stream's audio/video fingerprint database upon `tusComplete`.
2. **Flagging & Pre-processing**: Videos flagged for commercial copyright infringement are automatically placed in `moderation_status = 'copyright_flagged'` and blocked from public or portal playback prior to manual review.

### 3.3 DMCA & Copyright Takedown Procedure
Rights holders may file copyright infringement claims to `copyright@mygrownet.com` containing:
- Identification of the copyrighted work.
- Exact URL / Subdomain where the infringing video is hosted.
- Contact information of the claimant.
- A statement of good-faith belief.

Upon receiving a valid notice:
- GrowStream will disable access to the target video within **24 hours**.
- GrowStream will notify the Tenant operator with a 7-day counter-notice window.

### 3.4 Strike Policy & Tenant Termination
- **Strike 1**: Warning + video removal.
- **Strike 2**: Temporary suspension of video upload privileges for 14 days.
- **Strike 3**: Permanent termination of the Creator Hub platform and subdomain revocation.

---

## SECTION 4: DATA PROTECTION & PRIVACY POLICY

### 4.1 Statutory Compliance
This section strictly adheres to the **Zambian Data Protection Act No. 4 of 2021 (DPA)** and international **General Data Protection Regulation (GDPR)** principles.

### 4.2 Data Ownership & Tenant Scoping
- **Tenant Audience Data**: Student, employee, or subscriber records registered under a Creator Hub (`mrbanda.mygrownet.com`) belong to the Tenant entity.
- **Zero Cross-Tenant Contamination**: Server-side request scoping (`ResolveDomainContext` + `SetPlatformContext`) guarantees Tenant A cannot view or export Tenant B's customer data under any circumstances.

### 4.3 Data Subject Rights
End-users and students possess statutory rights under Zambia DPA 2021:
1. **Right of Access**: View all personal information and progress records held.
2. **Right to Rectification**: Correct inaccuracies in user profiles.
3. **Right to Erasure ("Right to be Forgotten")**: Request deletion of personal records, subject to financial record retention laws.

### 4.4 Data Sovereignty & International Transfers
- Customer data is encrypted in transit (TLS 1.3) and at rest (AES-256).
- Where cross-border data transfer occurs (e.g. Cloudflare global edge delivery), data processing agreements guarantee equivalent protection levels to the Zambian DPA 2021.
