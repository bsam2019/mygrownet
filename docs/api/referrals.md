# Referral API

The Referral API manages the multi-level referral system, 3x3 matrix structure, commission calculations, and referral tree visualization.

## Endpoints

### Get Referral Dashboard

```http
GET /referrals
```

Returns comprehensive referral dashboard data including statistics, matrix visualization, and recent activity.

**Response:**
```json
{
  "success": true,
  "data": {
    "referral_stats": {
      "total_referrals_count": 18,
      "active_referrals_count": 12,
      "direct_referrals_count": 6,
      "indirect_referrals_count": 12,
      "total_commission_earned": 2250,
      "pending_commission": 150,
      "this_month_commission": 450,
      "commission_by_level": {
        "level_1": 1500,
        "level_2": 600,
        "level_3": 150
      }
    },
    "matrix_data": {
      "structure": {
        "level_1": [
          {
            "id": 123,
            "name": "John Doe",
            "position": 1,
            "investment_amount": 2500,
            "tier": "Starter",
            "joined_date": "2025-07-15",
            "status": "active"
          }
        ],
        "level_2": [...],
        "level_3": [...]
      },
      "visualization_data": {
        "total_positions": 39,
        "filled_positions": 27,
        "available_positions": 12,
        "spillover_received": 8,
        "spillover_given": 12
      }
    },
    "recent_referrals": [
      {
        "id": 456,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "joined_date": "2025-08-01",
        "investment_amount": 1000,
        "tier": "Starter",
        "status": "active",
        "commission_earned": 100
      }
    ],
    "recent_commissions": [
      {
        "id": 789,
        "amount": 100,
        "level": 1,
        "status": "paid",
        "referee": {
          "name": "Jane Smith"
        },
        "investment": {
          "amount": 1000
        },
        "created_at": "2025-08-01T10:00:00Z"
      }
    ],
    "referral_code": "REF123456",
    "referral_link": "https://vbif.com/register?ref=REF123456",
    "matrix_performance": {
      "total_downline": 27,
      "matrix_levels_filled": 2,
      "spillover_received": 8,
      "matrix_commissions_earned": 675,
      "average_downline_investment": 1875
    }
  }
}
```

### Get Referral Tree

```http
GET /referrals/tree
```

Returns hierarchical referral tree structure with investment data.

**Query Parameters:**
- `max_level` (optional) - Maximum tree depth (default: 3)

**Response:**
```json
{
  "success": true,
  "data": {
    "tree": {
      "user": {
        "id": 1,
        "name": "Root User",
        "referral_code": "REF123456",
        "total_investment": 15000,
        "tier": "Builder"
      },
      "children": [
        {
          "user": {
            "id": 123,
            "name": "John Doe",
            "referral_code": "REF789012",
            "total_investment": 2500,
            "tier": "Starter"
          },
          "level": 1,
          "commission_earned": 250,
          "children": [
            {
              "user": {
                "id": 456,
                "name": "Jane Smith",
                "total_investment": 1000,
                "tier": "Basic"
              },
              "level": 2,
              "commission_earned": 30,
              "children": []
            }
          ]
        }
      ]
    },
    "statistics": {
      "total_referrals_count": 18,
      "active_referrals_count": 12,
      "total_commission_earned": 2250,
      "levels_breakdown": {
        "level_1": {
          "count": 6,
          "total_investment": 15000,
          "commission_earned": 1500
        },
        "level_2": {
          "count": 9,
          "total_investment": 9000,
          "commission_earned": 600
        },
        "level_3": {
          "count": 3,
          "total_investment": 3000,
          "commission_earned": 150
        }
      }
    },
    "max_level": 3
  }
}
```

### Get Referral Statistics

```http
GET /referrals/statistics
```

Returns detailed referral statistics and performance metrics.

**Response:**
```json
{
  "success": true,
  "data": {
    "referral_statistics": {
      "total_referrals_count": 18,
      "active_referrals_count": 12,
      "direct_referrals_count": 6,
      "indirect_referrals_count": 12,
      "total_commission_earned": 2250,
      "pending_commission": 150,
      "this_month_commission": 450,
      "commission_by_level": {
        "level_1": 1500,
        "level_2": 600,
        "level_3": 150
      },
      "average_commission_per_referral": 125,
      "highest_earning_referral": 250,
      "conversion_rate": 75.5
    },
    "matrix_performance": {
      "total_downline": 27,
      "matrix_levels_filled": 2,
      "spillover_received": 8,
      "spillover_given": 12,
      "matrix_commissions_earned": 675,
      "average_downline_investment": 1875,
      "matrix_completion_percentage": 69.2
    },
    "earnings_breakdown": {
      "total_earnings": 2925,
      "referral_commissions": 2250,
      "matrix_commissions": 675,
      "profit_shares": 0,
      "pending_earnings": 150
    }
  }
}
```

### Get Commission History

```http
GET /referrals/commissions
```

Returns paginated commission history with filtering options.

