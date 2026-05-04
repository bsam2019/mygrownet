# CMS Analytics - Charts & Visual Analytics Plan

**Last Updated:** April 28, 2026  
**Status:** Planning Phase

## Overview

This document outlines where charts and visual analytics should be added to the CMS Analytics pages to improve data visualization and user experience.

**Current Status:**
- ✅ Chart.js v4.4.8 installed
- ✅ vue-chartjs v5.3.2 installed
- ✅ Multiple chart components already exist in the project
- ❌ CMS Analytics pages currently use only text/tables

---

## 🎯 Priority Chart Implementations

### **1. Operations Analytics** (`resources/js/pages/CMS/Analytics/Operations.vue`)

#### **HIGH PRIORITY**

##### **A. Jobs by Status - Doughnut/Pie Chart**
**Current:** Simple list with colored dots  
**Improvement:** Interactive doughnut chart

**Benefits:**
- Visual proportion of job statuses at a glance
- Color-coded segments (pending=gray, in_progress=blue, completed=green, cancelled=red)
- Hover to see exact counts and percentages

**Data Available:** `metrics.jobs_by_status` (Record<string, number>)

**Chart Type:** Doughnut Chart
```typescript
{
  pending: 15,
  in_progress: 23,
  completed: 142,
  cancelled: 3
}
```

---

##### **B. Jobs Timeline - Line/Area Chart**
**Current:** Horizontal bars with dates  
**Improvement:** Line chart with area fill showing job creation trends

**Benefits:**
- Identify patterns and trends over time
- Spot busy periods vs slow periods
- Better for time-series data visualization

**Data Available:** `metrics.jobs_timeline` (Record<string, number>)

**Chart Type:** Line Chart with Area Fill
```typescript
{
  "2026-04-01": 5,
  "2026-04-02": 8,
  "2026-04-03": 12,
  // ...
}
```

---

##### **C. Worker Productivity - Horizontal Bar Chart**
**Current:** Table format  
**Improvement:** Keep table, add horizontal bar chart for top 10 workers

**Benefits:**
- Quick visual comparison of worker performance
- Easy to identify top performers
- Complements the detailed table

**Data Available:** `metrics.worker_productivity` (Array)

**Chart Type:** Horizontal Bar Chart (Hours or Earnings)
```typescript
[
  { worker_name: "John Doe", total_hours: 160, total_earned: 5000 },
  { worker_name: "Jane Smith", total_hours: 155, total_earned: 4800 }
]
```

---

##### **D. Jobs by Type - Bar Chart**
**Current:** Simple list  
**Improvement:** Vertical bar chart

**Benefits:**
- Visual comparison of job type distribution
- Easy to identify most common job types

**Data Available:** `metrics.jobs_by_type` (Record<string, number>)

**Chart Type:** Vertical Bar Chart

---

#### **MEDIUM PRIORITY**

##### **E. Completion Rate Gauge/Progress Circle**
**Current:** Percentage number  
**Improvement:** Circular progress indicator in the metric card

**Benefits:**
- More engaging visual
- Instant understanding of performance

**Data Available:** `metrics.job_completion_rate` (number)

**Chart Type:** Radial/Gauge Chart or CSS-based circular progress

---

### **2. Finance Analytics** (`resources/js/pages/CMS/Analytics/Finance.vue`)

#### **HIGH PRIORITY**

##### **A. Revenue vs Expenses - Dual Line Chart**
**Current:** Separate metric cards  
**Improvement:** Line chart showing both over time with profit area

**Benefits:**
- See relationship between revenue and expenses
- Identify profit/loss periods
- Trend analysis

**Data Needed:** Need to add time-series data to backend
```typescript
{
  dates: ["2026-04-01", "2026-04-02", ...],
  revenue: [5000, 6000, ...],
  expenses: [3000, 3500, ...],
  profit: [2000, 2500, ...]
}
```

**Chart Type:** Multi-Line Chart with Area Fill

---

##### **B. Cash Flow - Stacked Area/Bar Chart**
**Current:** Grid layout with text  
**Improvement:** Stacked bar chart showing inflow, outflow, and net

**Benefits:**
- Visual representation of cash movement
- Easy to spot negative cash flow days
- Better understanding of financial health

**Data Available:** `metrics.cash_flow` (Record<string, object>)

**Chart Type:** Stacked Bar Chart or Waterfall Chart
```typescript
{
  "2026-04-01": { inflow: 5000, outflow: 3000, net: 2000 },
  "2026-04-02": { inflow: 6000, outflow: 4000, net: 2000 }
}
```

---

##### **C. Expense Breakdown - Doughnut Chart**
**Current:** Simple list  
**Improvement:** Doughnut chart with category breakdown

**Benefits:**
- Visual proportion of expense categories
- Quick identification of major expense areas
- Interactive hover for details

**Data Available:** `metrics.expense_breakdown` (Record<string, number>)

**Chart Type:** Doughnut Chart
```typescript
{
  "Labor": 15000,
  "Materials": 8000,
  "Equipment": 5000,
  "Overhead": 3000
}
```

---

##### **D. Revenue by Customer - Horizontal Bar Chart**
**Current:** Table format  
**Improvement:** Keep table, add horizontal bar chart for top 10

**Benefits:**
- Visual comparison of customer revenue
- Easy to identify top customers
- Complements the detailed table

**Data Available:** `metrics.revenue_by_customer` (Array)

**Chart Type:** Horizontal Bar Chart

---

##### **E. Profit Margin Trend - Line Chart**
**Current:** Single metric card  
**Improvement:** Line chart showing profit margin over time

**Benefits:**
- Track profitability trends
- Identify improving or declining margins
- Set performance benchmarks

**Data Available:** `metrics.profit_margin_trend` (Record<string, number>)

