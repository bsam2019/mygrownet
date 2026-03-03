# Budget Management Guide

**Last Updated:** 2026-03-02  
**Status:** Production Ready  
**Phase:** CMS Integration - Phase 2

## Overview

The Budget Management system allows administrators to create budgets in CMS and compare them against actual spending from the transactions table. This provides real-time visibility into budget performance and helps identify overspending before it becomes a problem.

## Key Features

- **Budget vs Actual Comparison**: Compare budgeted amounts with actual transactions
- **Variance Analysis**: See over/under budget amounts and percentages
- **Performance Metrics**: Track categories that are over budget, on track, or under budget
- **Critical Alerts**: Get notified when categories exceed 120% of budget
- **Unbudgeted Expenses**: Identify expenses that weren't included in the budget
- **Multi-Period Support**: View budgets for today, week, month, quarter, or year
- **Real-Time Data**: Automatically syncs with transactions table

## Accessing Budget Management

### From Admin Dashboard

1. Log in as Administrator
2. Navigate to **Finance** section in sidebar
3. Click **Budget Management** (3rd item, has CMS badge)
4. Direct URL: `http://yourdomain.com/admin/budget`

## Creating a Budget

Budgets are created in the CMS system:

1. From Budget Management dashboard, click **"Manage Budgets"** button
2. Or navigate to: Finance → Manage Expenses → Budgets tab
3. Click **"Create Budget"**
4. Fill in budget details:
   - **Name**: e.g., "MyGrowNet Platform - March 2026"
   - **Period Type**: Monthly, Quarterly, or Yearly
   - **Start Date**: Budget start date
   - **End Date**: Budget end date
   - **Status**: Active (to show in dashboard)

### Adding Budget Items

For each budget, add items for both expenses and revenue:

**Expense Categories:**
- Marketing
- Office Expenses
- Travel
- Infrastructure
- Legal & Compliance
- Professional Services
- Utilities
- General Expenses

**Revenue Categories:**
- Wallet Top-ups
- Starter Kits
- Module Subscriptions
- Product Sales
- Service Fees
- Workshop Fees
- Learning Packs
- Coaching
- Venture Builder
- Storage
- Support

**For Each Item:**
1. Select **Category** (must match transaction categories)
2. Select **Item Type**: Expense or Revenue
3. Enter **Budgeted Amount** (in Kwacha)
4. Add **Notes** (optional, for context)

## Understanding the Dashboard

### Summary Cards

**Total Budgeted**
- Sum of all budgeted amounts (expenses + revenue)
- Blue card with currency icon

**Total Actual**
- Sum of all actual transactions for the period
- Color changes based on variance (red if over, green if under)

**Variance**
- Difference between actual and budgeted
- Shows "Over Budget" or "Under Budget"
- Red for over, green for under

**Budget Used**
- Percentage of budget consumed
- Red: ≥100% (over budget)
- Amber: 90-99% (approaching limit)
- Green: <90% (on track)

### Performance Metrics

**Over Budget** (Red)
- Number of categories exceeding their budget
- Requires immediate attention

**On Track** (Blue)
- Categories at 90-100% of budget
- Monitor closely

**Under Budget** (Green)
- Categories below 90% of budget
- Good performance or underutilization

**Unbudgeted** (Amber)
- Expenses that occurred but weren't in the budget
- Need to be added to future budgets

### Critical Overages Alert

Red alert box appears when any category exceeds 120% of budget:
- Shows category name
- Shows percentage used
- Shows amount over budget
- Requires immediate action

### Budget vs Actual Table

Detailed breakdown showing:
- **Category**: Expense or revenue category
- **Type**: Expense (red badge) or Revenue (green badge)
- **Budgeted**: Planned amount
- **Actual**: Actual spending/revenue from transactions
- **Variance**: Difference (↑ over, ↓ under)
- **Usage**: Progress bar showing percentage
- **Status**: Badge (Over Budget, On Track, Under Budget)

### Unbudgeted Expenses Alert

Amber alert box shows expenses that weren't included in the budget:
- Category name
- Amount spent
- Suggests adding to future budgets

## Period Selection

Use the dropdown to view different time periods:
- **Today**: Current day only
- **This Week**: Monday to Sunday
- **This Month**: 1st to last day of current month
- **This Quarter**: Q1, Q2, Q3, or Q4
- **This Year**: January 1 to December 31

Click **Refresh** button to reload data after changing period.

## How Actuals Are Calculated

The system automatically pulls actual amounts from the `transactions` table:

**Expense Types:**
- commission
- withdrawal
- profit_share
- lgr_award
- loan_disbursement
- shop_credit
- marketing_expense
- office_expense
- travel_expense
- infrastructure_expense
- legal_expense
- professional_fees
- utilities_expense
- general_expense

**Revenue Types:**
- wallet_topup
- starter_kit_purchase
- module_subscription
- product_sale
- service_fee
- workshop_fee
- learning_pack_sale
- coaching_fee
- venture_builder_fee
- storage_subscription
- support_ticket_fee

**Mapping:**
Transaction types are automatically mapped to budget categories. For example:
- `marketing_expense` → "Marketing"
- `office_expense` → "Office Expenses"
- `wallet_topup` → "Wallet Top-ups"

## Best Practices

### Budget Planning

1. **Review Historical Data**: Check past months' actuals before creating budget
2. **Be Realistic**: Don't underbudget to make numbers look good
3. **Include Buffer**: Add 10-15% buffer for unexpected expenses
4. **Categorize Properly**: Ensure all expected expenses have a category
5. **Set Alerts**: Monitor critical categories weekly

### Budget Monitoring

1. **Weekly Reviews**: Check dashboard every Monday
2. **Address Overages**: Investigate categories over 100% immediately
3. **Track Trends**: Look for patterns in overspending
4. **Adjust Forecasts**: Update future budgets based on actuals
5. **Document Variances**: Add notes explaining significant differences

### Budget Adjustments

If you need to adjust a budget mid-period:
1. Go to CMS → Budgets
2. Edit the active budget
3. Update budget item amounts
4. Add notes explaining the change
5. Dashboard will reflect changes immediately

## Troubleshooting

### "No Active Budget" Message

**Problem**: Dashboard shows "No active budget found for this period"

**Solutions:**
1. Create a budget in CMS for the current period
2. Ensure budget status is set to "Active"
3. Check start/end dates match the selected period
4. Verify budget is for "MyGrowNet Platform" company (ID: 7)

### Actual Amounts Are Zero

**Problem**: Budget shows but all actuals are K0.00

**Solutions:**
1. Check if transactions exist for the period
2. Verify transaction status is "completed"
3. Ensure transaction types match budget categories
4. Check date range matches budget period

### Category Not Showing

**Problem**: Budget category doesn't appear in comparison

**Solutions:**
1. Verify category name matches exactly (case-sensitive)
2. Check item type is correct (expense vs revenue)
3. Ensure budget item was saved properly
4. Refresh the page

### Unbudgeted Expenses Appearing

**Problem**: Expenses showing as "unbudgeted"

**Solutions:**
1. Add missing categories to budget
2. Check if transaction type is mapped correctly
3. Review expense categorization in CMS
4. Update budget to include new expense types

## Integration with Other Systems

### CMS Expense Management

- Expenses recorded in CMS automatically sync to transactions
- Approved expenses appear in budget actuals immediately
- Drill down from budget to CMS for expense details

### Profit & Loss Dashboard

- Budget data can be viewed alongside P&L
- Compare budgeted vs actual profitability
- Identify areas impacting bottom line

### Financial Reports

- Budget performance included in financial reports
- Export budget vs actual for stakeholders
- Track budget compliance over time

## Sample Budget Template

Here's a recommended monthly budget structure for MyGrowNet Platform:

**Expenses (K40,000):**
- Marketing: K10,000
- Office Expenses: K5,000
- Travel: K3,000
- Infrastructure: K8,000
- Legal & Compliance: K4,000
- Professional Services: K6,000
- Utilities: K2,000
- General Expenses: K2,000

**Revenue (K75,000):**
- Wallet Top-ups: K30,000
- Starter Kits: K15,000
- Module Subscriptions: K20,000
- Product Sales: K10,000

**Net Budget:** K35,000 profit

Adjust amounts based on your actual business needs and historical data.

## Related Documentation

- [CMS Financial Integration Analysis](./CMS_FINANCIAL_INTEGRATION_ANALYSIS.md) - Complete integration overview
- [CMS Expense Integration Implementation](./CMS_EXPENSE_INTEGRATION_IMPLEMENTATION.md) - Phase 1 details
- [Admin Expense Recording Guide](./ADMIN_EXPENSE_RECORDING_GUIDE.md) - How to record expenses
- [Financial System Architecture](./FINANCIAL_SYSTEM_ARCHITECTURE.md) - System design
- [Transaction-Based Reporting](./TRANSACTION_BASED_REPORTING.md) - Reporting system

## Support

For technical issues or questions:
1. Check this guide first
2. Review related documentation
3. Contact system administrator
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Remember:** Budget management is a continuous process. Regular monitoring and adjustments ensure financial health and accountability.
