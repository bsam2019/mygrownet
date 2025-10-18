# Dashboard API

The Dashboard API provides real-time data aggregation for user dashboards, including earnings, investment performance, and comprehensive metrics.

## Endpoints

### Get Dashboard Data

```http
GET /dashboard
```

Returns comprehensive dashboard data for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "portfolio": {
      "total_investment": 15000,
      "total_earnings": 2250,
      "referral_earnings": 750,
      "profit_earnings": 1200,
      "matrix_commissions": 300,
      "pending_earnings": 150,
      "active_referrals": 12,
      "total_referrals": 18
    },
    "earnings": {
      "total_earnings": 2250,
      "referral_commissions": 750,
      "profit_shares": 1200,
      "matrix_commissions": 300,
      "pending_earnings": 150
    },
    "tierInfo": {
      "current_tier": "Builder",
      "eligible": true,
      "next_tier": "Leader",
      "required_amount": 5000,
      "current_amount": 15000
    },
    "matrixStructure": {
      "level_1": [
        {
          "id": 123,
          "name": "John Doe",
          "position": 1,
          "investment_amount": 2500
        }
      ],
      "level_2": [...],
      "level_3": [...]
    },
    "recent_transactions": [...],
    "investment_opportunities": [...],
    "withdrawal_summary": {
      "total_investments": 3,
      "eligible_for_withdrawal": 1,
      "total_withdrawable_amount": 5000,
      "pending_withdrawals": 0
    }
  }
}
```

### Get Real-time Earnings

```http
GET /dashboard/real-time-earnings
```

Returns real-time earnings and investment performance data.

**Response:**
```json
{
  "success": true,
  "data": {
    "earnings": {
      "total_earnings": 2250,
      "referral_commissions": 750,
      "profit_shares": 1200,
      "matrix_commissions": 300,
      "pending_earnings": 150
    },
    "investment_performance": {
      "total_invested": 15000,
      "current_value": 17250,
      "total_profit": 2250,
      "average_roi": 15.0,
      "investment_count": 3,
      "performance_by_tier": [
        {
          "tier": "Builder",
          "count": 2,
          "total_invested": 10000,
          "current_value": 11500,
          "profit": 1500,
          "roi_percentage": 15.0
        }
      ],
      "growth_rate": 18.5
    },
    "recent_earnings": {
      "commissions": [...],
      "profit_shares": [...],
      "combined": [...]
    },
    "last_updated": "2025-08-07T10:30:00Z"
  }
}
```

### Get Withdrawal Eligibility

```http
GET /dashboard/withdrawal-eligibility
```

**Query Parameters:**
- `investment_id` (optional) - Specific investment ID
- `withdrawal_type` (optional) - Type of withdrawal (full, partial, emergency)
- `amount` (optional) - Withdrawal amount for validation

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 123,
      "amount": 5000,
      "current_value": 5750,
      "tier": "Builder"
    },
    "eligibility": {
      "is_eligible": true,
      "reasons": [],
      "lock_in_period_remaining": 0,
      "withdrawal_date_eligible": "2024-08-07"
    },
    "penalties": {
      "penalty_applicable": false,
      "penalty_amount": 0,
      "penalty_percentage": 0,
      "net_withdrawable_amount": 5750
    },
    "scenarios": {
      "full_withdrawal": {
        "amount": 5750,
        "penalty": 0,
        "net_amount": 5750
      },
      "partial_withdrawal": {
        "max_amount": 2875,
        "penalty": 0,
        "net_amount": 2875
      },
      "emergency_withdrawal": {
        "amount": 5750,
        "penalty": 2875,
        "net_amount": 2875
      }
    },
    "withdrawable_amount": 5750
  }
}
```

### Get Penalty Preview

```http
GET /dashboard/penalty-preview
```

