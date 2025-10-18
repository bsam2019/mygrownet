# Withdrawal Management

This guide covers the management of withdrawal requests, approval processes, and emergency withdrawal procedures in the VBIF system.

## Overview

The VBIF withdrawal system implements a comprehensive policy framework with:
- **12-month lock-in period** for all investments
- **Tiered penalty structure** for early withdrawals
- **Multi-level approval process** for emergency withdrawals
- **Automated policy enforcement** with manual override capabilities

## Withdrawal Types

### 1. Standard Withdrawal (Post Lock-in)
- **Eligibility**: After 12-month lock-in period
- **Penalty**: None
- **Approval**: Automatic
- **Processing Time**: 3-5 business days

### 2. Partial Withdrawal
- **Eligibility**: Profits only, after lock-in period
- **Penalty**: None (profits only)
- **Approval**: Automatic
- **Processing Time**: 3-5 business days

### 3. Emergency Withdrawal
- **Eligibility**: Any time with justification
- **Penalty**: 50% of withdrawable amount
- **Approval**: Manual admin approval required
- **Processing Time**: 1-2 business days after approval

### 4. Early Withdrawal
- **Eligibility**: Before lock-in period ends
- **Penalty**: Tiered based on time elapsed
- **Approval**: Manual admin approval required
- **Processing Time**: 3-5 business days after approval

## Withdrawal Dashboard Access

### Admin Dashboard Navigation

```bash
# Access withdrawal management dashboard
https://vbif.com/admin/withdrawals

# Command line access
php artisan withdrawal:admin-dashboard
```

### Dashboard Overview

The withdrawal dashboard provides:
- **Pending Requests**: Withdrawals awaiting approval
- **Processing Queue**: Approved withdrawals being processed
- **Recent Activity**: Recently completed withdrawals
- **Statistics**: Withdrawal metrics and trends
- **Policy Configuration**: Withdrawal policy settings

## Managing Withdrawal Requests

### Viewing Pending Requests

```bash
# List all pending withdrawal requests
php artisan withdrawal:list-pending

# Filter by type
php artisan withdrawal:list-pending --type=emergency

# Filter by amount range
php artisan withdrawal:list-pending --min-amount=1000 --max-amount=10000

# Filter by date range
php artisan withdrawal:list-pending --from=2025-08-01 --to=2025-08-07
```

**Sample Output:**
```
Pending Withdrawal Requests
===========================
ID: 123 | User: John Doe (ID: 456) | Amount: K2,500 | Type: Emergency
Investment: K5,000 (Builder Tier) | Requested: 2025-08-07 10:30:00
Reason: Medical emergency - hospital bills
Penalty: K1,250 | Net Amount: K1,250
Status: PENDING_APPROVAL | Priority: HIGH

ID: 124 | User: Jane Smith (ID: 789) | Amount: K1,000 | Type: Partial
Investment: K3,000 (Starter Tier) | Requested: 2025-08-07 09:15:00
Reason: Personal financial needs
Penalty: K0 | Net Amount: K1,000
Status: PENDING | Priority: NORMAL
```

### Detailed Request Review

```bash
# View detailed withdrawal request
php artisan withdrawal:view-details --request-id=123

# View user's investment history
php artisan withdrawal:user-history --user-id=456

# Check withdrawal eligibility
php artisan withdrawal:check-eligibility --request-id=123
```

**Detailed Request Output:**
```
Withdrawal Request Details - ID: 123
====================================
User Information:
- Name: John Doe
- Email: john.doe@example.com
- Phone: +1234567890
- Member Since: 2024-07-15
- Current Tier: Builder
- Total Investments: K15,000

Investment Details:
- Investment ID: 789
- Amount: K5,000
- Date: 2025-01-15
- Tier: Builder (7% annual return)
- Current Value: K5,291.67
- Lock-in End: 2026-01-15
- Days Remaining: 161

Withdrawal Request:
- Type: Emergency
- Amount Requested: K2,500
- Reason: Medical emergency - hospital bills
- Supporting Documents: medical_bill_scan.pdf
- Requested Date: 2025-08-07 10:30:00

Penalty Calculation:
- Base Penalty Rate: 50% (emergency withdrawal)
- Penalty Amount: K1,250
- Net Withdrawable: K1,250
- Commission Clawback: K250 (affects referrer)

Risk Assessment:
- Fraud Risk: LOW
- Pattern Analysis: First emergency request
- Account Standing: GOOD
- Verification Status: VERIFIED

Recommendation: APPROVE (Medical emergency with documentation)
```

### Approving Withdrawal Requests

#### Web Interface Approval

1. **Navigate to Admin Dashboard**
   - Login to admin panel
   - Go to Withdrawals → Pending Requests
   - Click on request ID to view details

