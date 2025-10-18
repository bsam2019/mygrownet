# OTP API

The OTP (One-Time Password) API provides secure verification for sensitive operations like withdrawals and account changes.

## Endpoints

### Generate OTP

```http
POST /otp/generate
```

Generates a new OTP token for the authenticated user.

**Request Body:**
```json
{
  "purpose": "withdrawal",
  "delivery_method": "sms"
}
```

**Parameters:**
- `purpose`: Required, must be one of: withdrawal, account_change, password_reset, investment
- `delivery_method`: Required, must be one of: sms, email

**Response:**
```json
{
  "success": true,
  "data": {
    "otp_id": "otp_123456789",
    "delivery_method": "sms",
    "phone_number": "****1234",
    "expires_at": "2025-08-07T10:35:00Z",
    "attempts_remaining": 3,
    "can_resend_at": "2025-08-07T10:32:00Z"
  },
  "message": "OTP sent successfully to your registered phone number"
}
```

### Verify OTP

```http
POST /otp/verify
```

Verifies an OTP token.

**Request Body:**
```json
{
  "otp_code": "123456",
  "purpose": "withdrawal"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "verified": true,
    "otp_id": "otp_123456789",
    "verified_at": "2025-08-07T10:33:00Z",
    "valid_for_minutes": 10
  },
  "message": "OTP verified successfully"
}
```

**Invalid OTP Response:**
```json
{
  "success": false,
  "message": "Invalid OTP code",
  "data": {
    "verified": false,
    "attempts_remaining": 2,
    "locked_until": null
  },
  "code": "INVALID_OTP"
}
```

### Resend OTP

```http
POST /otp/resend
```

Resends the OTP to the user.

**Request Body:**
```json
{
  "otp_id": "otp_123456789",
  "delivery_method": "email"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "otp_id": "otp_123456789",
    "delivery_method": "email",
    "email": "user@example.com",
    "expires_at": "2025-08-07T10:40:00Z",
    "attempts_remaining": 3,
    "can_resend_at": "2025-08-07T10:37:00Z"
  },
  "message": "OTP resent successfully to your email address"
}
```

### Get OTP Status

```http
GET /otp/status
```

Returns the current OTP status for the authenticated user.

**Query Parameters:**
- `purpose` (optional) - Filter by OTP purpose

**Response:**
```json
{
  "success": true,
  "data": {
    "active_otps": [
      {
        "otp_id": "otp_123456789",
        "purpose": "withdrawal",
        "delivery_method": "sms",
        "created_at": "2025-08-07T10:30:00Z",
        "expires_at": "2025-08-07T10:35:00Z",
        "attempts_remaining": 3,
        "is_verified": false,
        "can_resend": true,
        "can_resend_at": "2025-08-07T10:32:00Z"
      }
    ],
    "recent_verifications": [
      {
        "purpose": "investment",
        "verified_at": "2025-08-06T15:20:00Z",
        "delivery_method": "email"
      }
    ],
    "rate_limit_status": {
      "requests_remaining": 3,
      "reset_at": "2025-08-07T11:00:00Z",
      "is_locked": false
    }
  }
}
```

### Get OTP Statistics (Admin)

```http
GET /otp/stats
```

Returns OTP usage statistics (admin only).

**Query Parameters:**
- `period` (optional) - Time period (day, week, month)
- `user_id` (optional) - Specific user ID

**Response:**
```json
{
  "success": true,
  "data": {
    "overview": {
      "total_generated": 1250,
      "total_verified": 1100,
      "total_failed": 150,
      "verification_rate": 88.0,
      "average_attempts": 1.2
    },
    "by_purpose": {
      "withdrawal": {
        "generated": 650,
        "verified": 580,
        "failed": 70,
        "verification_rate": 89.2
      },
      "investment": {
        "generated": 400,
        "verified": 360,
        "failed": 40,
        "verification_rate": 90.0
      },
      "account_change": {
        "generated": 150,
        "verified": 120,
        "failed": 30,
        "verification_rate": 80.0
      },
      "password_reset": {
        "generated": 50,
        "verified": 40,
        "failed": 10,
        "verification_rate": 80.0
      }
    },
    "by_delivery_method": {
      "sms": {
        "generated": 800,
        "verified": 720,
        "failed": 80,
        "verification_rate": 90.0,
        "average_delivery_time": 15
      },
      "email": {
        "generated": 450,
        "verified": 380,
        "failed": 70,
        "verification_rate": 84.4,
        "average_delivery_time": 45
      }
    },
    "trends": {
      "daily_usage": [
        {
          "date": "2025-08-01",
          "generated": 45,
          "verified": 40,
          "failed": 5
        },
        {
          "date": "2025-08-02",
          "generated": 52,
          "verified": 47,
          "failed": 5
        }
      ],
      "peak_hours": [
        {
          "hour": 10,
          "count": 85
        },
        {
          "hour": 14,
          "count": 92
        },
        {
          "hour": 16,
          "count": 78
        }
      ]
    },
    "security_metrics": {
      "blocked_attempts": 25,
      "rate_limited_users": 8,
      "suspicious_patterns": 3,
      "fraud_attempts": 1
    }
  }
}
```

