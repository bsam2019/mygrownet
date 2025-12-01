# Investor Portal - Quick Start Guide

**Last Updated:** November 24, 2025  
**Status:** Production Ready

---

## 🚀 Quick Access URLs

```
Public Page:        /investors
Investor Login:     /investor/login
Investor Dashboard: /investor/dashboard
Admin Rounds:       /admin/investment-rounds
Admin Accounts:     /admin/investor-accounts
```

---

## 📋 Quick Workflows

### Record Your First Investment

```
1. Go to /admin/investor-accounts
2. Click "Record Investment"
3. Fill in:
   - Name: John Doe
   - Email: john@example.com
   - Round: Select active round
   - Amount: 50000
   - Equity: 2.5
   - Date: Today
4. Save
5. ✅ Get access code (e.g., JOHN1)
6. Send access code to investor
7. Investor can login at /investor/login
```

### Create Investment Round

```
1. Go to /admin/investment-rounds
2. Click "Create New Round"
3. Fill in details
4. Save as draft
5. Click "Activate"
6. Click "Set as Featured"
7. ✅ Appears on /investors
```

### Convert Investor to Shareholder

```
1. Go to /admin/investor-accounts
2. Find investor with "CIU" badge
3. Click "Convert"
4. Confirm
5. ✅ Status changes to "Shareholder"
```

---

## 🎯 Key Features

| Feature | URL | What It Does |
|---------|-----|--------------|
| **Public Page** | `/investors` | Shows metrics & investment opportunity |
| **Investor Login** | `/investor/login` | Investor portal authentication |
| **Investor Dashboard** | `/investor/dashboard` | View investment details & performance |
| **Investment Rounds** | `/admin/investment-rounds` | Create & manage fundraising rounds |
| **Investor Accounts** | `/admin/investor-accounts` | Track actual investments |
| **Auto Updates** | Automatic | Rounds update when investments recorded |
| **Status Tracking** | Admin panel | CIU → Shareholder → Exited |

---

## 📊 Dashboard Metrics

### Public Page Shows:
- Total Members
- Monthly Revenue
- Active Rate
- Retention Rate
- Revenue Growth Chart
- Current Investment Opportunity

### Admin Accounts Shows:
- Total Investors
- Total Invested
- All Investor Accounts
- Status Distribution

---

## 🔄 Investor Lifecycle

```
1. Inquiry → Investor submits interest form
2. CIU → Record investment (Convertible Investment Unit)
3. Shareholder → Convert when milestone reached
4. Exited → Mark when investor exits
```

---

## ⚡ Quick Commands

```bash
# View all investors
php artisan tinker
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::all();

# Check total invested
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::sum('investment_amount');

# Count by status
>>> App\Infrastructure\Persistence\Eloquent\Investor\InvestorAccountModel::where('status', 'ciu')->count();
```

---

## 🎨 Status Badges

| Status | Badge Color | Meaning |
|--------|-------------|---------|
| **CIU** | Blue | Convertible Investment Unit |
| **Shareholder** | Green | Converted to equity shares |
| **Exited** | Gray | Investor has exited |

---

## ✅ Testing Checklist

Quick test to verify everything works:

- [ ] Visit `/investors` - See public page
- [ ] Create investment round
- [ ] Activate and feature round
- [ ] Verify round appears on public page
- [ ] Record test investment
- [ ] Check round raised_amount updated
- [ ] Convert investor to shareholder
- [ ] Mark investor as exited
- [ ] View totals in admin dashboard

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| `INVESTOR_DASHBOARD_FINAL_SUMMARY.md` | Complete overview |
| `INVESTOR_ACCOUNT_MANAGEMENT_GUIDE.md` | Detailed account management |
| `INVESTMENT_ROUNDS_ADMIN_GUIDE.md` | Rounds management |
| `INVESTOR_PORTAL_COMPLETION_SUMMARY.md` | What was built |
| `INVESTOR_PORTAL_QUICK_START.md` | This file |

---

## 🆘 Common Issues

### Round not showing on public page?
- Check status is "Active"
- Verify "is_featured" is true
- Clear cache: `php artisan cache:clear`

### Investment not updating round?
- Check investment_round_id is correct
- Verify round exists in database

### Can't convert investor?
- Verify status is "CIU"
- Check investor exists

---

## 🎉 You're Ready!

Your investor portal is complete with:
- ✅ Public landing page
- ✅ Investment rounds management
- ✅ Investor account tracking
- ✅ Automatic updates
- ✅ Status lifecycle management

**Start sharing `/investors` with potential investors!** 🚀

---

## Need Help?

Check the detailed guides:
- Account management → `INVESTOR_ACCOUNT_MANAGEMENT_GUIDE.md`
- Rounds management → `INVESTMENT_ROUNDS_ADMIN_GUIDE.md`
- Complete overview → `INVESTOR_DASHBOARD_FINAL_SUMMARY.md`