**Query Parameters:**
- `investment_id` (required) - Investment ID
- `withdrawal_type` (required) - Type of withdrawal
- `amount` (optional) - Withdrawal amount
- `withdrawal_date` (optional) - Planned withdrawal date

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 123,
      "amount": 5000,
      "current_value": 5750,
      "tier": "Builder"
    },
    "withdrawal_details": {
      "type": "emergency",
      "requested_amount": 5750,
      "withdrawal_date": "2025-08-07T10:30:00Z",
      "withdrawable_amount": 5750
    },
    "penalty_details": {
      "penalty_applicable": true,
      "penalty_amount": 2875,
      "penalty_percentage": 50,
      "penalty_reason": "Emergency withdrawal within lock-in period",
      "net_withdrawable_amount": 2875
    },
    "eligibility": {
      "is_eligible": true,
      "requires_approval": true,
      "approval_reason": "Emergency withdrawal requires admin approval"
    },
    "net_amount": 2875
  }
}
```

### Get Dashboard Metrics

```http
GET /dashboard/metrics
```

Returns comprehensive dashboard metrics including portfolio, earnings, and performance data.

**Response:**
```json
{
  "success": true,
  "data": {
    "portfolio_overview": {
      "total_invested": 15000,
      "current_value": 17250,
      "total_profit": 2250,
      "roi_percentage": 15.0,
      "investment_count": 3,
      "tier_distribution": {
        "Builder": 2,
        "Starter": 1
      },
      "average_investment": 5000
    },
    "earnings_breakdown": {
      "total_earnings": 2250,
      "referral_commissions": 750,
      "profit_shares": 1200,
      "matrix_commissions": 300,
      "pending_earnings": 150
    },
    "investment_performance": {
      "total_count": 3,
      "performance_summary": [...],
      "tier_performance": [...],
      "growth_trends": [...]
    },
    "referral_metrics": {
      "statistics": {
        "total_referrals_count": 18,
        "active_referrals_count": 12,
        "total_commission_earned": 750,
        "pending_commission": 150
      },
      "matrix_performance": {
        "total_downline": 45,
        "matrix_levels_filled": 2,
        "spillover_received": 8,
        "matrix_commissions_earned": 300
      }
    },
    "tier_information": {
      "current_tier": "Builder",
      "upgrade_eligibility": {
        "eligible": true,
        "next_tier": "Leader",
        "required_amount": 5000
      },
      "progress_percentage": 75,
      "tier_benefits": [...]
    },
    "matrix_performance": {
      "position": {
        "level": 2,
        "position": 3,
        "sponsor": "Jane Smith"
      },
      "structure": {...},
      "downline_counts": {
        "level_1": 3,
        "level_2": 9,
        "level_3": 15
      }
    },
    "withdrawal_summary": {
      "total_investments": 3,
      "eligible_for_withdrawal": 1,
      "total_withdrawable_amount": 5750,
      "pending_withdrawals": 0,
      "withdrawal_restrictions": 2
    },
    "growth_trends": {
      "monthly_data": [...],
      "total_growth_rate": 18.5,
      "investment_growth_rate": 12.3,
      "commission_growth_rate": 25.7
    }
  }
}
```

### Get Investment Trends

```http
GET /dashboard/investment-trends
```

**Query Parameters:**
- `period` (optional) - Time period (day, week, month, quarter, year)
- `investment_id` (optional) - Specific investment ID

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "month",
    "trends": {
      "investment_growth": [
        {
          "date": "2025-07-01",
          "value": 12000,
          "growth_rate": 8.5
        },
        {
          "date": "2025-08-01",
          "value": 15000,
          "growth_rate": 25.0
        }
      ],
      "earnings_trend": [
        {
          "date": "2025-07-01",
          "earnings": 1800,
          "commissions": 600,
          "profit_shares": 1200
        }
      ],
      "roi_trend": [
        {
          "date": "2025-07-01",
          "roi": 12.5
        },
        {
          "date": "2025-08-01",
          "roi": 15.0
        }
      ]
    },
    "summary": {
      "total_growth": 25.0,
      "average_monthly_growth": 12.5,
      "best_performing_period": "2025-08",
      "volatility": 3.2
    }
  }
}
```

### Get Tier Upgrade Recommendations

```http
GET /dashboard/tier-upgrade-recommendations
```

