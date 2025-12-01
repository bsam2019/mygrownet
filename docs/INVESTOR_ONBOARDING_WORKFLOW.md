# Investor Onboarding Workflow

**Date:** November 24, 2025  
**Type:** Admin-Controlled (Private Limited Company)  
**Status:** Production Ready

---

## Why Admin-Controlled?

As a **private limited company**, MyGrowNet requires admin approval for all investors to ensure:

✅ **Regulatory Compliance** - Meet securities regulations  
✅ **Due Diligence** - Verify investor identity and suitability  
✅ **Legal Requirements** - Private placements require approval  
✅ **Accredited Investor Status** - Confirm investor qualifications  
✅ **Relationship Building** - Personal interaction before investment  
✅ **Quality Control** - Ensure alignment with company values  

---

## Complete Onboarding Flow

### Step 1: Investor Inquiry (Public)

**Investor Action:**
1. Visits `/investors` (public landing page)
2. Reviews platform metrics and investment opportunity
3. Fills out inquiry form:
   - Name
   - Email
   - Phone
   - Investment range interest
   - Message/questions
4. Submits inquiry

**System Action:**
- Stores inquiry in `investor_inquiries` table
- Status: "pending"
- Timestamp recorded

---

### Step 2: Admin Review (Internal)

**Admin Action:**
1. Reviews inquiry in admin panel (future feature)
2. Evaluates investor:
   - Investment amount suitable?
   - Accredited investor?
   - Background check passed?
   - Alignment with company?
3. Decides: Approve or Decline

**Communication:**
- Email/call investor to discuss
- Schedule meeting if needed
- Explain investment terms
- Answer questions
- Send investment documents

---

### Step 3: Investment Agreement (Legal)

**Process:**
1. Admin sends investment agreement
2. Investor reviews with legal counsel
3. Both parties sign agreement
4. Investor transfers funds
5. Admin confirms receipt

**Documents:**
- Convertible Investment Agreement
- Subscription Agreement
- Disclosure Documents
- Terms & Conditions

---

### Step 4: Record Investment (Admin)

**Admin Action:**
1. Go to `/admin/investor-accounts`
2. Click "Record Investment"
3. Fill in details:
   ```
   Name: John Doe
   Email: john@example.com
   User ID: (optional - if also a member)
   Investment Amount: K50,000
   Investment Date: 2025-11-24
   Investment Round: Seed Round
   Equity Percentage: 2.5%
   ```
4. Save

**System Action:**
- Creates investor account
- Generates access code (e.g., `JOHN123`)
- Updates investment round raised amount
- Sets status to "CIU"
- Displays success message with access code

---

### Step 5: Send Access Code (Admin)

**Admin Action:**
1. Copy access code from success message
2. Send welcome email to investor

**Email Template:**
```
Subject: Welcome to MyGrowNet - Investor Portal Access

Dear John,

Thank you for your investment of K50,000 in MyGrowNet!

Your investment has been recorded and you now have access to your 
investor portal where you can track your investment performance.

INVESTOR PORTAL ACCESS:
URL: https://mygrownet.com/investor/login
Email: john@example.com
Access Code: JOHN123

In your portal, you can:
✓ View your investment details
✓ Track company performance
✓ Monitor your equity value
✓ Access investment documents
✓ Receive quarterly updates

If you have any questions, please contact:
investors@mygrownet.com
+260 XXX XXX XXX

Best regards,
MyGrowNet Investor Relations Team
```

---

### Step 6: Investor Login (Investor)

**Investor Action:**
1. Visits `/investor/login`
2. Enters email and access code
3. Logs into dashboard
4. Views investment details
5. Explores portal features

**What Investor Sees:**
- Investment amount
- Equity percentage
- Investment status (CIU)
- Round progress
- Company valuation
- Potential ROI
- Documents

---

## Workflow Diagram

```
┌─────────────────────────────────────────────────────────┐
│  1. INQUIRY (Public)                                    │
│  Investor submits interest via /investors               │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  2. REVIEW (Admin)                                      │
│  Admin reviews inquiry, conducts due diligence          │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  3. AGREEMENT (Legal)                                   │
│  Send docs, sign agreement, receive funds               │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  4. RECORD (Admin)                                      │
│  Admin records investment in system                     │
│  System generates access code                           │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  5. NOTIFY (Admin)                                      │
│  Admin sends welcome email with access code             │
└────────────────────┬────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│  6. ACCESS (Investor)                                   │
│  Investor logs in and tracks investment                 │
└─────────────────────────────────────────────────────────┘
```

---

## Admin Responsibilities

### Initial Onboarding
- [ ] Review investor inquiries
- [ ] Conduct due diligence
- [ ] Verify accredited investor status
- [ ] Schedule meetings/calls
- [ ] Send investment documents
- [ ] Collect signed agreements
- [ ] Confirm fund receipt
- [ ] Record investment in system
- [ ] Send access code to investor

### Ongoing Management
- [ ] Update investor status (CIU → Shareholder)
- [ ] Upload quarterly reports
- [ ] Communicate company updates
- [ ] Process investor questions
- [ ] Manage document access
- [ ] Track conversion milestones
- [ ] Handle exit events

---

## Inquiry Management (Future Enhancement)

### Admin Inquiry Dashboard
Create admin interface to manage inquiries:

```
/admin/investor-inquiries

Features:
- View all inquiries
- Filter by status (pending/approved/declined)
- Filter by investment range
- Add notes to inquiries
- Update inquiry status
- Send emails directly
- Convert inquiry to investor account
- Track conversion funnel
```

### Inquiry Statuses
- **Pending** - New inquiry, needs review
- **Reviewing** - Under evaluation
- **Approved** - Ready to invest
- **Declined** - Not suitable
- **Converted** - Became investor

---

## Due Diligence Checklist

Before recording investment:

### Identity Verification
- [ ] Government-issued ID verified
- [ ] Address confirmed
- [ ] Contact information validated

### Financial Verification
- [ ] Accredited investor status confirmed
- [ ] Source of funds verified
- [ ] Investment amount appropriate
- [ ] Financial capacity assessed

### Legal Compliance
- [ ] Anti-money laundering (AML) check
- [ ] Know Your Customer (KYC) completed
- [ ] Sanctions list screening
- [ ] Tax identification verified

### Suitability Assessment
- [ ] Investment objectives align
- [ ] Risk tolerance appropriate
- [ ] Understanding of terms confirmed
- [ ] Long-term commitment verified

---

## Communication Templates

### 1. Inquiry Acknowledgment
```
Subject: Thank you for your interest in MyGrowNet

Dear [Name],

Thank you for your inquiry about investing in MyGrowNet.

We have received your information and will review it shortly. 
A member of our investor relations team will contact you within 
2-3 business days to discuss the opportunity.

In the meantime, feel free to review our platform at:
https://mygrownet.com/investors

Best regards,
MyGrowNet Team
```

### 2. Meeting Invitation
```
Subject: Investment Discussion - MyGrowNet

Dear [Name],

Thank you for your interest in investing in MyGrowNet.

I would like to schedule a call to discuss the investment 
opportunity and answer any questions you may have.

Please let me know your availability for a 30-minute call 
this week.

Looking forward to speaking with you.

Best regards,
[Your Name]
Investor Relations
```

### 3. Document Request
```
Subject: Investment Documents - MyGrowNet

Dear [Name],

Following our discussion, please find attached the investment 
documents for your review:

- Convertible Investment Agreement
- Company Overview & Financials
- Terms & Conditions
- Disclosure Documents

Please review these with your legal and financial advisors. 
Once you're ready to proceed, we can arrange the signing and 
fund transfer.

Please let me know if you have any questions.

Best regards,
[Your Name]
```

### 4. Welcome Email (with access code)
See Step 5 above for complete template.

---

## Security & Compliance

### Data Protection
- Investor data encrypted at rest
- Secure transmission (HTTPS)
- Access logs maintained
- Regular security audits

### Regulatory Compliance
- Securities regulations followed
- Private placement rules adhered to
- Investor limits respected
- Proper documentation maintained

### Record Keeping
- All communications documented
- Agreements stored securely
- Transaction records maintained
- Audit trail preserved

---

## Best Practices

### 1. Timely Communication
- Respond to inquiries within 24-48 hours
- Keep investors updated on progress
- Set clear expectations on timeline

### 2. Professional Documentation
- Use proper legal agreements
- Maintain consistent branding
- Provide clear instructions
- Include contact information

### 3. Personal Touch
- Schedule calls/meetings when possible
- Build relationships before investment
- Understand investor motivations
- Provide excellent service

### 4. Transparency
- Be honest about risks
- Share accurate information
- Provide regular updates
- Maintain open communication

---

## Metrics to Track

### Conversion Funnel
- Inquiries received
- Inquiries reviewed
- Meetings scheduled
- Agreements sent
- Investments closed
- Conversion rate

### Time Metrics
- Time to first response
- Time to meeting
- Time to agreement
- Time to investment
- Average onboarding time

### Quality Metrics
- Average investment size
- Investor satisfaction
- Retention rate
- Referral rate

---

## Future Enhancements

### Phase 1 (Current) ✅
- Public inquiry form
- Admin records investments
- Manual access code distribution
- Investor portal access

### Phase 2 (Planned)
- [ ] Admin inquiry management dashboard
- [ ] Automated email templates
- [ ] Document upload system
- [ ] Status tracking workflow

### Phase 3 (Planned)
- [ ] E-signature integration
- [ ] Automated KYC/AML checks
- [ ] Payment gateway integration
- [ ] Automated onboarding emails

### Phase 4 (Planned)
- [ ] Investor CRM integration
- [ ] Advanced analytics
- [ ] Investor communication portal
- [ ] Automated compliance checks

---

## Summary

✅ **Admin-controlled onboarding** - Proper for private limited company  
✅ **Due diligence process** - Ensures compliance and quality  
✅ **Manual verification** - Personal touch and relationship building  
✅ **Secure access** - Access codes generated after approval  
✅ **Professional workflow** - Clear steps from inquiry to portal access  

**This workflow ensures regulatory compliance while providing excellent investor experience!** 🎯

---

## Related Documentation

- `INVESTOR_PORTAL_LOGIN_GUIDE.md` - Login system details
- `INVESTOR_ACCOUNT_MANAGEMENT_GUIDE.md` - Account management
- `INVESTOR_DASHBOARD_FINAL_SUMMARY.md` - Complete system overview
- `INVESTOR_PORTAL_QUICK_START.md` - Quick reference
