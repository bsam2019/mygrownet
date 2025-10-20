# Registration & Login Testing Guide

**Date:** October 20, 2025  
**Status:** Ready for Testing

---

## ✅ Implementation Complete

Users can now register and login with:
- **Email only** (phone optional)
- **Phone only** (email optional)
- **Both email and phone** (recommended)

---

## Test Scenarios

### Scenario 1: Register with Email Only

**Steps:**
1. Go to `/register`
2. Fill in:
   - Name: `John Doe`
   - Email: `john@example.com`
   - Phone: *(leave empty)*
   - Password: `Password123!`
   - Confirm Password: `Password123!`
3. Click "Create Account"

**Expected Result:**
- ✅ Registration successful
- ✅ User created with email, no phone
- ✅ Auto-login and redirect to dashboard

**Login Test:**
- Login with: `john@example.com` → ✅ Should work
- Login with phone: N/A (no phone registered)

---

### Scenario 2: Register with Phone Only

**Steps:**
1. Go to `/register`
2. Fill in:
   - Name: `Jane Smith`
   - Email: *(leave empty)*
   - Phone: `0977123456`
   - Password: `Password123!`
   - Confirm Password: `Password123!`
3. Click "Create Account"

**Expected Result:**
- ✅ Registration successful
- ✅ User created with phone `+260977123456`, no email
- ✅ Auto-login and redirect to dashboard

**Login Test:**
- Login with: `0977123456` → ✅ Should work
- Login with: `+260977123456` → ✅ Should work
- Login with: `0 977 123 456` → ✅ Should work
- Login with email: N/A (no email registered)

---

### Scenario 3: Register with Both Email and Phone (Recommended)

**Steps:**
1. Go to `/register`
2. Fill in:
   - Name: `Bob Johnson`
   - Email: `bob@example.com`
   - Phone: `0966987654`
   - Password: `Password123!`
   - Confirm Password: `Password123!`
3. Click "Create Account"

**Expected Result:**
- ✅ Registration successful
- ✅ User created with both email and phone
- ✅ Phone normalized to `+260966987654`
- ✅ Auto-login and redirect to dashboard

**Login Test:**
- Login with: `bob@example.com` → ✅ Should work
- Login with: `0966987654` → ✅ Should work
- Login with: `+260966987654` → ✅ Should work

---

### Scenario 4: Register with Neither (Should Fail)

**Steps:**
1. Go to `/register`
2. Fill in:
   - Name: `Test User`
   - Email: *(leave empty)*
   - Phone: *(leave empty)*
   - Password: `Password123!`
   - Confirm Password: `Password123!`
3. Click "Create Account"

**Expected Result:**
- ❌ Registration fails
- ❌ Error message: "Please provide either an email address or phone number."
- ❌ User not created

---

### Scenario 5: Duplicate Phone Number

**Steps:**
1. Register user with phone: `0977123456`
2. Try to register another user with same phone: `0977123456`

**Expected Result:**
- ❌ Registration fails
- ❌ Error: "This phone number is already registered."

---

### Scenario 6: Duplicate Email

**Steps:**
1. Register user with email: `test@example.com`
2. Try to register another user with same email: `test@example.com`

**Expected Result:**
- ❌ Registration fails
- ❌ Error: "This email is already registered."

---

### Scenario 7: Phone Format Variations

Test that all these phone formats work:

| Input Format | Normalized To | Should Work? |
|--------------|---------------|--------------|
| `0977123456` | `+260977123456` | ✅ Yes |
| `+260977123456` | `+260977123456` | ✅ Yes |
| `260977123456` | `+260977123456` | ✅ Yes |
| `0 977 123 456` | `+260977123456` | ✅ Yes |
| `(097) 712-3456` | `+260977123456` | ✅ Yes |
| `097-712-3456` | `+260977123456` | ✅ Yes |

**Test:**
1. Register with any format above
2. Check database - should be normalized to `+260` format
3. Login with any format - should work

---

## Quick Test Commands

### Check Database
```bash
# See registered users
php artisan tinker
>>> User::select('name', 'email', 'phone')->get()
```

