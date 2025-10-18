# Withdrawal API

The Withdrawal API manages withdrawal requests, policy enforcement, penalty calculations, and approval workflows.

## Endpoints

### List Withdrawals

```http
GET /withdrawals
```

Returns paginated list of withdrawal requests for the authenticated user.

**Query Parameters:**
- `status` (optional) - Filter by status (pending, pending_approval, approved, rejected, processed)
- `type` (optional) - Filter by type (full, partial, emergency, profits_only)
- `date_from` (optional) - Start date filter
- `date_to` (optional) - End date filter
- `page` (optional) - Page number
- `per_page` (optional) - Items per page (default: 15)

**Response:**
```json
{
  "success": true,
  "data": {
    "withdrawals": {
      "data": [
        {
          "id": 123,
          "investment_id": 456,
          "amount": 2500,
          "type": "partial",
          "status": "approved",
          "penalty_amount": 0,
          "net_amount": 2500,
          "reason": "Personal financial needs",
          "requested_at": "2025-08-01T10:00:00Z",
          "approved_at": "2025-08-02T14:30:00Z",
          "processed_at": null,
          "investment": {
            "id": 456,
            "amount": 5000,
            "tier": {
              "name": "Builder"
            }
          },
          "user": {
            "id": 789,
            "name": "John Doe",
            "email": "john@example.com"
          }
        }
      ],
      "current_page": 1,
      "last_page": 2,
      "per_page": 15,
      "total": 25
    },
    "summary": {
      "total_requests": 25,
      "pending_requests": 3,
      "approved_requests": 18,
      "rejected_requests": 2,
      "processed_requests": 16,
      "total_amount_requested": 125000,
      "total_amount_approved": 98000,
      "total_penalties_applied": 12500
    },
    "filters": {
      "status": null,
      "type": null,
      "date_from": null,
      "date_to": null
    }
  }
}
```

### Create Withdrawal Request

```http
POST /withdrawals
```

Creates a new withdrawal request.

**Request Body:**
```json
{
  "investment_id": 456,
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
    "withdrawal": {
      "id": 124,
      "investment_id": 456,
      "amount": 2500,
      "type": "partial",
      "status": "pending",
      "penalty_amount": 0,
      "net_amount": 2500,
      "reason": "Personal financial needs",
      "requested_at": "2025-08-07T10:30:00Z",
      "estimated_processing_time": "3-5 business days",
      "investment": {
        "id": 456,
        "amount": 5000,
        "current_value": 5750,
        "tier": {
          "name": "Builder"
        }
      }
    }
  },
  "message": "Withdrawal request submitted successfully."
}
```

### Get Withdrawal Details

```http
GET /withdrawals/{id}
```

Returns detailed information about a specific withdrawal request.

**Response:**
```json
{
  "success": true,
  "data": {
    "withdrawal": {
      "id": 123,
      "investment_id": 456,
      "amount": 2500,
      "type": "partial",
      "status": "approved",
      "penalty_amount": 0,
      "net_amount": 2500,
      "reason": "Personal financial needs",
      "requested_at": "2025-08-01T10:00:00Z",
      "approved_at": "2025-08-02T14:30:00Z",
      "approved_by": {
        "id": 1,
        "name": "Admin User"
      },
      "processed_at": null,
      "admin_notes": "Approved after verification",
      "investment": {
        "id": 456,
        "amount": 5000,
        "current_value": 5750,
        "investment_date": "2025-07-15",
        "lock_in_period_end": "2026-07-15",
        "tier": {
          "id": 3,
          "name": "Builder",
          "fixed_profit_rate": 7.0
        }
      },
      "user": {
        "id": 789,
        "name": "John Doe",
        "email": "john@example.com"
      }
    },
    "penalty_breakdown": {
      "base_penalty_rate": 0,
      "early_withdrawal_penalty": 0,
      "emergency_penalty": 0,
      "total_penalty_amount": 0,
      "penalty_reason": "No penalty - withdrawal after lock-in period"
    },
    "processing_details": {
      "estimated_processing_time": "3-5 business days",
      "processing_fee": 25,
      "payment_method": "bank_transfer",
      "account_details": "****1234"
    },
    "timeline": [
      {
        "status": "requested",
        "timestamp": "2025-08-01T10:00:00Z",
        "note": "Withdrawal request submitted"
      },
      {
        "status": "approved",
        "timestamp": "2025-08-02T14:30:00Z",
        "note": "Approved by Admin User",
        "admin": "Admin User"
      }
    ]
  }
}
```

### Get Withdrawal Creation Form Data

```http
GET /withdrawals/create
```

Returns data needed for withdrawal creation form.

