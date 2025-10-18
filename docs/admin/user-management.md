# User Management

This guide covers comprehensive user account management, role-based permissions, and user lifecycle management in the VBIF system.

## Overview

The VBIF user management system provides:
- **Role-based Access Control (RBAC)**: Hierarchical permission system
- **User Lifecycle Management**: Registration, activation, suspension, deletion
- **Investment Tier Management**: Tier assignments and upgrades
- **Security Controls**: Account verification, fraud prevention, audit trails

## User Roles and Permissions

### Role Hierarchy

#### Super Administrator
- **Full system access**: All administrative functions
- **User management**: Create, modify, delete any user account
- **System configuration**: Modify system settings and policies
- **Financial oversight**: Access to all financial data and controls
- **Emergency procedures**: Execute emergency protocols

#### Financial Administrator
- **Investment management**: Oversee investment processing and approvals
- **Withdrawal approvals**: Approve withdrawal requests and emergency withdrawals
- **Profit distribution**: Manage annual and quarterly distributions
- **Financial reporting**: Generate and access financial reports
- **Tier management**: Manage investment tier configurations

#### Support Administrator
- **User support**: Handle user inquiries and basic account issues
- **Account verification**: Verify user identities and documents
- **Basic reporting**: Generate user activity and support reports
- **Limited modifications**: Basic account updates and password resets

#### Read-Only Administrator
- **View access**: Read-only access to system data
- **Report generation**: Generate reports without modification rights
- **System monitoring**: Monitor system health and performance
- **Audit access**: View audit logs and system activities

#### Investment Manager
- **Investment oversight**: Monitor investment performance and metrics
- **Tier analysis**: Analyze tier performance and recommend changes
- **Commission management**: Oversee referral commission calculations
- **Investment reporting**: Generate investment-specific reports

### Permission Management

#### Viewing User Permissions
```bash
# List all user roles and permissions
php artisan user:list-permissions --detailed

# View specific user permissions
php artisan user:show-permissions --user-id=123

# Check permission for specific action
php artisan user:check-permission --user-id=123 --permission=withdraw.approve

# List users by role
php artisan user:list-by-role --role=financial-administrator
```

#### Assigning Permissions
```bash
# Assign role to user
php artisan user:assign-role --user-id=123 --role=support-administrator

# Grant specific permission
php artisan user:grant-permission --user-id=123 --permission=investment.view

# Revoke permission
php artisan user:revoke-permission --user-id=123 --permission=user.delete

# Bulk role assignment
php artisan user:bulk-assign-role --user-ids="123,456,789" --role=read-only-administrator
```

## User Account Management

### User Registration and Onboarding

#### New User Registration
```bash
# View pending registrations
php artisan user:pending-registrations

# Approve user registration
php artisan user:approve-registration --user-id=123 --admin-id=1

# Reject user registration
php artisan user:reject-registration --user-id=123 --reason="Incomplete documentation"

# Bulk approve registrations
php artisan user:bulk-approve --user-ids="123,456,789" --admin-id=1
```

#### User Verification Process
```bash
# Check user verification status
php artisan user:verification-status --user-id=123

# Manually verify user
php artisan user:manual-verify --user-id=123 --admin-id=1 --reason="Documents verified offline"

# Request additional verification
php artisan user:request-verification --user-id=123 --type=id-document --reason="Unclear document image"

# Bulk verification status check
php artisan user:bulk-verification-check --user-ids="123,456,789"
```

### Account Status Management

#### Account Status Types
- **Active**: Full system access and functionality
- **Pending**: Registration pending approval
- **Suspended**: Temporary access restriction
- **Blocked**: Permanent access restriction
- **Inactive**: Account deactivated by user
- **Deleted**: Account marked for deletion

#### Managing Account Status
```bash
# View user account status
php artisan user:status --user-id=123

# Suspend user account
php artisan user:suspend --user-id=123 --reason="Suspicious activity detected" --admin-id=1

# Reactivate suspended account
php artisan user:reactivate --user-id=123 --admin-id=1 --reason="Investigation completed"

# Block user account permanently
php artisan user:block --user-id=123 --reason="Fraud confirmed" --admin-id=1

# Bulk status update
php artisan user:bulk-status-update --user-ids="123,456" --status=suspended --reason="Policy violation"
```

### User Profile Management

