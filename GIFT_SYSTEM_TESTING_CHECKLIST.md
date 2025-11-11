# Gift Starter Kit - Testing Checklist

**Date:** November 11, 2025  
**Status:** Ready for Testing

## Pre-Testing Setup

### 1. Database Setup
- [x] Migrations run successfully
- [x] Gift settings seeded
- [ ] Test users created with network relationships

### 2. Configuration Check
```bash
# Verify gift settings exist
php artisan tinker
>>> App\Infrastructure\Persistence\Eloquent\Settings\GiftSettingsModel::first()
```

Expected output:
```
max_gifts_per_month: 5
max_gift_amount_per_month: 5000
min_wallet_balance_to_gift: 1000
gift_feature_enabled: true
gift_fee_percentage: 0
```

## Automated Testing

### Run Test Script
```bash
php artisan tinker < scripts/test-gift-starter-kit.php
```

**Expected Results:**
- [ ] Test users created/found
- [ ] Gift settings loaded
- [ ] Wallet balance checked/topped up
- [ ] Gift executed successfully
- [ ] Recipient receives starter kit
- [ ] Announcement created
- [ ] Wallet debited correctly

## Manual Testing - Happy Path

### Test Case 1: Basic Gift Flow
**Setup:**
1. [ ] Login as user with downline members
2. [ ] Ensure wallet balance > K1,000
3. [ ] Identify downline member without starter kit

**Steps:**
1. [ ] Open mobile dashboard
2. [ ] Navigate to network section
3. [ ] Click on Level 1 (or any level)
4. [ ] Verify "Gift Starter Kit" button appears for members without kits
5. [ ] Click "Gift Starter Kit" button
6. [ ] Modal opens with recipient info
7. [ ] Select "Basic" tier (K500)
8. [ ] Verify limits display correctly
9. [ ] Click "Confirm Gift"
10. [ ] Success message appears
11. [ ] Modal closes

**Verification:**
- [ ] Recipient now has starter kit
- [ ] Gifter's wallet debited K500
- [ ] Gift record created in database
- [ ] Recipient sees announcement
- [ ] Transaction appears in wallet history

### Test Case 2: Premium Gift Flow
**Steps:**
1. [ ] Open gift modal for another member
2. [ ] Select "Premium" tier (K1,000)
3. [ ] Confirm gift

**Verification:**
- [ ] Wallet debited K1,000
- [ ] Recipient gets premium starter kit
- [ ] All benefits activated

## Edge Cases Testing

### Test Case 3: Insufficient Balance
**Setup:**
- [ ] User with wallet balance < K500

**Expected:**
- [ ] Error message: "Insufficient wallet balance"
- [ ] Gift button disabled or shows warning
- [ ] No transaction created

### Test Case 4: Monthly Limit Reached (Count)
**Setup:**
- [ ] User who has already gifted 5 times this month

**Expected:**
- [ ] Error message: "You have reached your monthly gift limit"
- [ ] Cannot proceed with gift

### Test Case 5: Monthly Limit Reached (Amount)
**Setup:**
- [ ] User who has gifted K5,000 this month

**Expected:**
- [ ] Error message: "You have exceeded your monthly gift amount limit"
- [ ] Cannot proceed with gift

### Test Case 6: Recipient Already Has Starter Kit
**Setup:**
- [ ] Try to gift to member who already has starter kit

**Expected:**
- [ ] Gift button should NOT appear
- [ ] If API called directly, error returned

### Test Case 7: Not in Downline
**Setup:**
- [ ] Try to gift to user not in downline

**Expected:**
- [ ] Member should not appear in level list
- [ ] If API called directly, error returned

### Test Case 8: Feature Disabled
**Setup:**
```php
GiftSettingsModel::first()->update(['gift_feature_enabled' => false]);
```

**Expected:**
- [ ] Error message: "Gift feature is currently disabled"
- [ ] Cannot proceed with gift

### Test Case 9: Below Minimum Balance
**Setup:**
- [ ] User with K800 balance (below K1,000 minimum)

**Expected:**
- [ ] Warning message about minimum balance
- [ ] Cannot proceed with gift

## UI/UX Testing

### Mobile Responsiveness
- [ ] Modal displays correctly on small screens
- [ ] Buttons are easily tappable
- [ ] Text is readable
- [ ] No horizontal scrolling

### Visual Design
- [ ] Colors match design system (blue for basic, indigo for premium)
- [ ] Icons display correctly
- [ ] Loading states work
- [ ] Success/error messages styled properly