**Response:**
```json
{
  "success": true,
  "data": {
    "eligible_investments": [
      {
        "id": 456,
        "amount": 5000,
        "current_value": 5750,
        "tier": "Builder",
        "investment_date": "2025-07-15",
        "lock_in_status": {
          "is_within_lock_in": false,
          "days_remaining": 0,
          "eligible_date": "2026-07-15"
        },
        "withdrawal_eligibility": {
          "can_withdraw": true,
          "max_withdrawable": 5750,
          "penalties": {
            "full_withdrawal": 0,
            "partial_withdrawal": 0,
            "emergency_withdrawal": 2875
          }
        }
      }
    ],
    "withdrawal_types": [
      {
        "type": "full",
        "name": "Full Withdrawal",
        "description": "Withdraw entire investment amount",
        "requires_approval": false
      },
      {
        "type": "partial",
        "name": "Partial Withdrawal",
        "description": "Withdraw portion of profits only",
        "requires_approval": false
      },
      {
        "type": "emergency",
        "name": "Emergency Withdrawal",
        "description": "Immediate withdrawal with penalties",
        "requires_approval": true
      }
    ],
    "policy_information": {
      "lock_in_period": "12 months",
      "early_withdrawal_penalties": {
        "0-1_months": "100% profit + 12% capital",
        "1-3_months": "100% profit + 12% capital",
        "3-6_months": "50% profit + 6% capital",
        "6-12_months": "30% profit + 3% capital"
      },
      "processing_time": "3-5 business days",
      "minimum_withdrawal": 100
    }
  }
}
```

### Approve Withdrawal (Admin Only)

```http
PATCH /withdrawals/{id}/approve
```

Approves a pending withdrawal request (admin only).

**Request Body:**
```json
{
  "admin_notes": "Verified and approved for processing",
  "processing_priority": "normal"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "withdrawal": {
      "id": 123,
      "status": "approved",
      "approved_at": "2025-08-07T10:30:00Z",
      "approved_by": 1,
      "admin_notes": "Verified and approved for processing",
      "estimated_processing_date": "2025-08-12"
    }
  },
  "message": "Withdrawal request approved successfully."
}
```

### Reject Withdrawal (Admin Only)

```http
PATCH /withdrawals/{id}/reject
```

Rejects a pending withdrawal request (admin only).

**Request Body:**
```json
{
  "rejection_reason": "Insufficient documentation provided",
  "admin_notes": "User needs to provide additional verification documents"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "withdrawal": {
      "id": 123,
      "status": "rejected",
      "rejected_at": "2025-08-07T10:30:00Z",
      "rejected_by": 1,
      "rejection_reason": "Insufficient documentation provided",
      "admin_notes": "User needs to provide additional verification documents"
    }
  },
  "message": "Withdrawal request rejected."
}
```

### Get Withdrawal Eligibility Check

```http
GET /withdrawals/eligibility-check
```

Checks withdrawal eligibility for user's investments.

**Query Parameters:**
- `investment_id` (optional) - Specific investment ID
- `withdrawal_type` (optional) - Type of withdrawal to check

**Response:**
```json
{
  "success": true,
  "data": {
    "eligible_investments": [
      {
        "investment": {
          "id": 456,
          "amount": 5000,
          "current_value": 5750,
          "tier": "Builder"
        },
        "eligibility": {
          "can_withdraw": true,
          "withdrawal_types_allowed": ["full", "partial"],
          "lock_in_period_remaining": 0,
          "reasons": []
        },
        "withdrawal_scenarios": {
          "full_withdrawal": {
            "amount": 5750,
            "penalty": 0,
            "net_amount": 5750,
            "requires_approval": false
          },
          "partial_withdrawal": {
            "max_amount": 2875,
            "penalty": 0,
            "net_amount": 2875,
            "requires_approval": false
          },
          "emergency_withdrawal": {
            "amount": 5750,
            "penalty": 2875,
            "net_amount": 2875,
            "requires_approval": true
          }
        }
      }
    ],
    "ineligible_investments": [
      {
        "investment": {
          "id": 789,
          "amount": 2500,
          "current_value": 2625,
          "tier": "Starter"
        },
        "eligibility": {
          "can_withdraw": false,
          "reasons": [
            "Investment is within 12-month lock-in period",
            "Only emergency withdrawal available with penalties"
          ],
          "lock_in_period_remaining": 180,
          "eligible_date": "2026-02-15"
        }
      }
    ],
    "summary": {
      "total_investments": 2,
      "eligible_count": 1,
      "ineligible_count": 1,
      "total_withdrawable_amount": 5750
    }
  }
}
```

### Calculate Withdrawal Penalties

```http
POST /withdrawals/calculate-penalties
```

Calculates penalties for a specific withdrawal scenario.