#### Profile Information Updates
```bash
# View user profile
php artisan user:profile --user-id=123 --detailed

# Update user information
php artisan user:update-profile --user-id=123 --field=email --value=newemail@example.com --admin-id=1

# Update contact information
php artisan user:update-contact --user-id=123 --phone="+1234567890" --admin-id=1

# Bulk profile updates
php artisan user:bulk-update --csv-file=user_updates.csv --admin-id=1
```

#### Investment Tier Management
```bash
# View user's current tier
php artisan user:current-tier --user-id=123

# Manually upgrade user tier
php artisan user:upgrade-tier --user-id=123 --tier=Leader --reason="Manual adjustment" --admin-id=1

# Downgrade user tier
php artisan user:downgrade-tier --user-id=123 --tier=Starter --reason="Investment reduction" --admin-id=1

# View tier upgrade history
php artisan user:tier-history --user-id=123

# Bulk tier updates
php artisan user:bulk-tier-update --csv-file=tier_updates.csv --admin-id=1
```

## Investment Management

### User Investment Overview

#### Investment Portfolio Management
```bash
# View user's investment portfolio
php artisan user:investment-portfolio --user-id=123

# View investment history
php artisan user:investment-history --user-id=123 --period=1y

# Check investment eligibility
php artisan user:investment-eligibility --user-id=123 --amount=5000

# View investment performance
php artisan user:investment-performance --user-id=123 --detailed
```

#### Investment Modifications
```bash
# Adjust user investment
php artisan user:adjust-investment --user-id=123 --investment-id=456 --amount=1000 --reason="Calculation correction" --admin-id=1

# Cancel pending investment
php artisan user:cancel-investment --user-id=123 --investment-id=456 --reason="User request" --admin-id=1

# Transfer investment between users
php artisan user:transfer-investment --from-user=123 --to-user=456 --investment-id=789 --admin-id=1
```

### Referral Management

#### Referral Tree Management
```bash
# View user's referral tree
php artisan user:referral-tree --user-id=123 --levels=3

# View referral statistics
php artisan user:referral-stats --user-id=123

# Manually add referral relationship
php artisan user:add-referral --sponsor-id=123 --referral-id=456 --admin-id=1

# Remove referral relationship
php artisan user:remove-referral --sponsor-id=123 --referral-id=456 --reason="Duplicate account" --admin-id=1
```

#### Commission Management
```bash
# View user's commission history
php artisan user:commission-history --user-id=123 --period=3m

# Adjust commission amount
php artisan user:adjust-commission --user-id=123 --commission-id=789 --amount=100 --reason="Calculation error" --admin-id=1

# Process pending commissions
php artisan user:process-commissions --user-id=123

# Clawback commission
php artisan user:clawback-commission --user-id=123 --commission-id=789 --reason="Referral withdrawal" --admin-id=1
```

## Security and Fraud Management

### Account Security

#### Password Management
```bash
# Force password reset
php artisan user:force-password-reset --user-id=123 --reason="Security breach"

# Check password strength
php artisan user:check-password-strength --user-id=123

# View password history
php artisan user:password-history --user-id=123

# Bulk password reset
php artisan user:bulk-password-reset --user-ids="123,456,789" --reason="Security update"
```

#### Two-Factor Authentication
```bash
# Check 2FA status
php artisan user:2fa-status --user-id=123

# Enable 2FA for user
php artisan user:enable-2fa --user-id=123 --method=sms

# Disable 2FA
php artisan user:disable-2fa --user-id=123 --admin-id=1 --reason="Lost phone access"

# Reset 2FA secret
php artisan user:reset-2fa --user-id=123 --admin-id=1
```

### Fraud Detection and Prevention

#### Suspicious Activity Monitoring
```bash
# Check for suspicious activity
php artisan user:check-suspicious --user-id=123

# View fraud risk score
php artisan user:fraud-score --user-id=123

# Flag user for review
php artisan user:flag-for-review --user-id=123 --reason="Multiple IP addresses" --admin-id=1

# Review flagged users
php artisan user:review-flagged --priority=high
```

#### Account Verification
```bash
# Check verification documents
php artisan user:check-documents --user-id=123

# Request additional documents
php artisan user:request-documents --user-id=123 --type=proof-of-address

# Approve verification documents
php artisan user:approve-documents --user-id=123 --admin-id=1

# Reject verification documents
php artisan user:reject-documents --user-id=123 --reason="Document unclear" --admin-id=1
```