**Response:**
```json
{
  "success": true,
  "data": {
    "eligibility": {
      "eligible": true,
      "current_tier": "Builder",
      "next_tier": "Leader",
      "required_amount": 5000,
      "current_amount": 15000,
      "additional_needed": 0
    },
    "recommendations": [
      {
        "tier": "Leader",
        "benefits": [
          "12% referral commission rate",
          "10% fixed profit share",
          "Level 3 referral bonuses"
        ],
        "investment_required": 0,
        "potential_earnings_increase": 450,
        "roi_improvement": 2.5
      }
    ],
    "tier_comparison": {
      "current": {
        "name": "Builder",
        "profit_rate": 7,
        "referral_rate": 10,
        "benefits": [...]
      },
      "next": {
        "name": "Leader",
        "profit_rate": 10,
        "referral_rate": 12,
        "benefits": [...]
      }
    },
    "upgrade_benefits": {
      "additional_annual_profit": 450,
      "increased_referral_rate": 2,
      "new_features": [
        "Level 3 referral bonuses",
        "Priority customer support"
      ]
    }
  }
}
```

### Get Matrix Data

```http
GET /dashboard/matrix-data
```

**Response:**
```json
{
  "success": true,
  "data": {
    "structure": {
      "level_1": [
        {
          "id": 123,
          "name": "John Doe",
          "position": 1,
          "investment_amount": 2500,
          "tier": "Starter",
          "joined_date": "2025-07-15"
        }
      ],
      "level_2": [...],
      "level_3": [...]
    },
    "position_details": {
      "level": 2,
      "position": 3,
      "sponsor": "Jane Smith",
      "placement_date": "2025-06-01"
    },
    "downline_counts": {
      "level_1": 3,
      "level_2": 9,
      "level_3": 15,
      "total": 27
    },
    "performance_metrics": {
      "total_downline": 27,
      "active_downline": 22,
      "matrix_commissions_earned": 300,
      "spillover_received": 8,
      "spillover_given": 12
    },
    "spillover_opportunities": {
      "has_opportunities": true,
      "next_position": {
        "level": 2,
        "position": 7,
        "placement_type": "spillover"
      },
      "available_slots": 2
    },
    "commission_potential": {
      "level_1": {
        "max_positions": 3,
        "current_filled": 3,
        "commission_rate": 2.5,
        "potential_monthly": 187.5
      },
      "level_2": {
        "max_positions": 9,
        "current_filled": 9,
        "commission_rate": 1.5,
        "potential_monthly": 337.5
      },
      "level_3": {
        "max_positions": 27,
        "current_filled": 15,
        "commission_rate": 1.0,
        "potential_monthly": 150
      }
    }
  }
}
```

### Get Notifications and Activity

```http
GET /dashboard/notifications-activity
```

**Query Parameters:**
- `limit` (optional) - Number of items to return (default: 20)
- `type` (optional) - Type of data (all, notifications, activities)

**Response:**
```json
{
  "success": true,
  "data": {
    "notifications": [
      {
        "type": "tier_upgrade_available",
        "title": "Tier Upgrade Available",
        "message": "You're eligible to upgrade to Leader tier!",
        "action_url": "/investments/tier-upgrade",
        "created_at": "2025-08-07T10:30:00Z",
        "priority": "high"
      }
    ],
    "activities": [
      {
        "id": 456,
        "action": "investment_created",
        "description": "Created new investment of K5000",
        "created_at": "2025-08-07T09:15:00Z",
        "loggable_type": "Investment",
        "loggable_id": 123
      }
    ],
    "combined_feed": [
      // Combined and sorted notifications and activities
    ]
  }
}
```

## Error Responses

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "investment_id": ["The investment id field is required."]
  }
}
```

### Not Found Error
```json
{
  "success": false,
  "message": "Investment not found",
  "code": "INVESTMENT_NOT_FOUND"
}
```

### Unauthorized Access
```json
{
  "success": false,
  "message": "You are not authorized to access this investment",
  "code": "UNAUTHORIZED_ACCESS"
}
```