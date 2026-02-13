# CMS Analytics & Dashboards System

**Last Updated:** February 10, 2026  
**Status:** Production Ready ✅

## Overview

Comprehensive analytics and dashboard system providing real-time insights into operations and financial performance. Features period-based filtering and visual data representation.

## Features

### 1. Operations Dashboard

**Access:** `/cms/analytics/operations`

**Key Metrics:**
- **Job Completion Rate** - Percentage of jobs completed vs total jobs
- **Average Job Duration** - Mean time from job creation to completion (in days)
- **Active Jobs** - Count of pending and in-progress jobs
- **Inventory Turnover** - Rate of inventory movement

**Detailed Analytics:**
- Jobs by Status (Pending, In Progress, Completed, Cancelled)
- Jobs by Type breakdown
- Worker Productivity (hours, days, earnings per worker)
- Jobs Timeline (daily job creation trends)

### 2. Finance Dashboard

**Access:** `/cms/analytics/finance`

**Key Metrics:**
- **Revenue** - Total payments received in period
- **Expenses** - Total approved/paid expenses in period
- **Profit** - Revenue minus expenses
- **Profit Margin** - Profit as percentage of revenue

**Detailed Analytics:**
- Outstanding Invoices (total amount, count, by customer)
- Top Customers by Revenue (top 10)
- Expense Breakdown by Category
- Cash Flow Analysis (daily inflow, outflow, net)
- Payment Trends (daily totals and counts)
- Profit Margin Trend (monthly progression)

## Period Filtering

All dashboards support period-based filtering:
- **Last Week** - 7 days of data
- **Last Month** - 30 days of data (default)
- **Last Quarter** - 90 days of data
- **Last Year** - 365 days of data

## Technical Implementation

### Backend Architecture

**AnalyticsService** (`app/Domain/CMS/Core/Services/AnalyticsService.php`)
- Centralized metrics calculation
- Optimized database queries with aggregations
- Period-based data filtering
- Grouped and sorted results

**Key Methods:**
- `getOperationsMetrics()` - Operations dashboard data
- `getFinanceMetrics()` - Finance dashboard data
- Private calculation methods for each metric

**AnalyticsController** (`app/Http/Controllers/CMS/AnalyticsController.php`)
- `operations()` - Operations dashboard endpoint
- `finance()` - Finance dashboard endpoint
- Period parameter handling

### Frontend Components

**Operations Dashboard** (`resources/js/Pages/CMS/Analytics/Operations.vue`)
- 4 key metric cards
- Jobs by status chart
- Jobs by type breakdown
- Worker productivity table
- Jobs timeline visualization

**Finance Dashboard** (`resources/js/Pages/CMS/Analytics/Finance.vue`)
- 4 financial metric cards
- Outstanding invoices summary
- Expense breakdown
- Top customers table
- Cash flow analysis
- Payment trends

### Routes

```php
GET /cms/analytics/operations?period=month
GET /cms/analytics/finance?period=quarter
```

## Metrics Calculations

### Operations Metrics

**Job Completion Rate:**
```
(Completed Jobs / Total Jobs) × 100
```

**Average Job Duration:**
```
AVG(DATEDIFF(completed_at, created_at))
```

**Worker Productivity:**
- Sum of hours worked per worker
- Sum of days worked per worker
- Total earnings per worker

### Finance Metrics

**Revenue:**
```
SUM(payments.amount) WHERE is_voided = false
```

**Expenses:**
```
SUM(expenses.amount) WHERE status IN ('approved', 'paid')
```

**Profit:**
```
Revenue - Expenses
```

**Profit Margin:**
```
((Revenue - Expenses) / Revenue) × 100
```

**Cash Flow:**
```
Daily Inflow: SUM(payments)
Daily Outflow: SUM(expenses)
Net: Inflow - Outflow
```

## Usage Examples

### View Operations Dashboard
1. Navigate to `/cms/analytics/operations`
2. Select period (week, month, quarter, year)
3. Review key metrics and charts
4. Analyze worker productivity
5. Check jobs timeline

### View Finance Dashboard
1. Navigate to `/cms/analytics/finance`
2. Select period for analysis
3. Review revenue, expenses, profit
4. Check outstanding invoices
5. Analyze top customers
6. Monitor cash flow trends

### Period Comparison
1. Select "Last Month" to view current month
2. Note key metrics
3. Change to "Last Quarter" for broader view
4. Compare trends and patterns

## Data Sources

**Operations Dashboard:**
- `cms_jobs` - Job data and status
- `cms_worker_attendance` - Worker productivity
- `cms_inventory_items` - Inventory turnover

**Finance Dashboard:**
- `cms_payments` - Revenue data
- `cms_expenses` - Expense data
- `cms_invoices` - Outstanding invoices
- `cms_customers` - Customer information

## Performance Considerations

- Queries use database aggregations (SUM, AVG, COUNT)
- Indexed columns for date filtering
- Results cached at controller level (future enhancement)
- Pagination for large datasets (top 10 customers)

## Future Enhancements

- [ ] Export analytics to PDF/Excel
- [ ] Custom date range selection
- [ ] Comparative period analysis (YoY, MoM)
- [ ] Predictive analytics and forecasting
- [ ] Real-time dashboard updates
- [ ] Custom metric builder
- [ ] Scheduled email reports
- [ ] Dashboard widgets customization
- [ ] Drill-down capabilities
- [ ] Advanced charting (line, pie, area charts)

## Troubleshooting

### No data showing
- Verify period selection includes data
- Check that jobs/payments exist in database
- Ensure company_id filtering is correct

### Incorrect calculations
- Verify date ranges are correct
- Check status filters (approved, paid, etc.)
- Ensure voided records are excluded

### Slow performance
- Check database indexes on date columns
- Consider caching for frequently accessed metrics
- Optimize queries with EXPLAIN

## Related Documentation

- [CMS Implementation Progress](./IMPLEMENTATION_PROGRESS.md)
- [CMS Complete Feature Specification](./COMPLETE_FEATURE_SPECIFICATION.md)
- [Reports System](./PHASE_2_3_TESTING_GUIDE.md)

## Changelog

### February 10, 2026
- Initial implementation complete
- Operations and Finance dashboards
- Period filtering functionality
- All metrics calculations working
- Navigation integration complete
