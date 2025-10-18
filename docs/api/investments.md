# Investment API

The Investment API handles all investment-related operations including creation, tier management, performance tracking, and withdrawal requests.

## Endpoints

### List Investments

```http
GET /investments
```

Returns paginated list of investments for the authenticated user (or all investments for admin users).

**Query Parameters:**
- `status` (optional) - Filter by status (pending, active, rejected)
- `tier` (optional) - Filter by tier name
- `date_range` (optional) - Filter by date range
- `page` (optional) - Page number for pagination
- `per_page` (optional) - Items per page (default: 10)

**Response:**
```json
{
  "success": true,
  "data": {
    "investments": {
      "data": [
        {
          "id": 123,
          "amount": 5000,
          "status": "active",
          "investment_date": "2025-07-15",
          "lock_in_period_end": "2026-07-15",
          "current_value": 5750,
          "roi": 15.0,
          "tier": {
            "id": 3,
            "name": "Builder",
            "minimum_investment": 2500,
            "fixed_profit_rate": 7.0
          },
          "user": {
            "id": 456,
            "name": "John Doe",
            "email": "john@example.com"
          }
        }
      ],
      "current_page": 1,
      "last_page": 3,
      "per_page": 10,
      "total": 25
    },
    "stats": {
      "total_investments": 25,
      "active_investments": 20,
      "pending_investments": 3,
      "total_amount": 125000,
      "total_current_value": 143750,
      "average_roi": 15.0
    },
    "filters": {
      "status": null,
      "tier": null,
      "date_range": null
    },
    "tiers": [
      {
        "name": "Basic",
        "minimum_investment": 500
      }
    ]
  }
}
```

### Create Investment

```http
POST /investments
```

Creates a new investment for the authenticated user.

**Request Body:**
```json
{
  "tier_id": 3,
  "amount": 5000,
  "payment_method": "bank_transfer",
  "referrer_code": "REF123456",
  "payment_proof": "base64_encoded_file_or_file_upload"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 124,
      "amount": 5000,
      "status": "pending",
      "investment_date": "2025-08-07",
      "lock_in_period_end": "2026-08-07",
      "tier": {
        "id": 3,
        "name": "Builder",
        "minimum_investment": 2500,
        "fixed_profit_rate": 7.0
      },
      "payment_method": "bank_transfer",
      "payment_proof": "storage/payment_proofs/proof_124.jpg"
    }
  },
  "message": "Investment created successfully and is pending approval."
}
```

### Get Investment Details

```http
GET /investments/{id}
```

Returns detailed information about a specific investment.

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 123,
      "amount": 5000,
      "status": "active",
      "investment_date": "2025-07-15",
      "lock_in_period_end": "2026-07-15",
      "current_value": 5750,
      "roi": 15.0,
      "tier": {
        "id": 3,
        "name": "Builder",
        "minimum_investment": 2500,
        "fixed_profit_rate": 7.0
      },
      "user": {
        "id": 456,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "referral_commissions": [
        {
          "id": 789,
          "amount": 500,
          "level": 1,
          "status": "paid",
          "referrer": {
            "name": "Jane Smith"
          }
        }
      ]
    },
    "metrics": {
      "days_invested": 23,
      "profit_earned": 750,
      "projected_annual_return": 350,
      "performance_vs_tier": 105.2
    },
    "withdrawal_info": {
      "can_withdraw": false,
      "lock_in_remaining_days": 342,
      "early_withdrawal_penalty": 50,
      "eligible_date": "2026-07-15"
    },
    "projections": {
      "6_months": 5175,
      "12_months": 5350,
      "24_months": 5700
    },
    "penalties": {
      "emergency_withdrawal": {
        "penalty_percentage": 50,
        "penalty_amount": 2875,
        "net_amount": 2875
      },
      "early_withdrawal": {
        "penalty_percentage": 30,
        "penalty_amount": 1725,
        "net_amount": 4025
      }
    },
    "lock_in_status": {
      "is_within_lock_in": true,
      "days_remaining": 342,
      "eligible_date": "2026-07-15"
    },
    "withdrawal_scenarios": {
      "full_withdrawal": {
        "amount": 5750,
        "penalty": 1725,
        "net_amount": 4025
      },
      "partial_withdrawal": {
        "max_amount": 2875,
        "penalty": 862.5,
        "net_amount": 2012.5
      }
    },
    "tier_benefits": [
      "7% fixed annual profit share",
      "10% direct referral commission",
      "3% level 2 referral bonus"
    ]
  }
}
```

### Get Investment History

```http
GET /investments/history/all
```

Returns comprehensive investment history for the authenticated user.

**Query Parameters:**
- `status` (optional) - Filter by status
- `tier` (optional) - Filter by tier name
- `date_from` (optional) - Start date filter
- `date_to` (optional) - End date filter
- `page` (optional) - Page number
- `per_page` (optional) - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "investments": {
      "data": [...],
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 25
    },
    "performance_metrics": {
      "total_invested": 25000,
      "current_value": 28750,
      "total_profit": 3750,
      "average_roi": 15.0,
      "best_performing": {
        "id": 123,
        "roi": 18.5
      },
      "worst_performing": {
        "id": 124,
        "roi": 12.3
      }
    },
    "filters": {
      "status": null,
      "tier": null,
      "date_from": null,
      "date_to": null
    },
    "tiers": [
      {
        "name": "Basic"
      },
      {
        "name": "Starter"
      }
    ]
  }
}
```