2. **Review Request Details**
   - Verify user information
   - Check investment eligibility
   - Review supporting documentation
   - Assess penalty calculations

3. **Make Approval Decision**
   - Click "Approve" or "Reject"
   - Enter approval/rejection reason
   - Add admin notes if necessary
   - Confirm decision

#### Command Line Approval

```bash
# Approve withdrawal request
php artisan withdrawal:approve --request-id=123 --admin-id=1 --reason="Medical emergency verified with documentation"

# Approve with custom processing priority
php artisan withdrawal:approve --request-id=123 --admin-id=1 --priority=high --reason="Urgent medical situation"

# Bulk approve multiple requests
php artisan withdrawal:bulk-approve --request-ids=123,124,125 --admin-id=1 --reason="Batch approval after review"
```

### Rejecting Withdrawal Requests

```bash
# Reject withdrawal request
php artisan withdrawal:reject --request-id=123 --admin-id=1 --reason="Insufficient documentation provided"

# Reject with specific feedback
php artisan withdrawal:reject --request-id=123 --admin-id=1 --reason="Medical bills not verified" --feedback="Please provide official hospital invoice"

# Bulk reject requests
php artisan withdrawal:bulk-reject --request-ids=126,127 --admin-id=1 --reason="Policy violation - multiple emergency requests"
```

## Processing Approved Withdrawals

### Processing Queue Management

```bash
# View processing queue
php artisan withdrawal:processing-queue

# Process next batch of approved withdrawals
php artisan withdrawal:process-batch --batch-size=50

# Process specific withdrawal
php artisan withdrawal:process --request-id=123

# Monitor processing status
php artisan withdrawal:monitor-processing
```

### Payment Processing

```bash
# Generate payment batch file
php artisan withdrawal:generate-payment-batch --date=2025-08-07

# Export payment instructions
php artisan withdrawal:export-payments --format=csv --date=2025-08-07

# Mark payments as completed
php artisan withdrawal:mark-completed --batch-id=20250807-001

# Handle payment failures
php artisan withdrawal:handle-payment-failures --batch-id=20250807-001
```

**Payment Batch Output:**
```
Payment Batch: 20250807-001
===========================
Generated: 2025-08-07 14:00:00
Total Requests: 25
Total Amount: K125,000
Payment Method Breakdown:
- Bank Transfer: 20 requests, K100,000
- Mobile Money: 5 requests, K25,000

Bank Transfer Instructions:
Account: VBIF Withdrawal Account
Bank: National Bank of Zambia
Account Number: 1234567890
Reference Format: WD-{REQUEST_ID}-{DATE}

Mobile Money Instructions:
Provider: MTN Mobile Money
Account: VBIF-WITHDRAWALS
Reference Format: VBIF-WD-{REQUEST_ID}
```

## Emergency Procedures

### Emergency Withdrawal Halt

```bash
# Immediately halt all withdrawal processing
php artisan withdrawal:emergency-halt --reason="System security breach detected"

# Halt specific withdrawal types
php artisan withdrawal:emergency-halt --type=emergency --reason="Policy review required"

# Check halt status
php artisan withdrawal:check-halt-status
```

### Emergency Withdrawal Override

```bash
# Override withdrawal policies for emergency
php artisan withdrawal:emergency-override --request-id=123 --admin-id=1 --reason="Life-threatening medical emergency"

# Expedite critical withdrawal
php artisan withdrawal:expedite --request-id=123 --processing-time=immediate --reason="Critical medical situation"
```

### Fraud Prevention and Detection

```bash
# Run fraud detection on pending requests
php artisan withdrawal:fraud-detection --scan-pending

# Flag suspicious withdrawal patterns
php artisan withdrawal:flag-suspicious --user-id=456 --reason="Multiple emergency requests in short period"

# Review flagged requests
php artisan withdrawal:review-flagged

# Generate fraud report
php artisan withdrawal:fraud-report --period=monthly
```

## Policy Management

### Withdrawal Policy Configuration

```bash
# View current withdrawal policies
php artisan withdrawal:view-policies

# Update lock-in period
php artisan withdrawal:update-policy --policy=lock_in_period --value=12 --unit=months

# Update penalty rates
php artisan withdrawal:update-penalty-rates --emergency=50 --early-0-3-months=100 --early-3-6-months=50

# Configure processing times
php artisan withdrawal:update-processing-times --standard=5 --emergency=2 --early=5
```

**Current Policy Configuration:**
```
VBIF Withdrawal Policies
========================
Lock-in Period: 12 months
Minimum Withdrawal: K100
Maximum Daily Withdrawals: K50,000

Penalty Structure:
- Emergency Withdrawal: 50% of amount
- Early Withdrawal (0-3 months): 100% profit + 12% capital
- Early Withdrawal (3-6 months): 50% profit + 6% capital
- Early Withdrawal (6-12 months): 30% profit + 3% capital
- Post Lock-in: No penalty

Processing Times:
- Standard Withdrawal: 3-5 business days
- Emergency Withdrawal: 1-2 business days (after approval)
- Early Withdrawal: 3-5 business days (after approval)

Approval Requirements:
- Standard: Automatic
- Emergency: Manual admin approval + documentation
- Early: Manual admin approval + justification
```