**Query Parameters:**
- `status` (optional) - Filter by status (pending, paid, cancelled)
- `level` (optional) - Filter by referral level (1, 2, 3)
- `date_from` (optional) - Start date filter
- `date_to` (optional) - End date filter
- `page` (optional) - Page number
- `per_page` (optional) - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "commissions": {
      "data": [
        {
          "id": 789,
          "amount": 250,
          "level": 1,
          "status": "paid",
          "commission_rate": 10.0,
          "referee": {
            "id": 123,
            "name": "John Doe",
            "email": "john@example.com"
          },
          "investment": {
            "id": 456,
            "amount": 2500,
            "tier": "Starter"
          },
          "created_at": "2025-07-15T10:00:00Z",
          "paid_at": "2025-07-16T09:00:00Z"
        }
      ],
      "current_page": 1,
      "last_page": 3,
      "per_page": 15,
      "total": 42
    },
    "filters": {
      "status": null,
      "level": null,
      "date_from": null,
      "date_to": null
    }
  }
}
```

### Generate Referral Code

```http
POST /referrals/generate-code
```

Generates a new unique referral code for the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": {
    "referral_code": "REF789012",
    "referral_link": "https://vbif.com/register?ref=REF789012",
    "message": "Referral code generated successfully"
  }
}
```

### Validate Referral Code

```http
POST /referrals/validate-code
```

Validates a referral code and returns referrer information.

**Request Body:**
```json
{
  "referral_code": "REF123456"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "is_valid": true,
    "referrer": {
      "id": 1,
      "name": "Root User",
      "tier": "Builder"
    }
  }
}
```

**Invalid Code Response:**
```json
{
  "success": true,
  "data": {
    "is_valid": false,
    "message": "Invalid referral code"
  }
}
```

### Get Matrix Position

```http
GET /referrals/matrix-position
```

Returns detailed matrix position and structure data.

**Response:**
```json
{
  "success": true,
  "data": {
    "matrix_structure": {
      "level_1": [
        {
          "id": 123,
          "name": "John Doe",
          "position": 1,
          "investment_amount": 2500,
          "tier": "Starter",
          "joined_date": "2025-07-15",
          "commission_potential": 250
        }
      ],
      "level_2": [...],
      "level_3": [...]
    },
    "visualization_data": {
      "total_positions": 39,
      "filled_positions": 27,
      "available_positions": 12,
      "completion_percentage": 69.2,
      "spillover_received": 8,
      "spillover_given": 12
    },
    "position_details": {
      "level": 2,
      "position": 3,
      "sponsor": "Jane Smith",
      "placement_date": "2025-06-01T10:00:00Z"
    },
    "downline_counts": {
      "level_1": 3,
      "level_2": 9,
      "level_3": 15,
      "total": 27
    },
    "spillover_opportunities": {
      "has_opportunities": true,
      "next_position": {
        "level": 2,
        "position": 7,
        "placement_type": "spillover"
      },
      "available_slots": 2
    }
  }
}
```

### Get Matrix Genealogy

```http
GET /referrals/matrix-genealogy
```

Returns comprehensive matrix genealogy report.

**Query Parameters:**
- `max_level` (optional) - Maximum depth (default: 3)

**Response:**
```json
{
  "success": true,
  "data": {
    "genealogy": {
      "root": {
        "id": 1,
        "name": "Root User",
        "position": "root",
        "total_downline": 27,
        "total_commission_earned": 2250
      },
      "levels": {
        "level_1": [
          {
            "id": 123,
            "name": "John Doe",
            "position": 1,
            "sponsor": "Root User",
            "placement_date": "2025-07-15",
            "investment_amount": 2500,
            "tier": "Starter",
            "downline_count": 9,
            "commission_earned": 750
          }
        ],
        "level_2": [...],
        "level_3": [...]
      }
    },
    "summary": {
      "total_levels": 3,
      "total_members": 27,
      "total_investment_volume": 67500,
      "total_commissions_paid": 2250,
      "average_investment_per_member": 2500,
      "matrix_completion": 69.2
    },
    "max_level": 3
  }
}
```

### Get Referrals by Level

```http
GET /referrals/referrals-by-level
```

Returns referrals filtered by specific level.

**Query Parameters:**
- `level` (required) - Referral level (1, 2, or 3)

**Response:**
```json
{
  "success": true,
  "data": {
    "level": 1,
    "referrals": [
      {
        "id": 123,
        "name": "John Doe",
        "email": "john@example.com",
        "referral_code": "REF789012",
        "total_investment_amount": 2500,
        "tier": "Starter",
        "joined_date": "2025-07-15",
        "status": "active",
        "commission_earned": 250,
        "last_investment_date": "2025-07-15"
      }
    ],
    "count": 6,
    "total_investment": 15000,
    "active_count": 5
  }
}
```

### Calculate Commission

```http
POST /referrals/calculate-commission
```

Calculates potential commission for a given investment amount and tier.

