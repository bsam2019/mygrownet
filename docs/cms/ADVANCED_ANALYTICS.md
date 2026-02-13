# Advanced Analytics & Business Intelligence

**Last Updated:** February 12, 2026  
**Status:** Production Ready

## Overview

Advanced analytics and business intelligence system with custom dashboards, KPI tracking, trend analysis, forecasting, goal management, and predictive analytics.

## Features

### Custom Dashboard Builder
- Create multiple custom dashboards
- Drag-and-drop widget positioning
- Widget types: charts, KPIs, tables, metrics
- Configurable widget sizes and layouts
- Default and shared dashboards
- Personal and company-wide dashboards

### Advanced Charts and Graphs
- Line charts for trends
- Bar charts for comparisons
- Pie charts for distributions
- Area charts for cumulative data
- Multi-series charts
- Interactive tooltips and legends
- Export to image/PDF

### KPI Tracking
- Define custom KPIs
- Multiple calculation methods (sum, average, count)
- Historical value tracking
- Target vs actual comparison
- Variance analysis
- Frequency options (daily, weekly, monthly, quarterly, yearly)
- Automated KPI calculation

### Trend Analysis
- Historical trend calculation
- Period-over-period comparison
- Trend direction detection (up, down, stable)
- Change percentage calculation
- Multi-period analysis
- Customizable time ranges

### Forecasting
- Linear regression forecasting
- Revenue forecasting
- Expense forecasting
- Profit forecasting
- Confidence intervals
- Accuracy tracking
- Multiple forecast periods

### Goal Setting and Tracking
- Set business goals with targets
- Goal types: revenue, profit, customers, jobs, custom
- Start and end dates
- Progress tracking
- Milestone management
- Goal status (not started, in progress, achieved, failed)
- Assignment to team members
- On-track vs off-track indicators

### Predictive Analytics
- Simple linear regression models
- Trend-based predictions
- Historical pattern analysis
- Confidence intervals
- Model accuracy tracking

## Database Schema

### cms_dashboards
- Custom dashboard definitions
- Layout configuration
- Sharing settings

### cms_dashboard_widgets
- Widget configurations
- Position and size
- Data source settings

### cms_kpis
- KPI definitions
- Calculation methods
- Data sources

### cms_kpi_values
- Historical KPI values
- Target tracking
- Variance calculation

### cms_goals
- Goal definitions
- Target and current values
- Status tracking

### cms_goal_milestones
- Goal milestone tracking
- Completion status

### cms_trend_analysis
- Trend calculations
- Period comparisons
- Change tracking

### cms_forecasts
- Forecast values
- Confidence intervals
- Accuracy tracking

## Implementation Files

### Backend
- `database/migrations/2026_02_12_190000_create_cms_analytics_bi_tables.php`
- `app/Domain/CMS/Core/Services/AdvancedAnalyticsService.php`

## Usage

### Calculate Trend
```php
$trend = $analyticsService->calculateTrend(
    companyId: 1,
    metric: 'revenue',
    periodType: 'monthly',
    periods: 12
);
// Returns: data points, trend direction, change percentage
```

### Generate Forecast
```php
$forecast = $analyticsService->generateForecast(
    companyId: 1,
    metric: 'revenue',
    forecastPeriods: 3
);
// Returns: historical data, forecasts, model parameters
```

### Calculate KPI
```php
$kpi = $analyticsService->calculateKPI(
    companyId: 1,
    kpiType: 'revenue',
    startDate: Carbon::now()->startOfMonth(),
    endDate: Carbon::now()->endOfMonth()
);
// Returns: value, previous value, variance, trend
```

### Track Goal Progress
```php
$progress = $analyticsService->calculateGoalProgress(goalId: 1);
// Returns: progress percentage, on-track status, days remaining
```

### Get Dashboard Data
```php
$data = $analyticsService->getDashboardData(
    companyId: 1,
    period: 'month'
);
// Returns: all KPIs and trends for the period
```

## Supported Metrics

- **Revenue**: Total invoice amounts (paid)
- **Profit**: Total job profit amounts
- **Expenses**: Total expense amounts
- **Customers**: Customer count
- **Jobs**: Job count
- **Payments**: Total payment amounts

## Forecasting Models

### Linear Regression
- Uses historical data (last 12 months)
- Calculates slope and intercept
- Generates forecasts with confidence intervals
- 90-110% confidence range

## Business Rules

1. **Trend Analysis**: Minimum 3 periods for trend direction
2. **Forecasting**: Requires 12 months historical data
3. **KPI Variance**: Calculated against previous period
4. **Goal Status**: Automatically determined based on progress and dates
5. **On-Track Indicator**: Progress >= expected progress based on time elapsed
6. **Confidence Intervals**: ±10% of forecasted value

## Security

- Company-scoped queries (all queries filtered by company_id)
- Authorization for dashboard creation and sharing
- Audit trail for goal and KPI changes
- Data access controls

## Future Enhancements

- Machine learning models (ARIMA, Prophet)
- Anomaly detection
- Correlation analysis
- What-if scenarios
- Advanced statistical models
- Real-time analytics
- Automated insights and recommendations
- Natural language queries
- Export to Excel/CSV
- Scheduled report generation
- Alert thresholds
- Benchmark comparisons
- Industry comparisons

## Changelog

### February 12, 2026
- Initial implementation
- Custom dashboard builder
- KPI tracking system
- Trend analysis
- Linear regression forecasting
- Goal management
- Predictive analytics foundation