### Policy Override Management

```bash
# Create temporary policy override
php artisan withdrawal:create-override --policy=penalty_rate --value=25 --duration=7 --reason="Holiday season compassionate adjustment"

# List active overrides
php artisan withdrawal:list-overrides

# Remove policy override
php artisan withdrawal:remove-override --override-id=123
```

## Reporting and Analytics

### Withdrawal Reports

```bash
# Generate daily withdrawal report
php artisan withdrawal:daily-report --date=2025-08-07

# Generate monthly summary
php artisan withdrawal:monthly-report --month=2025-08

# Generate annual analysis
php artisan withdrawal:annual-report --year=2025

# Export detailed withdrawal data
php artisan withdrawal:export-data --format=excel --period=quarterly --year=2025 --quarter=Q3
```

### Performance Metrics

```bash
# View withdrawal statistics
php artisan withdrawal:statistics --period=monthly

# Analyze approval rates
php artisan withdrawal:approval-analysis --period=quarterly

# Monitor processing times
php artisan withdrawal:processing-metrics --period=weekly

# Generate compliance report
php artisan withdrawal:compliance-report --year=2025
```

**Sample Statistics Output:**
```
Withdrawal Statistics - August 2025
===================================
Total Requests: 156
Approved: 142 (91.0%)
Rejected: 14 (9.0%)
Processing: 8
Completed: 134

By Type:
- Standard: 89 requests (57.1%)
- Partial: 45 requests (28.8%)
- Emergency: 15 requests (9.6%)
- Early: 7 requests (4.5%)

Average Processing Time:
- Standard: 4.2 days
- Emergency: 1.8 days
- Early: 4.5 days

Total Amount Processed: K678,500
Average Withdrawal: K4,349
Penalties Collected: K45,600

Top Rejection Reasons:
1. Insufficient documentation (35.7%)
2. Policy violation (28.6%)
3. Fraud suspicion (21.4%)
4. Technical issues (14.3%)
```

## User Communication

### Automated Notifications

```bash
# Configure withdrawal notifications
php artisan withdrawal:configure-notifications --email=true --sms=true --push=false

# Send status update notifications
php artisan withdrawal:send-status-updates --request-ids=123,124,125

# Resend notification for specific request
php artisan withdrawal:resend-notification --request-id=123 --type=approval
```

### Manual Communication

```bash
# Send custom message to user
php artisan withdrawal:send-message --user-id=456 --subject="Withdrawal Request Update" --message="Your emergency withdrawal has been approved and will be processed within 24 hours."

# Bulk communication for delayed processing
php artisan withdrawal:bulk-message --status=processing --subject="Processing Delay Notice" --template=processing_delay
```

## Troubleshooting Common Issues

### Issue 1: Withdrawal Stuck in Processing

**Symptoms:**
- Withdrawal approved but not processed
- Payment batch generation fails
- User complaints about delays

**Diagnosis:**
```bash
# Check processing queue status
php artisan withdrawal:queue-status

# View specific request processing log
php artisan withdrawal:processing-log --request-id=123

# Check payment system connectivity
php artisan withdrawal:test-payment-system
```

**Resolution:**
```bash
# Retry failed processing
php artisan withdrawal:retry-processing --request-id=123

# Manually process withdrawal
php artisan withdrawal:manual-process --request-id=123 --admin-id=1

# Reset processing status
php artisan withdrawal:reset-processing --request-id=123
```

### Issue 2: Penalty Calculation Errors

**Symptoms:**
- Incorrect penalty amounts
- User disputes penalty calculations
- System calculation mismatches

**Diagnosis:**
```bash
# Validate penalty calculations
php artisan withdrawal:validate-penalties --request-id=123

# Recalculate penalties
php artisan withdrawal:recalculate-penalties --request-id=123

# Check policy configuration
php artisan withdrawal:check-policy-config
```

**Resolution:**
```bash
# Correct penalty calculation
php artisan withdrawal:correct-penalty --request-id=123 --correct-amount=1000 --reason="Calculation error correction"

# Update policy configuration
php artisan withdrawal:update-policy --policy=penalty_calculation --fix-errors=true
```

### Issue 3: Approval Workflow Issues

**Symptoms:**
- Requests stuck in pending status
- Approval notifications not sent
- Dual approval process failures

**Diagnosis:**
```bash
# Check approval workflow status
php artisan withdrawal:workflow-status --request-id=123

# View approval history
php artisan withdrawal:approval-history --request-id=123

# Test notification system
php artisan withdrawal:test-notifications
```