**Request Body:**
```json
{
  "investment_amount": 5000,
  "tier_id": 3
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "investment_amount": 5000,
    "tier": "Builder",
    "referral_commissions": {
      "level_1": {
        "rate": 10.0,
        "amount": 500,
        "eligible": true
      },
      "level_2": {
        "rate": 3.0,
        "amount": 150,
        "eligible": true
      },
      "level_3": {
        "rate": 1.0,
        "amount": 50,
        "eligible": true
      }
    },
    "matrix_commissions": {
      "level_1": {
        "per_position": 125,
        "max_positions": 3,
        "max_earnings": 375
      },
      "level_2": {
        "per_position": 75,
        "max_positions": 9,
        "max_earnings": 675
      },
      "level_3": {
        "per_position": 50,
        "max_positions": 27,
        "max_earnings": 1350
      }
    },
    "total_potential_direct": 700,
    "has_matrix_position": true
  }
}
```

### Get Performance Report

```http
GET /referrals/performance-report
```

Returns comprehensive referral performance analysis.

**Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_referrals_count": 18,
      "active_referrals_count": 12,
      "total_commission_earned": 2250,
      "this_month_commission": 450,
      "commission_growth_rate": 25.5
    },
    "matrix_performance": {
      "total_downline": 27,
      "matrix_levels_filled": 2,
      "spillover_received": 8,
      "matrix_commissions_earned": 675,
      "matrix_completion_percentage": 69.2
    },
    "earnings_breakdown": {
      "total_earnings": 2925,
      "referral_commissions": 2250,
      "matrix_commissions": 675,
      "profit_shares": 0,
      "pending_earnings": 150
    },
    "tier_analysis": {
      "current_tier": "Builder",
      "tier_benefits": [
        "10% direct referral commission",
        "3% level 2 referral bonus",
        "1% level 3 referral bonus"
      ],
      "referral_tier_distribution": {
        "Basic": 3,
        "Starter": 9,
        "Builder": 6
      },
      "upgrade_potential": {
        "eligible": true,
        "next_tier": "Leader",
        "additional_benefits": [
          "12% direct referral commission",
          "5% level 2 referral bonus",
          "2% level 3 referral bonus"
        ]
      },
      "performance_vs_tier": {
        "referrals_vs_benchmark": {
          "actual": 18,
          "benchmark": 20,
          "performance_ratio": 0.9
        },
        "commission_vs_benchmark": {
          "actual_monthly": 187.5,
          "benchmark_monthly": 200,
          "performance_ratio": 0.94
        }
      }
    },
    "growth_metrics": {
      "monthly_growth": {
        "2025-07": {
          "count": 3,
          "total_amount": 300,
          "average_amount": 100
        },
        "2025-08": {
          "count": 2,
          "total_amount": 450,
          "average_amount": 225
        }
      },
      "total_growth_rate": 50.0,
      "average_monthly_earnings": 375,
      "best_performing_month": {
        "month": "2025-08",
        "total_amount": 450
      }
    },
    "commission_trends": {
      "by_level": {
        "level_1": {
          "count": 12,
          "total_amount": 1500,
          "average_amount": 125
        },
        "level_2": {
          "count": 8,
          "total_amount": 600,
          "average_amount": 75
        },
        "level_3": {
          "count": 3,
          "total_amount": 150,
          "average_amount": 50
        }
      },
      "by_month": {
        "2025-07": 300,
        "2025-08": 450
      },
      "recent_performance": {
        "last_30_days": 450,
        "last_90_days": 750,
        "last_year": 2250
      }
    }
  }
}
```

### Export Referral Data

```http
POST /referrals/export
```

Exports referral data in various formats.

**Request Body:**
```json
{
  "format": "csv",
  "type": "referrals"
}
```

**Parameters:**
- `format`: csv, excel, pdf
- `type`: referrals, commissions, matrix, performance

**Response:**
```json
{
  "success": true,
  "data": {
    "export_url": "https://vbif.com/exports/referrals_2025-08-07.csv",
    "file_size": "2.5MB",
    "record_count": 18,
    "generated_at": "2025-08-07T10:30:00Z"
  },
  "message": "Export data prepared successfully"
}
```

## Validation Rules

### Generate Referral Code
- No additional validation required (system generates unique code)

### Validate Referral Code
- `referral_code`: Required, string, maximum 20 characters

### Calculate Commission
- `investment_amount`: Required, numeric, minimum 500
- `tier_id`: Optional, must exist in investment_tiers table

### Export Data
- `format`: Required, must be one of: csv, excel, pdf
- `type`: Required, must be one of: referrals, commissions, matrix, performance

## Error Responses

### Invalid Referral Code
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "referral_code": ["The referral code field is required."]
  }
}
```

### Commission Calculation Error
```json
{
  "success": false,
  "message": "No investment tier found",
  "code": "TIER_NOT_FOUND"
}
```

### Export Error
```json
{
  "success": false,
  "message": "Failed to export data",
  "error": "Invalid export format specified",
  "code": "EXPORT_ERROR"
}
```

### Matrix Position Not Found
```json
{
  "success": false,
  "message": "Matrix position not found for user",
  "code": "MATRIX_POSITION_NOT_FOUND"
}
```