### User Experience
- [ ] Modal animations smooth
- [ ] Loading indicators during processing
- [ ] Clear error messages
- [ ] Confirmation before action
- [ ] Easy to cancel

## Integration Testing

### Wallet Integration
- [ ] Correct amount debited
- [ ] Transaction recorded with proper description
- [ ] Balance updates immediately
- [ ] Transaction appears in history

### Starter Kit Integration
- [ ] Starter kit purchase created
- [ ] Correct tier assigned
- [ ] `gifted_by` field populated
- [ ] All starter kit benefits activated
- [ ] Shop credit added (if applicable)
- [ ] Library access granted

### Announcement Integration
- [ ] Announcement created for recipient
- [ ] Correct title and message
- [ ] 7-day expiry set
- [ ] Appears in recipient's dashboard
- [ ] Can be dismissed

### Network Integration
- [ ] Only downline members shown
- [ ] Correct level filtering
- [ ] Member data accurate
- [ ] `has_starter_kit` flag correct

## API Testing

### Endpoint: POST /mygrownet/gifts/starter-kit
```bash
curl -X POST http://localhost/mygrownet/gifts/starter-kit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"recipient_id": 123, "tier": "basic"}'
```

**Test Cases:**
- [ ] Valid request returns success
- [ ] Invalid recipient_id returns 404
- [ ] Invalid tier returns validation error
- [ ] Insufficient balance returns error
- [ ] Limits exceeded returns error

### Endpoint: GET /mygrownet/gifts/limits
```bash
curl http://localhost/mygrownet/gifts/limits \
  -H "Authorization: Bearer {token}"
```

**Verify Response:**
- [ ] All limit fields present
- [ ] Correct calculations
- [ ] Current balance accurate

### Endpoint: GET /mygrownet/network/level/{level}/members
```bash
curl http://localhost/mygrownet/network/level/1/members \
  -H "Authorization: Bearer {token}"
```

**Verify Response:**
- [ ] Correct level members returned
- [ ] `has_starter_kit` flag accurate
- [ ] All member fields present

## Performance Testing

### Load Testing
- [ ] Gift 10 starter kits in quick succession
- [ ] Check for race conditions
- [ ] Verify all transactions atomic
- [ ] No duplicate gifts created

### Database Performance
- [ ] Queries optimized (check query log)
- [ ] Indexes used properly
- [ ] No N+1 queries

## Security Testing

### Authorization
- [ ] Cannot gift to non-downline members
- [ ] Cannot bypass wallet balance check
- [ ] Cannot exceed limits via API manipulation
- [ ] CSRF protection working

### Validation
- [ ] All inputs validated
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] Type checking enforced

## Regression Testing

### Existing Features
- [ ] Regular starter kit purchase still works
- [ ] Wallet operations unaffected
- [ ] Network display unchanged
- [ ] Announcements system working
- [ ] Other dashboard features functional

## Browser Testing

### Desktop Browsers
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge

### Mobile Browsers
- [ ] Chrome Mobile
- [ ] Safari iOS
- [ ] Samsung Internet

## Accessibility Testing

- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] Color contrast sufficient
- [ ] Focus indicators visible
- [ ] Error messages announced

## Documentation Review

- [ ] API documentation accurate
- [ ] User guide clear
- [ ] Admin instructions complete
- [ ] Troubleshooting guide helpful

## Production Readiness

### Code Quality
- [ ] No console.log statements
- [ ] No commented code
- [ ] Proper error handling
- [ ] Code formatted consistently

### Monitoring
- [ ] Error logging configured
- [ ] Success metrics tracked
- [ ] Performance monitoring ready

### Rollback Plan
- [ ] Feature flag exists
- [ ] Can disable via settings
- [ ] Database rollback tested

## Sign-Off

### Development Team
- [ ] Code reviewed
- [ ] Tests passing
- [ ] Documentation complete

### QA Team
- [ ] All test cases passed
- [ ] Edge cases verified
- [ ] Performance acceptable

### Product Owner
- [ ] Feature meets requirements
- [ ] UX approved
- [ ] Ready for production

---

## Test Results Summary

**Date Tested:** _______________  
**Tested By:** _______________  
**Environment:** _______________

**Results:**
- Total Tests: ___
- Passed: ___
- Failed: ___
- Blocked: ___

**Critical Issues:** _______________

**Recommendation:** [ ] Approve for Production [ ] Needs Fixes

---

## Notes

Use this space for additional observations, issues found, or recommendations:

```
[Your notes here]
```