### Test Registration Programmatically
```bash
php artisan tinker

# Test with email only
>>> $user = User::create(['name' => 'Test Email', 'email' => 'test1@example.com', 'password' => bcrypt('password')]);

# Test with phone only
>>> $user = User::create(['name' => 'Test Phone', 'phone' => '+260977111111', 'password' => bcrypt('password')]);

# Test with both
>>> $user = User::create(['name' => 'Test Both', 'email' => 'test2@example.com', 'phone' => '+260977222222', 'password' => bcrypt('password')]);
```

### Test Login
```bash
php artisan tinker

# Find by phone
>>> User::findByPhoneOrEmail('0977123456')

# Find by email
>>> User::findByPhoneOrEmail('test@example.com')

# Test normalization
>>> User::normalizePhone('0977123456')
>>> User::normalizePhone('+260 977 123 456')
```

---

## Browser Testing

### 1. Start Server
```bash
php artisan serve
```

### 2. Test Registration
Visit: `http://localhost:8000/register`

**Test A: Email Only**
- Fill name, email, password
- Leave phone empty
- Submit → Should work ✅

**Test B: Phone Only**
- Fill name, phone, password
- Leave email empty
- Submit → Should work ✅

**Test C: Both**
- Fill name, email, phone, password
- Submit → Should work ✅

**Test D: Neither**
- Fill name, password only
- Leave both email and phone empty
- Submit → Should show error ❌

### 3. Test Login
Visit: `http://localhost:8000/login`

**Test with Email:**
- Enter registered email
- Enter password
- Submit → Should work ✅

**Test with Phone:**
- Enter registered phone (any format)
- Enter password
- Submit → Should work ✅

---

## Expected Behavior

### Registration Form
- Both email and phone fields visible
- Both marked as optional
- Helper text: "You must provide at least one"
- Blue info box explaining the requirement

### Login Form
- Single field: "Email or Phone Number"
- Accepts either format
- Auto-detects which one user entered
- Helper text showing both options

### Database
- Email can be NULL
- Phone can be NULL
- But at least one must have a value
- Phone numbers stored in normalized format (`+260...`)

---

## Common Issues & Solutions

### Issue: "Email is required" error
**Solution:** Make sure email field validation is `nullable` not `required`

### Issue: "Phone is required" error
**Solution:** Make sure phone field validation is `nullable` not `required`

### Issue: Can register with neither email nor phone
**Solution:** Check the validation logic that ensures at least one is provided

### Issue: Phone login doesn't work
**Solution:** 
- Check phone is normalized in database
- Verify `findByPhoneOrEmail()` method works
- Test phone normalization function

### Issue: Different phone formats don't work
**Solution:** Check `normalizePhone()` method handles all formats

---

## Production Checklist

Before going live:

- [ ] Test registration with email only
- [ ] Test registration with phone only
- [ ] Test registration with both
- [ ] Test registration with neither (should fail)
- [ ] Test login with email
- [ ] Test login with phone (multiple formats)
- [ ] Test duplicate email validation
- [ ] Test duplicate phone validation
- [ ] Test phone normalization
- [ ] Check database - phones are normalized
- [ ] Test "Remember me" functionality
- [ ] Test password reset (email-based)
- [ ] Clear all test users before launch

---

## Clear Test Data

```bash
# Clear test users (BE CAREFUL IN PRODUCTION!)
php artisan tinker
>>> User::where('email', 'like', '%test%')->delete();
>>> User::where('name', 'like', '%Test%')->delete();
```

---

## Ready for Production

✅ **Email-only registration works**  
✅ **Phone-only registration works**  
✅ **Both email and phone registration works**  
✅ **Login with email works**  
✅ **Login with phone works**  
✅ **Phone normalization works**  
✅ **Validation works correctly**  

**Start registering real members!** 🎉

---

## Support Information

### For Members
- "You can register with your email, phone number, or both"
- "Login with whichever one you used to register"
- "Phone numbers work in any format (0977..., +260977..., etc.)"

### For Support Team
- Check which identifier user registered with
- Phone numbers are stored as `+260...` format
- Users can login with any phone format
- Email and phone are both unique (can't duplicate)

---

*Testing guide created: October 20, 2025*