## OTP Configuration

### Rate Limiting
- **Generation**: 5 OTPs per 10 minutes per user
- **Verification**: 3 attempts per OTP
- **Resend**: 1 resend per 2 minutes

### Expiration Times
- **Withdrawal OTP**: 5 minutes
- **Investment OTP**: 10 minutes
- **Account Change OTP**: 15 minutes
- **Password Reset OTP**: 30 minutes

### Delivery Methods

#### SMS
- Delivery time: 15-30 seconds
- Format: "Your VBIF verification code is: {code}. Valid for {minutes} minutes. Do not share this code."
- Character limit: 160 characters

#### Email
- Delivery time: 30-60 seconds
- Subject: "VBIF Verification Code"
- HTML template with branding
- Includes security warnings

## Security Features

### Fraud Prevention
- IP-based rate limiting
- Device fingerprinting
- Suspicious pattern detection
- Automatic account locking

### Audit Logging
All OTP operations are logged with:
- User ID and IP address
- Purpose and delivery method
- Timestamp and result
- Device information
- Geographic location (if available)

### Encryption
- OTP codes encrypted in database
- Secure transmission protocols
- No plain text storage
- Automatic cleanup of expired tokens

## Integration Examples

### Frontend Integration (JavaScript)

```javascript
// Generate OTP
async function generateOTP(purpose, deliveryMethod) {
  const response = await fetch('/otp/generate', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      purpose: purpose,
      delivery_method: deliveryMethod
    })
  });
  
  const data = await response.json();
  if (data.success) {
    return data.data.otp_id;
  }
  throw new Error(data.message);
}

// Verify OTP
async function verifyOTP(otpCode, purpose) {
  const response = await fetch('/otp/verify', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      otp_code: otpCode,
      purpose: purpose
    })
  });
  
  const data = await response.json();
  return data.success;
}
```

### Backend Integration (PHP)

```php
use App\Services\OtpService;

class WithdrawalController extends Controller
{
    public function store(Request $request, OtpService $otpService)
    {
        // Verify OTP before processing withdrawal
        $isValid = $otpService->verify(
            $request->user(),
            $request->otp_code,
            'withdrawal'
        );
        
        if (!$isValid) {
            return back()->withErrors(['otp_code' => 'Invalid OTP code']);
        }
        
        // Process withdrawal...
    }
}
```

## Validation Rules

### Generate OTP
- `purpose`: Required, must be one of: withdrawal, account_change, password_reset, investment
- `delivery_method`: Required, must be one of: sms, email

### Verify OTP
- `otp_code`: Required, string, exactly 6 digits
- `purpose`: Required, must match the purpose used during generation

### Resend OTP
- `otp_id`: Required, must be valid active OTP ID
- `delivery_method`: Optional, must be one of: sms, email

## Error Responses

### Rate Limit Exceeded
```json
{
  "success": false,
  "message": "Rate limit exceeded. Please wait before requesting another OTP.",
  "data": {
    "retry_after": 300,
    "requests_remaining": 0
  },
  "code": "RATE_LIMIT_EXCEEDED"
}
```

### OTP Expired
```json
{
  "success": false,
  "message": "OTP has expired. Please generate a new one.",
  "data": {
    "expired_at": "2025-08-07T10:35:00Z"
  },
  "code": "OTP_EXPIRED"
}
```

### Maximum Attempts Exceeded
```json
{
  "success": false,
  "message": "Maximum verification attempts exceeded. OTP has been locked.",
  "data": {
    "locked_until": "2025-08-07T11:00:00Z",
    "attempts_made": 3
  },
  "code": "MAX_ATTEMPTS_EXCEEDED"
}
```

### Delivery Failed
```json
{
  "success": false,
  "message": "Failed to deliver OTP. Please try again or use alternative delivery method.",
  "data": {
    "delivery_method": "sms",
    "error_code": "DELIVERY_FAILED"
  },
  "code": "DELIVERY_FAILED"
}
```

### Invalid Purpose
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "purpose": ["The selected purpose is invalid."]
  }
}
```

### User Not Eligible
```json
{
  "success": false,
  "message": "User not eligible for OTP generation",
  "data": {
    "reason": "Phone number not verified"
  },
  "code": "USER_NOT_ELIGIBLE"
}
```