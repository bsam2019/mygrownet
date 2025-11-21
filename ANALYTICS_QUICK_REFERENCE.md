# Analytics System - Quick Reference

**Last Updated:** November 20, 2025

---

## Access Points

### Desktop
- **URL:** `/mygrownet/analytics`
- **Route Name:** `mygrownet.analytics.index`
- **Sidebar:** MyGrowNet → Analytics

### Mobile
- **Quick Action:** "Performance Analytics" button on Home tab
- **Opens:** Full-screen analytics modal
- **Component:** `AnalyticsModal.vue` → `AnalyticsView.vue`

---

## API Endpoints

### Get Performance Data
```
GET /mygrownet/analytics/performance
```
Returns complete performance analytics for authenticated user.

### Get Recommendations
```
GET /mygrownet/analytics/recommendations
```
Generates and returns personalized recommendations.

### Dismiss Recommendation
```
POST /mygrownet/analytics/recommendations/{id}/dismiss
```
Marks a recommendation as dismissed.

### Get Growth Potential
```
GET /mygrownet/analytics/growth-potential
```
Returns growth potential analysis.

### Debug Endpoint
```
GET /debug/analytics
```
Returns all analytics data in one response (for testing).

---

## Key Metrics Explained

### Health Score (0-100)
Calculated from:
- **30 points** - Active status
- **20 points** - Has starter kit
- **25 points** - Network size (up to 25 points)
- **25 points** - Active network percentage

### Peer Percentiles
- **0-25%** - Below average
- **25-50%** - Average
- **50-75%** - Above average
- **75-100%** - Top performer

### Growth Rate
```
Growth Rate = ((Last 30 Days - Previous 30 Days) / Previous 30 Days) × 100
```

---

## Recommendation Priority

### High Priority (Red)
- Upgrade opportunities
- Critical actions needed

### Medium Priority (Yellow)
- Network growth opportunities
- Engagement improvements

### Low Priority (Blue)
- Learning suggestions
- General improvements

---

## Services

### AnalyticsService
**Purpose:** Core analytics calculations

**Methods:**
- `getMemberPerformance($user)` - Complete performance data
- `getEarningsBreakdown($user)` - Earnings by source
- `getNetworkMetrics($user)` - Network statistics
- `getGrowthTrends($user)` - Growth analysis
- `getEngagementMetrics($user)` - Engagement data
- `calculateHealthScore($user)` - Health score (0-100)
- `compareWithPeers($user)` - Peer comparison

### PredictiveAnalyticsService
**Purpose:** Forecasting and predictions

**Methods:**
- `predictEarnings($user, $months)` - Earnings forecast
- `calculateGrowthPotential($user)` - Growth analysis
- `calculateChurnRisk($user)` - Churn risk assessment
- `getNextMilestone($user)` - Next milestone info

### RecommendationEngine
**Purpose:** Personalized recommendations

**Methods:**
- `generateRecommendations($user)` - Create recommendations
- `getActiveRecommendations($user)` - Get active recommendations
- `dismissRecommendation($id, $user)` - Dismiss recommendation

---

## Database Tables

### recommendations
Stores personalized recommendations for users.

**Key Fields:**
- `user_id` - User ID
- `recommendation_type` - Type (upgrade, network_growth, etc.)
- `title` - Recommendation title
- `description` - Detailed description
- `action_url` - URL for action
- `priority` - high/medium/low
- `impact_score` - 0-100
- `is_dismissed` - Boolean
- `expires_at` - Expiration date

### analytics_events
Tracks user events for analytics.

**Key Fields:**
- `user_id` - User ID
- `event_type` - Event type (login, starter_kit_access, etc.)
- `event_data` - JSON metadata
- `created_at` - Timestamp

### performance_snapshots
Historical performance data.

**Key Fields:**
- `user_id` - User ID
- `snapshot_date` - Date of snapshot
- `total_earnings` - Total earnings
- `network_size` - Network size
- `active_members` - Active members count
- `performance_score` - Performance score

### member_analytics_cache
Cached analytics calculations.

**Key Fields:**
- `user_id` - User ID
- `metric_type` - Metric type
- `metric_value` - Numeric value
- `metric_data` - JSON data
- `period` - Time period
- `expires_at` - Cache expiration

---

## Caching

**Cache Duration:** 1 hour (3600 seconds)

**Cache Key Format:**
```
analytics.performance.{user_id}
```

**Clear Cache:**
```bash
php artisan cache:clear
```

**Clear Specific User:**
```php
Cache::forget("analytics.performance.{$userId}");
```

---

## Common Issues & Solutions

### Issue: Percentiles showing 0%
**Solution:** Not enough peers with same tier. System returns 50% when no peers exist.

### Issue: No recommendations showing
**Solution:** Call `generateRecommendations()` first, or visit analytics page (auto-generates).

### Issue: Growth rate is 0%
**Solution:** No new referrals in last 30 days. This is accurate, not a bug.

### Issue: Health score seems low
**Solution:** Health score factors in multiple metrics. Improve by:
- Staying active (login regularly)
- Growing network
- Activating team members
- Using starter kit content

---

## Testing Checklist

- [ ] Desktop analytics page loads
- [ ] Mobile analytics modal opens
- [ ] All metrics display correctly
- [ ] Peer comparison shows percentiles
- [ ] Recommendations appear (if eligible)
- [ ] Growth potential calculates
- [ ] Next milestone shows (if applicable)
- [ ] Dismiss recommendation works
- [ ] Cache works (fast second load)

---

## Performance Tips

1. **Use caching** - Analytics are cached for 1 hour
2. **Lazy load** - Load analytics only when needed
3. **Batch queries** - Services use efficient queries
4. **Index tables** - All analytics tables have proper indexes

---

## Future Enhancements

- [ ] Historical charts (earnings over time)
- [ ] Team performance comparison
- [ ] Achievement tracking
- [ ] Export analytics reports
- [ ] Email analytics summaries
- [ ] Push notifications for milestones