### Get Investment Performance

```http
GET /investments/performance/metrics
```

Returns detailed performance metrics for user's investments.

**Query Parameters:**
- `period` (optional) - Time period (day, week, month, quarter, year)

**Response:**
```json
{
  "success": true,
  "data": {
    "metrics": {
      "total_return": 15.5,
      "volatility": 3.2,
      "sharpe_ratio": 1.8,
      "max_drawdown": -2.1,
      "period_performance": [
        {
          "date": "2025-07-01",
          "value": 24500,
          "return": 12.3
        }
      ]
    },
    "user_metrics": {
      "period_return": 15.5,
      "volatility": 3.2,
      "sharpe_ratio": 1.8,
      "max_drawdown": -2.1
    },
    "portfolio_breakdown": [
      {
        "tier": "Builder",
        "count": 2,
        "total_amount": 10000,
        "current_value": 11500,
        "percentage": 65.5
      },
      {
        "tier": "Starter",
        "count": 1,
        "total_amount": 1000,
        "current_value": 1150,
        "percentage": 34.5
      }
    ],
    "period": "month"
  }
}
```

### Request Tier Upgrade

```http
POST /investments/tier-upgrade
```

Requests a tier upgrade by making an additional investment.

**Request Body:**
```json
{
  "target_tier_id": 4,
  "additional_amount": 2500,
  "payment_method": "bank_transfer",
  "payment_proof": "base64_encoded_file_or_file_upload"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 125,
      "amount": 2500,
      "status": "pending",
      "is_tier_upgrade": true,
      "previous_tier_id": 3,
      "tier": {
        "id": 4,
        "name": "Leader",
        "minimum_investment": 5000,
        "fixed_profit_rate": 10.0
      }
    }
  },
  "message": "Tier upgrade request submitted successfully."
}
```

### Request Withdrawal

```http
POST /investments/{id}/withdrawal
```

Requests a withdrawal from a specific investment.

**Request Body:**
```json
{
  "withdrawal_type": "partial",
  "amount": 2500,
  "reason": "Personal financial needs",
  "otp_code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "withdrawal_request": {
      "id": 789,
      "investment_id": 123,
      "amount": 2500,
      "type": "partial",
      "status": "pending",
      "penalty_amount": 0,
      "net_amount": 2500,
      "reason": "Personal financial needs",
      "requested_at": "2025-08-07T10:30:00Z"
    }
  },
  "message": "Withdrawal request submitted successfully."
}
```

### Approve Investment (Admin Only)

```http
PATCH /investments/{id}/approve
```