## Bulk Operations

### Bulk User Management

#### Mass User Operations
```bash
# Bulk user import from CSV
php artisan user:bulk-import --csv-file=users.csv --validate-first

# Bulk user export
php artisan user:bulk-export --format=csv --criteria="tier=Builder,status=active"

# Bulk email to users
php artisan user:bulk-email --template=system-update --criteria="status=active"

# Bulk SMS notification
php artisan user:bulk-sms --message="System maintenance tonight" --criteria="tier=Elite"
```

#### Data Migration and Cleanup
```bash
# Migrate user data
php artisan user:migrate-data --from-system=legacy --batch-size=100

# Clean up inactive accounts
php artisan user:cleanup-inactive --older-than=2y --dry-run

# Merge duplicate accounts
php artisan user:merge-duplicates --primary-id=123 --duplicate-id=456 --admin-id=1

# Archive old user data
php artisan user:archive-data --older-than=5y --to-cold-storage
```

## Reporting and Analytics

### User Analytics

#### User Statistics
```bash
# Generate user statistics report
php artisan user:statistics --period=monthly --format=pdf

# User growth analysis
php artisan user:growth-analysis --period=quarterly

# User engagement metrics
php artisan user:engagement-metrics --period=weekly

# Tier distribution analysis
php artisan user:tier-distribution --detailed
```

#### Activity Reporting
```bash
# User activity report
php artisan user:activity-report --user-id=123 --period=30d

# Login activity analysis
php artisan user:login-analysis --period=weekly --suspicious-only

# Investment activity report
php artisan user:investment-activity --period=monthly --by-tier

# Referral activity report
php artisan user:referral-activity --period=quarterly
```

### Compliance Reporting

#### Regulatory Compliance
```bash
# Generate KYC compliance report
php artisan user:kyc-compliance --period=monthly

# AML compliance report
php artisan user:aml-compliance --suspicious-only

# Data retention compliance
php artisan user:data-retention-compliance --check-violations

# Export compliance data
php artisan user:export-compliance --regulation=GDPR --format=csv
```

## System Integration

### External System Integration

#### CRM Integration
```bash
# Sync users to CRM
php artisan user:sync-to-crm --batch-size=100

# Import users from CRM
php artisan user:import-from-crm --validate-first

# Update CRM with user changes
php artisan user:update-crm --changed-since="2025-08-01"
```

#### Email Marketing Integration
```bash
# Sync users to email marketing platform
php artisan user:sync-to-email-platform --active-only

# Update email preferences
php artisan user:update-email-preferences --user-id=123 --preferences="newsletter,updates"

# Segment users for marketing
php artisan user:create-segments --criteria="tier=Elite,active=true"
```

## Troubleshooting User Issues

### Common User Problems

#### Login Issues
```bash
# Diagnose login problems
php artisan user:diagnose-login --user-id=123

# Check account lockout status
php artisan user:check-lockout --user-id=123

# Unlock user account
php artisan user:unlock-account --user-id=123 --admin-id=1

# Reset login attempts counter
php artisan user:reset-login-attempts --user-id=123
```

#### Investment Issues
```bash
# Diagnose investment problems
php artisan user:diagnose-investment --user-id=123 --investment-id=456

# Fix investment calculation errors
php artisan user:fix-investment-calculation --user-id=123 --recalculate-all

# Resolve tier upgrade issues
php artisan user:fix-tier-upgrade --user-id=123 --force-recalculate

# Fix referral commission issues
php artisan user:fix-commissions --user-id=123 --recalculate-tree
```

#### Account Access Issues
```bash
# Diagnose access problems
php artisan user:diagnose-access --user-id=123

# Check permission issues
php artisan user:check-permissions --user-id=123 --action=investment.create

# Fix role assignment issues
php artisan user:fix-roles --user-id=123 --reset-to-default

# Resolve verification issues
php artisan user:fix-verification --user-id=123 --force-verify
```

## Data Protection and Privacy

### GDPR Compliance