**Chart Type:** Line Chart
```typescript
{
  "2026-01": 35.5,
  "2026-02": 38.2,
  "2026-03": 36.8,
  "2026-04": 40.1
}
```

---

#### **MEDIUM PRIORITY**

##### **F. Outstanding Invoices - Funnel or Bar Chart**
**Current:** List by customer  
**Improvement:** Horizontal bar chart showing top customers with outstanding amounts

**Benefits:**
- Quick identification of largest outstanding amounts
- Visual priority for collections

**Data Available:** `metrics.outstanding_invoices.by_customer` (Array)

**Chart Type:** Horizontal Bar Chart

---

##### **G. Payment Trends - Line Chart**
**Current:** Not displayed  
**Improvement:** Add line chart showing payment volume and amount trends

**Benefits:**
- Identify payment patterns
- Forecast cash inflow

**Data Available:** `metrics.payment_trends` (Record<string, object>)

**Chart Type:** Dual-Axis Line Chart (amount + count)

---

## 📊 Recommended Chart Library Components

### **Reusable Chart Components to Create**

1. **`CMS/Charts/DoughnutChart.vue`**
   - For: Jobs by Status, Expense Breakdown
   - Features: Legend, tooltips, responsive

2. **`CMS/Charts/LineChart.vue`**
   - For: Jobs Timeline, Profit Margin Trend, Revenue vs Expenses
   - Features: Grid, tooltips, area fill option, multi-line support

3. **`CMS/Charts/BarChart.vue`**
   - For: Jobs by Type, Worker Productivity, Revenue by Customer
   - Features: Horizontal/vertical modes, tooltips, responsive

4. **`CMS/Charts/StackedBarChart.vue`**
   - For: Cash Flow
   - Features: Stacked bars, legend, tooltips

5. **`CMS/Charts/GaugeChart.vue`**
   - For: Completion Rate, Profit Margin
   - Features: Circular progress, color thresholds

---

## 🎨 Design Guidelines

### **Color Scheme (from tech.md)**

**Operations Charts:**
- Pending: `#6b7280` (gray-500)
- In Progress: `#3b82f6` (blue-500)
- Completed: `#10b981` (emerald-500)
- Cancelled: `#dc2626` (red-600)

**Finance Charts:**
- Revenue/Inflow: `#10b981` (emerald-500)
- Expenses/Outflow: `#dc2626` (red-600)
- Profit: `#059669` (emerald-600)
- Neutral: `#2563eb` (blue-600)

### **Chart Styling**
- Background: White (`#ffffff`)
- Grid lines: `#e5e7eb` (gray-200)
- Text: `#111827` (gray-900)
- Font: System font stack (matches Tailwind)
- Border radius: `0.5rem` (rounded-lg)
- Shadow: Tailwind `shadow` class

---

## 🔧 Backend Changes Needed

### **Operations Analytics**
✅ All data already available - no backend changes needed

### **Finance Analytics**
❌ Need to add time-series data for some charts:

1. **Revenue vs Expenses Over Time**
   ```php
   'revenue_trend' => [
       'dates' => ['2026-04-01', '2026-04-02', ...],
       'revenue' => [5000, 6000, ...],
       'expenses' => [3000, 3500, ...]
   ]
   ```

2. **Payment Trends Chart** (data exists but not displayed)
   - Already available: `metrics.payment_trends`
   - Just needs frontend implementation

---

## 📝 Implementation Priority

### **Phase 1: High Impact, Low Effort** (Week 1)
1. ✅ Jobs by Status - Doughnut Chart
2. ✅ Expense Breakdown - Doughnut Chart
3. ✅ Jobs Timeline - Line Chart
4. ✅ Cash Flow - Stacked Bar Chart

### **Phase 2: Enhanced Visualizations** (Week 2)
5. ✅ Worker Productivity - Horizontal Bar Chart
6. ✅ Revenue by Customer - Horizontal Bar Chart
7. ✅ Jobs by Type - Bar Chart
8. ✅ Profit Margin Trend - Line Chart

### **Phase 3: Advanced Analytics** (Week 3)
9. ✅ Revenue vs Expenses - Multi-Line Chart (requires backend)
10. ✅ Completion Rate - Gauge Chart
11. ✅ Outstanding Invoices - Bar Chart
12. ✅ Payment Trends - Line Chart

---

## 🎯 Success Metrics

**User Experience:**
- Reduced time to understand data (target: 50% faster)
- Increased engagement with analytics pages
- Positive user feedback on visualizations

**Technical:**
- Charts load in <500ms
- Responsive on all screen sizes
- Accessible (WCAG 2.1 AA compliant)
- No performance degradation

---

## 📚 Reference Examples

**Existing Chart Implementations in Project:**
- `resources/js/components/ui/charts/LineChart.vue`
- `resources/js/components/PerformanceChart.vue`
- `resources/js/components/BizBoost/Dashboard/InteractiveChart.vue`
- `resources/js/pages/LifePlus/Analytics/Index.vue`

**Chart.js Documentation:**
- https://www.chartjs.org/docs/latest/

**Vue-ChartJS Documentation:**
- https://vue-chartjs.org/

---

## 🚀 Next Steps

1. **Review and approve this plan**
2. **Create reusable chart components** in `resources/js/components/CMS/Charts/`
3. **Implement Phase 1 charts** (high impact, low effort)
4. **Add backend time-series data** for Revenue vs Expenses
5. **Test on different screen sizes** and browsers
6. **Gather user feedback** and iterate

---

## Notes

- All charts should be **responsive** and work on mobile devices
- Include **loading states** while data is being fetched
- Add **empty states** when no data is available
- Ensure **accessibility** (keyboard navigation, screen readers)
- Use **consistent colors** across all charts
- Add **export functionality** (PNG/PDF) for reports