Approves a pending investment (admin only).

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 123,
      "status": "active",
      "approved_at": "2025-08-07T10:30:00Z",
      "approved_by": 1
    }
  },
  "message": "Investment approved successfully."
}
```

### Reject Investment (Admin Only)

```http
PATCH /investments/{id}/reject
```

Rejects a pending investment (admin only).

**Request Body:**
```json
{
  "rejection_reason": "Insufficient payment proof"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 123,
      "status": "rejected",
      "rejected_at": "2025-08-07T10:30:00Z",
      "rejected_by": 1,
      "rejection_reason": "Insufficient payment proof"
    }
  },
  "message": "Investment rejected."
}
```

### Get Investment Opportunities

```http
GET /opportunities
```

Returns available investment opportunities.

**Response:**
```json
{
  "success": true,
  "data": {
    "opportunities": {
      "data": [
        {
          "id": 1,
          "name": "VBIF Growth Fund",
          "description": "High-growth investment opportunity with guaranteed returns",
          "minimum_investment": 500,
          "expected_returns": "15-20% annually",
          "risk_level": "Medium",
          "duration": "12 months",
          "status": "active",
          "category": {
            "name": "Growth Funds"
          }
        }
      ],
      "current_page": 1,
      "last_page": 1,
      "per_page": 10,
      "total": 3
    }
  }
}
```

### Get Portfolio Summary

```http
GET /portfolio
```

Returns comprehensive portfolio summary for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "portfolio": {
      "total_investment": 15000,
      "current_value": 17250,
      "total_profit": 2250,
      "roi_percentage": 15.0,
      "total_earnings": 2250,
      "referral_earnings": 750,
      "profit_earnings": 1200,
      "matrix_earnings": 300,
      "pending_earnings": 150,
      "active_referrals": 12,
      "total_referrals": 18,
      "investment_count": 3,
      "tier_distribution": {
        "Builder": 2,
        "Starter": 1
      },
      "average_investment": 5000
    },
    "investments": [
      {
        "id": 123,
        "amount": 5000,
        "current_value": 5750,
        "profit": 750,
        "roi": 15.0,
        "tier": "Builder",
        "investment_date": "2025-07-15",
        "lock_in_status": {
          "is_within_lock_in": true,
          "days_remaining": 342
        },
        "withdrawal_eligibility": {
          "can_withdraw": false,
          "reason": "Within lock-in period"
        },
        "performance_metrics": {
          "days_invested": 23,
          "daily_return": 0.65,
          "projected_annual": 350
        }
      }
    ],
    "recent_transactions": [
      {
        "id": 456,
        "reference_number": "TXN-2025-001",
        "investment_amount": 5000,
        "transaction_type": "investment",
        "status": "completed",
        "created_at": "2025-07-15T09:00:00Z"
      }
    ],
    "tier_information": {
      "current_tier": "Builder",
      "eligible_for_upgrade": true,
      "next_tier": "Leader",
      "upgrade_requirements": {
        "additional_investment": 0,
        "total_required": 5000,
        "current_total": 15000
      }
    },
    "matrix_information": {
      "position": {
        "level": 2,
        "position": 3
      },
      "downline_counts": {
        "level_1": 3,
        "level_2": 9,
        "level_3": 15
      },
      "structure": {
        "level_1": [...],
        "level_2": [...]
      }
    }
  }
}
```

## Validation Rules

### Investment Creation
- `tier_id`: Required, must exist in investment_tiers table
- `amount`: Required, numeric, minimum based on tier requirements
- `payment_method`: Required, must be valid payment method
- `referrer_code`: Optional, must exist if provided
- `payment_proof`: Required file upload

### Tier Upgrade Request
- `target_tier_id`: Required, must exist and be higher than current tier
- `additional_amount`: Required, numeric, minimum 1
- `payment_method`: Required
- `payment_proof`: Required file upload

### Withdrawal Request
- `withdrawal_type`: Required, must be one of: full, partial, emergency
- `amount`: Required for partial withdrawals, numeric, minimum 1
- `reason`: Required for emergency withdrawals
- `otp_code`: Required, must be valid OTP

## Error Responses

### Insufficient Investment Amount
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "amount": ["Minimum investment for Builder tier is K2500"]
  }
}
```

### Invalid Tier Upgrade
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "target_tier_id": ["You can only upgrade to a higher tier"]
  }
}
```

### Withdrawal Not Allowed
```json
{
  "success": false,
  "message": "Withdrawal not allowed: Investment is within lock-in period",
  "code": "WITHDRAWAL_NOT_ALLOWED"
}
```

### Investment Not Found
```json
{
  "success": false,
  "message": "Investment not found",
  "code": "INVESTMENT_NOT_FOUND"
}
```