**Request Body:**
```json
{
  "investment_id": 456,
  "withdrawal_type": "emergency",
  "amount": 5750,
  "withdrawal_date": "2025-08-07"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "investment": {
      "id": 456,
      "amount": 5000,
      "current_value": 5750,
      "investment_date": "2025-07-15",
      "lock_in_period_end": "2026-07-15",
      "days_since_investment": 23
    },
    "withdrawal_details": {
      "type": "emergency",
      "requested_amount": 5750,
      "withdrawal_date": "2025-08-07"
    },
    "penalty_calculation": {
      "penalty_applicable": true,
      "penalty_category": "0-1_months",
      "base_penalty_rate": 50,
      "additional_penalties": {
        "early_withdrawal": 12,
        "emergency_processing": 5
      },
      "total_penalty_rate": 67,
      "penalty_amount": 3852.5,
      "net_withdrawable_amount": 1897.5
    },
    "commission_clawback": {
      "applicable": true,
      "referrer_clawback_rate": 50,
      "estimated_clawback_amount": 250,
      "affected_referrers": [
        {
          "referrer_id": 123,
          "level": 1,
          "original_commission": 500,
          "clawback_amount": 250
        }
      ]
    },
    "alternatives": {
      "wait_for_lock_in_end": {
        "date": "2026-07-15",
        "days_to_wait": 342,
        "penalty_savings": 3852.5
      },
      "partial_withdrawal_after_lock_in": {
        "max_amount": 2875,
        "penalty": 0,
        "available_date": "2026-07-15"
      }
    }
  }
}
```

### Get Withdrawal Statistics (Admin)

```http
GET /withdrawals/statistics
```

Returns withdrawal statistics for admin dashboard (admin only).

**Query Parameters:**
- `period` (optional) - Time period (week, month, quarter, year)
- `status` (optional) - Filter by status

**Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_requests": 125,
      "pending_requests": 8,
      "approved_requests": 95,
      "rejected_requests": 12,
      "processed_requests": 85,
      "total_amount_requested": 625000,
      "total_amount_approved": 487500,
      "total_penalties_collected": 62500
    },
    "by_type": {
      "full_withdrawal": {
        "count": 45,
        "total_amount": 225000,
        "average_amount": 5000
      },
      "partial_withdrawal": {
        "count": 65,
        "total_amount": 325000,
        "average_amount": 5000
      },
      "emergency_withdrawal": {
        "count": 15,
        "total_amount": 75000,
        "average_amount": 5000,
        "total_penalties": 37500
      }
    },
    "by_tier": {
      "Basic": {
        "count": 25,
        "total_amount": 62500
      },
      "Starter": {
        "count": 40,
        "total_amount": 200000
      },
      "Builder": {
        "count": 35,
        "total_amount": 262500
      },
      "Leader": {
        "count": 20,
        "total_amount": 100000
      },
      "Elite": {
        "count": 5,
        "total_amount": 0
      }
    },
    "trends": {
      "monthly_requests": [
        {
          "month": "2025-06",
          "count": 18,
          "amount": 90000
        },
        {
          "month": "2025-07",
          "count": 22,
          "amount": 110000
        },
        {
          "month": "2025-08",
          "count": 15,
          "amount": 75000
        }
      ],
      "approval_rate": 76.0,
      "average_processing_time": 4.2,
      "penalty_rate": 12.0
    }
  }
}
```

## Validation Rules

### Create Withdrawal Request
- `investment_id`: Required, must exist and belong to authenticated user
- `withdrawal_type`: Required, must be one of: full, partial, emergency, profits_only
- `amount`: Required for partial withdrawals, numeric, minimum 100
- `reason`: Required for emergency withdrawals, string, maximum 500 characters
- `otp_code`: Required, must be valid OTP token

### Approve/Reject Withdrawal (Admin)
- `admin_notes`: Optional, string, maximum 1000 characters
- `rejection_reason`: Required for rejection, string, maximum 500 characters
- `processing_priority`: Optional, must be one of: low, normal, high, urgent

### Calculate Penalties
- `investment_id`: Required, must exist
- `withdrawal_type`: Required, must be valid withdrawal type
- `amount`: Required, numeric, minimum 1
- `withdrawal_date`: Optional, valid date format

## Error Responses

### Investment Not Found
```json
{
  "success": false,
  "message": "Investment not found or does not belong to user",
  "code": "INVESTMENT_NOT_FOUND"
}
```

### Withdrawal Not Allowed
```json
{
  "success": false,
  "message": "Withdrawal not allowed",
  "errors": {
    "withdrawal_type": ["Investment is within lock-in period. Only emergency withdrawal available with penalties."]
  },
  "code": "WITHDRAWAL_NOT_ALLOWED"
}
```

### Invalid OTP
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "otp_code": ["Invalid OTP code"]
  }
}
```

### Insufficient Withdrawal Amount
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "amount": ["Withdrawal amount exceeds available balance"]
  }
}
```

### Unauthorized Access
```json
{
  "success": false,
  "message": "You are not authorized to access this withdrawal request",
  "code": "UNAUTHORIZED_ACCESS"
}
```

### Processing Error
```json
{
  "success": false,
  "message": "Failed to process withdrawal request",
  "error": "System temporarily unavailable",
  "code": "PROCESSING_ERROR"
}
```