#### Data Subject Rights
```bash
# Export user data (Right to Data Portability)
php artisan user:export-data --user-id=123 --format=json --include-all

# Delete user data (Right to be Forgotten)
php artisan user:delete-data --user-id=123 --confirm --admin-id=1

# Anonymize user data
php artisan user:anonymize-data --user-id=123 --keep-financial-records

# Update user consent
php artisan user:update-consent --user-id=123 --consent-type=marketing --status=withdrawn
```

#### Data Retention Management
```bash
# Check data retention compliance
php artisan user:check-retention --user-id=123

# Apply retention policies
php artisan user:apply-retention-policies --dry-run

# Archive old user data
php artisan user:archive-old-data --older-than=7y

# Purge expired data
php artisan user:purge-expired-data --confirm
```

## Audit and Compliance

### Audit Trail Management

#### User Action Auditing
```bash
# View user audit trail
php artisan user:audit-trail --user-id=123 --period=30d

# Search audit logs
php artisan user:search-audit --action=login --period=7d --failed-only

# Export audit data
php artisan user:export-audit --user-id=123 --format=csv --period=1y

# Generate audit report
php artisan user:audit-report --period=monthly --include-admin-actions
```

#### Compliance Monitoring
```bash
# Monitor compliance violations
php artisan user:compliance-violations --severity=high

# Generate compliance dashboard
php artisan user:compliance-dashboard --period=quarterly

# Check regulatory requirements
php artisan user:check-regulatory-compliance --jurisdiction=zambia

# Update compliance status
php artisan user:update-compliance-status --user-id=123 --status=compliant
```

## Best Practices

### User Management Best Practices

#### Account Security
- Enforce strong password policies
- Require two-factor authentication for high-value accounts
- Regular security audits and reviews
- Monitor for suspicious activity patterns
- Implement account lockout policies

#### Data Management
- Regular data backups and verification
- Implement data retention policies
- Ensure GDPR compliance for EU users
- Maintain audit trails for all actions
- Regular data quality checks

#### Access Control
- Follow principle of least privilege
- Regular permission reviews and updates
- Implement role-based access control
- Monitor administrative actions
- Regular access certification

### Operational Procedures

#### Daily Tasks
- [ ] Review new user registrations
- [ ] Process pending verifications
- [ ] Monitor suspicious activity alerts
- [ ] Review and resolve user support tickets
- [ ] Check system health and user metrics

#### Weekly Tasks
- [ ] Review user access permissions
- [ ] Analyze user activity patterns
- [ ] Update user documentation
- [ ] Review fraud detection reports
- [ ] Process bulk user operations

#### Monthly Tasks
- [ ] Generate user analytics reports
- [ ] Review and update user policies
- [ ] Conduct security audits
- [ ] Update compliance documentation
- [ ] Review system performance metrics

## Emergency Procedures

### Account Compromise Response

#### Immediate Actions
```bash
# Suspend compromised account
php artisan user:emergency-suspend --user-id=123 --reason="Account compromise suspected"

# Reset all user sessions
php artisan user:reset-all-sessions --user-id=123

# Force password change
php artisan user:force-password-change --user-id=123

# Enable enhanced monitoring
php artisan user:enable-enhanced-monitoring --user-id=123 --duration=30d
```

#### Investigation and Recovery
```bash
# Generate security incident report
php artisan user:security-incident-report --user-id=123 --incident-id=SEC-2025-001

# Review account activity
php artisan user:review-activity --user-id=123 --period=30d --suspicious-only

# Restore account after investigation
php artisan user:restore-account --user-id=123 --admin-id=1 --reason="Investigation completed"
```

## Contact Information

### User Management Team
- **User Management Lead**: user.manager@vbif.com
- **Security Officer**: mike.johnson@vbif.com
- **Compliance Officer**: compliance@vbif.com
- **Support Team Lead**: support.lead@vbif.com

### Emergency Contacts
- **Emergency Hotline**: +1-800-VBIF-911
- **Security Incident Response**: security-incident@vbif.com
- **Fraud Alert Line**: fraud-alert@vbif.com
- **Compliance Emergency**: compliance-emergency@vbif.com

### External Contacts
- **Legal Counsel**: legal@vbif.com
- **Data Protection Officer**: dpo@vbif.com
- **External Auditor**: auditor@external-audit.com
- **Regulatory Liaison**: regulatory@vbif.com

Remember: Always follow the principle of least privilege when managing user accounts, maintain detailed audit trails for all administrative actions, and ensure compliance with applicable data protection regulations.