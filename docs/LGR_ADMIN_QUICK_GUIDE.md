# LGR Manual Awards - Admin Quick Guide

**Quick reference for awarding LGR bonuses to premium members**

---

## How to Award a Bonus

### Step 1: Navigate to Awards Page
- Go to **Admin Panel** → **LGR** → **Manual Awards**
- Click the **"Award Bonus"** button

### Step 2: Select Member
- Search for member by name, email, or phone
- Only premium members appear in the list
- Click to select the member

### Step 3: Enter Award Details
- **Amount**: K10 - K2,100 (must be in increments of 10)
- **Award Type**: Choose from dropdown
  - Early Adopter
  - Performance
  - Marketing
  - Special
- **Reason**: Explain why (minimum 10 characters)

### Step 4: Confirm & Submit
- Review the details in the confirmation dialog
- Click **"Yes, Award Bonus"**
- Member receives notification immediately

---

## Award Types Explained

| Type | When to Use | Example |
|------|-------------|---------|
| **Early Adopter** | First 100 premium members | "Thank you for being among our first premium members" |
| **Performance** | Exceptional engagement | "Outstanding platform activity and referrals this month" |
| **Marketing** | Promotional campaigns | "Special bonus for October referral campaign" |
| **Special** | Unique situations | "Compensation for technical issue experienced" |

---

## Best Practices

✅ **DO:**
- Provide clear, specific reasons
- Award consistently based on criteria
- Keep records of award campaigns
- Use appropriate award types
- Verify member eligibility first

❌ **DON'T:**
- Award without clear justification
- Exceed K2,100 per award
- Award non-premium members
- Use vague reasons like "bonus"

---

## What Happens After Award

1. ✅ Member's LGC balance updated immediately
2. ✅ Transaction record created for audit
3. ✅ Email sent to member with details
4. ✅ In-app notification appears in their dashboard
5. ✅ Award appears in admin awards list

---

## Troubleshooting

**Member not appearing in list?**
- Verify they have premium starter kit
- Check their account status is "active"

**Award failed?**
- Check amount is within limits (K10-K2,100)
- Ensure reason is at least 10 characters
- Verify member still exists and is active

**Notification not sent?**
- Award still processes successfully
- Check queue worker is running: `php artisan queue:work`
- Check member's email address is valid

---

## Quick Stats

View on the Manual Awards page:
- **Total Awarded**: All-time LGC awarded
- **Total Recipients**: Unique members who received awards
- **This Month**: Current month's total awards

---

## Need Help?

Contact technical support or refer to the full documentation:
- `docs/LGR_MANUAL_AWARDS.md` - Complete system documentation
- `docs/LGR_MANUAL_AWARDS_QUICKSTART.md` - Detailed walkthrough
