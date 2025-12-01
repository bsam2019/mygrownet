# Production Account Issues - Comprehensive Fix Guide

**Last Updated:** November 28, 2024  
**Status:** Ready for Production Use

## Overview

Based on the Esaya account fix, this comprehensive solution identifies and fixes common account issues across all production users:

- Missing email addresses
- Missing or invalid passwords
- Missing user profiles
- Inconsistent phone number formats
- Duplicate email/phone numbers
- Authentication problems

## Quick Start

### 1. Safe Check (Recommended First)
```bash
# Check for issues without making changes
./deployment/check-production-accounts.sh --check
```

### 2. Fix All Issues
```bash
# Fix all issues with database backup
./deployment/check-production-accounts.sh --fix --backup
```

### 3. Check Specific User
```bash
# Check specific user (like Esaya - ID 11)
./deployment/check-production-accounts.sh --user=11
```

## Available Scripts

### 1. `check-production-account-issues.php`
**Main diagnostic and fix script**

**Local Usage:**
```bash
# Check only
php check-production-account-issues.php --check

# Fix issues
php check-production-account-issues.php --fix

# Specific user
php check-production-account-issues.php --user=123
```

### 2. `deployment/check-production-accounts.sh`
**Production deployment script**

**Features:**
- Safe SSH connection testing
- Automatic script upload/cleanup
- Database backup option
- Report file download
- Dry-run mode

**Usage:**
```bash
# Safe check only
./deployment/check-production-accounts.sh --check

# Fix with backup
./deployment/check-production-accounts.sh --fix --backup

# Specific user
./deployment/check-production-accounts.sh --user=11

# Preview changes (dry run)
./deployment/check-production-accounts.sh --fix --dry-run
```

## Issues Detected & Fixed

### 1. Missing Email Addresses
**Problem:** Users without email addresses cannot log in via email
**Fix:** Generate email based on name: `firstname.lastname@mygrownet.com`
**Fallback:** `user.{id}@mygrownet.com`

### 2. Missing Passwords
**Problem:** Users without passwords cannot authenticate
**Fix:** Set temporary password: `TempPass{id}!`
**Action Required:** Notify users to change password

### 3. Invalid Password Hashes
**Problem:** Plain text passwords or corrupted hashes
**Fix:** Re-hash plain text passwords using bcrypt

### 4. Missing User Profiles
**Problem:** Application expects user profiles for full functionality
**Fix:** Create basic profile with default values:
- Phone: Copy from user table
- Address: "Lusaka, Zambia"
- DOB: "1990-01-01"

### 5. Inconsistent Phone Formats
**Problem:** Various phone formats cause login issues
**Fix:** Standardize to `+260XXXXXXXXX` format
- `0976123456` → `+260976123456`
- `260976123456` → `+260976123456`

### 6. Duplicate Email Addresses
**Problem:** Multiple users with same email
**Fix:** Keep first user, modify others: `email.{id}@domain.com`

### 7. No Contact Information
**Problem:** Users without email OR phone
**Fix:** Set default email: `user.{id}@mygrownet.com`

## Safety Features

### Database Backup
```bash
# Always backup before fixes
./deployment/check-production-accounts.sh --fix --backup
```

### Dry Run Mode
```bash
# Preview changes without executing
./deployment/check-production-accounts.sh --fix --dry-run
```

### Check-Only Mode
```bash
# Identify issues without fixing
./deployment/check-production-accounts.sh --check
```

## Report Files

Each run generates a detailed JSON report:
- `account-issues-report-YYYY-MM-DD-HH-MM-SS.json`
- Contains all issues found and fixes applied
- Automatically downloaded from production server

## Example Output

```
=== PRODUCTION ACCOUNT ISSUES ANALYSIS ===
Mode: CHECK & FIX
Time: 2024-11-28 10:30:00

📊 Total users in system: 150

1. CHECKING FOR MISSING EMAIL ADDRESSES
--------------------------------------------------
Found: 5 users without email addresses
  - ID 11: Esaya Nkhata (Phone: +260976311664)
  ✅ Fixed: Set email to: esaya.nkhata@mygrownet.com
  - ID 23: John Doe (Phone: +260977123456)
  ✅ Fixed: Set email to: john.doe@mygrownet.com

2. CHECKING FOR MISSING/INVALID PASSWORDS
--------------------------------------------------
Found: 2 users without passwords
  - ID 45: Jane Smith (Email: jane@example.com)
  ✅ Fixed: Set temporary password: TempPass45!

📋 FINAL SUMMARY REPORT
======================================================================
📊 Statistics:
  Total users analyzed: 150
  Users with issues: 8
  Total issues found: 12
  Fixes applied: 12

⚠️  ISSUES FOUND & FIXED:
  - Missing email addresses: 5 users (FIXED)
  - Missing passwords: 2 users (FIXED)
  - Inconsistent phone formats: 3 users (FIXED)
  - Missing user profiles: 2 users (FIXED)
```

## Post-Fix Actions

### 1. Verify Fixes
```bash
# Re-run check to verify all issues resolved
./deployment/check-production-accounts.sh --check
```

### 2. Test Authentication
- Test login with affected users
- Check Laravel logs for authentication errors
- Verify both email and phone login work

### 3. Notify Users
- Send password reset emails for temporary passwords
- Inform users of any email address changes
- Provide login instructions

### 4. Monitor System
- Watch for login failure rates
- Monitor user feedback
- Check error logs regularly

## Troubleshooting

### SSH Connection Issues
```bash
# Test SSH connection manually
ssh root@167.99.70.175 "echo 'Connection test'"

# Check SSH key configuration
ssh-add -l
```

### Script Execution Errors
```bash
# Check PHP version and extensions
ssh root@167.99.70.175 "cd /var/www/mygrownet && php -v"

# Check Laravel environment
ssh root@167.99.70.175 "cd /var/www/mygrownet && php artisan --version"
```

### Database Issues
```bash
# Test database connection
ssh root@167.99.70.175 "cd /var/www/mygrownet && php artisan tinker --execute='DB::connection()->getPdo();'"
```

## Security Considerations

1. **Temporary Passwords:** Change immediately after setting
2. **Email Changes:** Verify with users before changing
3. **Database Backups:** Always backup before bulk changes
4. **Access Logs:** Monitor for unusual login patterns
5. **User Notification:** Inform users of account changes

## Integration with Existing Fixes

This solution builds on the Esaya fix (`fix-esaya-account.php`) and extends it to:
- Handle all users systematically
- Provide comprehensive reporting
- Include safety mechanisms
- Support production deployment

## Maintenance Schedule

**Weekly:** Run check-only mode to identify new issues
**Monthly:** Run full analysis with fixes if needed
**After User Registration:** Monitor for validation issues
**Before Major Updates:** Verify account integrity

## Related Files

- `fix-esaya-account.php` - Original Esaya-specific fix
- `check-similar-account-issues.php` - Initial diagnostic script
- `fix-user-credentials.php` - Legacy credential fix script
- `LOGIN_CREDENTIALS_FIX.md` - Previous login fix documentation

---

## Quick Commands Reference

```bash
# Safe production check
./deployment/check-production-accounts.sh --check

# Fix with backup
./deployment/check-production-accounts.sh --fix --backup

# Check specific user
./deployment/check-production-accounts.sh --user=11

# Local testing
php check-production-account-issues.php --check

# Preview fixes
./deployment/check-production-accounts.sh --fix --dry-run
```

**Remember:** Always run `--check` first to understand the scope of issues before applying fixes.