**Resolution:**
```bash
# Reset approval workflow
php artisan withdrawal:reset-workflow --request-id=123

# Force approval completion
php artisan withdrawal:force-approval --request-id=123 --admin-id=1 --reason="Workflow system error"

# Fix notification system
php artisan withdrawal:fix-notifications
```

## Security and Compliance

### Audit Trail

All withdrawal management activities are logged with:
- **Administrator Identity**: Who performed the action
- **Timestamp**: When the action occurred
- **Action Details**: What was done
- **Request Information**: Which withdrawal was affected
- **Reason/Justification**: Why the action was taken
- **System Context**: IP address, session info

### Dual Approval Process

For high-value or emergency withdrawals:
1. **Primary Approval**: First administrator reviews and approves
2. **Secondary Approval**: Second administrator confirms approval
3. **System Validation**: Automated checks before processing
4. **Audit Logging**: Complete trail of approval process

### Compliance Monitoring

```bash
# Generate compliance report
php artisan withdrawal:compliance-report --period=monthly

# Check regulatory compliance
php artisan withdrawal:regulatory-check --jurisdiction=zambia

# Export audit trail
php artisan withdrawal:export-audit --format=csv --period=quarterly
```

## Best Practices

### Daily Operations Checklist

- [ ] Review pending withdrawal requests
- [ ] Process approved withdrawals
- [ ] Monitor processing queue status
- [ ] Check for system alerts or errors
- [ ] Review fraud detection reports
- [ ] Update withdrawal statistics
- [ ] Respond to user inquiries
- [ ] Verify payment batch completions

### Weekly Operations Checklist

- [ ] Analyze withdrawal trends and patterns
- [ ] Review and update policy configurations
- [ ] Conduct fraud pattern analysis
- [ ] Generate weekly performance reports
- [ ] Review and resolve escalated issues
- [ ] Update documentation as needed
- [ ] Train staff on new procedures
- [ ] Backup withdrawal data

### Monthly Operations Checklist

- [ ] Comprehensive withdrawal analysis
- [ ] Policy effectiveness review
- [ ] Compliance audit and reporting
- [ ] System performance optimization
- [ ] Staff performance evaluation
- [ ] Update emergency procedures
- [ ] Review and update training materials
- [ ] Stakeholder reporting

## Advanced Withdrawal Management

### Blockchain Integration

#### Cryptocurrency Withdrawal Support
```bash
# Configure cryptocurrency withdrawals
php artisan withdrawal:configure-crypto --currencies=BTC,ETH,USDT --minimum-amounts

# Process crypto withdrawal
php artisan withdrawal:process-crypto --request-id=123 --currency=BTC --address=1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa

# Monitor blockchain confirmations
php artisan withdrawal:monitor-blockchain --request-id=123 --required-confirmations=6

# Generate crypto withdrawal report
php artisan withdrawal:crypto-report --period=monthly --include-fees
```

### AI-Powered Fraud Detection

#### Machine Learning Fraud Prevention
```bash
# Train fraud detection model
php artisan withdrawal:train-fraud-model --training-data=historical-withdrawals --features=amount,frequency,location

# Run AI fraud detection
php artisan withdrawal:ai-fraud-detection --request-id=123 --confidence-threshold=0.8

# Update fraud detection rules
php artisan withdrawal:update-fraud-rules --based-on-ml-insights --auto-tune

# Generate AI fraud report
php artisan withdrawal:ai-fraud-report --period=weekly --model-performance
```

### Regulatory Compliance Automation

#### Automated Compliance Checking
```bash
# Configure compliance rules
php artisan withdrawal:configure-compliance --aml-threshold=10000 --kyc-requirements

# Run compliance checks
php artisan withdrawal:compliance-check --request-id=123 --comprehensive

# Generate regulatory reports
php artisan withdrawal:regulatory-report --period=monthly --format=xml --authority=boz

# Submit compliance filings
php artisan withdrawal:submit-compliance --period=2025-08 --authority=fic --electronic
```

## Contact Information

### Withdrawal Management Team
- **Withdrawal Manager**: withdrawal.manager@vbif.com
- **Financial Controller**: sarah.wilson@vbif.com
- **System Administrator**: john.smith@vbif.com
- **Compliance Officer**: compliance@vbif.com
- **Blockchain Specialist**: blockchain@vbif.com
- **AI/ML Engineer**: ai-ml@vbif.com

### Emergency Contacts
- **Emergency Hotline**: +1-800-VBIF-911
- **After-hours Support**: +1-800-VBIF-247
- **Technical Escalation**: tech-escalation@vbif.com
- **Fraud Hotline**: fraud-alert@vbif.com
- **Regulatory Emergency**: regulatory-emergency@vbif.com