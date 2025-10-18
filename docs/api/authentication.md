# Authentication API

The Authentication API handles user registration, login, password management, and session control for the VBIF system.

## Endpoints

### User Registration

```http
POST /register
```

Registers a new user account.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123!",
  "password_confirmation": "SecurePassword123!",
  "phone": "+1234567890",
  "referrer_code": "REF123456",
  "terms_accepted": true,
  "privacy_accepted": true
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "referral_code": "REF789012",
      "referred_by": {
        "id": 456,
        "name": "Jane Smith",
        "referral_code": "REF123456"
      },
      "email_verified_at": null,
      "phone_verified_at": null,
      "created_at": "2025-08-07T10:30:00Z"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2025-08-14T10:30:00Z"
  },
  "message": "Registration successful. Please verify your email address."
}
```

### User Login

```http
POST /login
```

Authenticates a user and returns access token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "SecurePassword123!",
  "remember": true,
  "device_name": "iPhone 12"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "referral_code": "REF789012",
      "email_verified_at": "2025-08-07T11:00:00Z",
      "phone_verified_at": "2025-08-07T11:15:00Z",
      "current_investment_tier": "Starter",
      "total_investment_amount": 1000,
      "total_earnings": 150,
      "roles": ["investor"]
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "expires_at": "2025-08-14T10:30:00Z",
    "permissions": [
      "view_dashboard",
      "create_investment",
      "view_referrals",
      "request_withdrawal"
    ]
  },
  "message": "Login successful"
}
```

### User Logout

```http
POST /logout
```

Logs out the authenticated user and invalidates the token.

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Get Authenticated User

```http
GET /user
```

Returns the currently authenticated user's information.

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "referral_code": "REF789012",
      "email_verified_at": "2025-08-07T11:00:00Z",
      "phone_verified_at": "2025-08-07T11:15:00Z",
      "current_investment_tier": "Starter",
      "total_investment_amount": 1000,
      "total_earnings": 150,
      "profile": {
        "date_of_birth": "1990-01-15",
        "address": "123 Main St, City, Country",
        "occupation": "Software Engineer",
        "id_verification_status": "verified"
      },
      "preferences": {
        "notification_email": true,
        "notification_sms": true,
        "marketing_emails": false
      },
      "security": {
        "two_factor_enabled": false,
        "last_login_at": "2025-08-07T10:30:00Z",
        "last_login_ip": "192.168.1.100"
      },
      "roles": ["investor"],
      "permissions": [
        "view_dashboard",
        "create_investment",
        "view_referrals",
        "request_withdrawal"
      ]
    }
  }
}
```

### Password Reset Request

```http
POST /forgot-password
```

Initiates password reset process.

**Request Body:**
```json
{
  "email": "john@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset link sent to your email address"
}
```

### Reset Password

```http
POST /reset-password
```

Resets user password using reset token.

**Request Body:**
```json
{
  "token": "reset_token_here",
  "email": "john@example.com",
  "password": "NewSecurePassword123!",
  "password_confirmation": "NewSecurePassword123!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

### Change Password

```http
POST /change-password
```

Changes password for authenticated user.

**Request Body:**
```json
{
  "current_password": "OldPassword123!",
  "password": "NewSecurePassword123!",
  "password_confirmation": "NewSecurePassword123!",
  "otp_code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

### Email Verification

```http
POST /email/verification-notification
```

Resends email verification notification.

**Response:**
```json
{
  "success": true,
  "message": "Verification email sent"
}
```

### Verify Email

```http
GET /verify-email/{id}/{hash}
```

Verifies user email address.

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "email_verified_at": "2025-08-07T11:00:00Z"
    }
  },
  "message": "Email verified successfully"
}
```

### Phone Verification Request

```http
POST /phone/verification-request
```

Requests phone number verification.

**Request Body:**
```json
{
  "phone": "+1234567890"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "verification_id": "phone_verify_123",
    "phone": "****7890",
    "expires_at": "2025-08-07T10:35:00Z"
  },
  "message": "Verification code sent to your phone"
}
```

### Verify Phone

```http
POST /phone/verify
```

Verifies phone number with OTP.

**Request Body:**
```json
{
  "verification_id": "phone_verify_123",
  "code": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "phone_verified_at": "2025-08-07T11:15:00Z"
    }
  },
  "message": "Phone number verified successfully"
}
```

### Update Profile

```http
PUT /profile
```

Updates user profile information.

**Request Body:**
```json
{
  "name": "John Smith",
  "phone": "+1234567891",
  "date_of_birth": "1990-01-15",
  "address": "456 New St, City, Country",
  "occupation": "Senior Software Engineer"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 123,
      "name": "John Smith",
      "phone": "+1234567891",
      "profile": {
        "date_of_birth": "1990-01-15",
        "address": "456 New St, City, Country",
        "occupation": "Senior Software Engineer"
      },
      "updated_at": "2025-08-07T12:00:00Z"
    }
  },
  "message": "Profile updated successfully"
}
```

### Update Preferences

```http
PUT /preferences
```

Updates user notification and privacy preferences.

**Request Body:**
```json
{
  "notification_email": true,
  "notification_sms": false,
  "marketing_emails": false,
  "privacy_level": "standard"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "preferences": {
      "notification_email": true,
      "notification_sms": false,
      "marketing_emails": false,
      "privacy_level": "standard",
      "updated_at": "2025-08-07T12:00:00Z"
    }
  },
  "message": "Preferences updated successfully"
}
```

### Get Active Sessions

```http
GET /sessions
```

Returns list of active user sessions.

**Response:**
```json
{
  "success": true,
  "data": {
    "sessions": [
      {
        "id": "session_123",
        "device_name": "iPhone 12",
        "ip_address": "192.168.1.100",
        "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)",
        "location": "New York, US",
        "last_activity": "2025-08-07T12:00:00Z",
        "is_current": true
      },
      {
        "id": "session_456",
        "device_name": "Chrome Browser",
        "ip_address": "192.168.1.101",
        "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
        "location": "New York, US",
        "last_activity": "2025-08-06T18:30:00Z",
        "is_current": false
      }
    ],
    "total_sessions": 2
  }
}
```

### Revoke Session

```http
DELETE /sessions/{session_id}
```

Revokes a specific user session.

**Response:**
```json
{
  "success": true,
  "message": "Session revoked successfully"
}
```

### Revoke All Sessions

```http
DELETE /sessions
```

Revokes all user sessions except current.

**Response:**
```json
{
  "success": true,
  "data": {
    "revoked_sessions": 3
  },
  "message": "All other sessions revoked successfully"
}
```

## Security Features

### Password Requirements
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character
- Cannot be common passwords
- Cannot be similar to user information

### Account Security
- Failed login attempt tracking
- Account lockout after 5 failed attempts
- IP-based rate limiting
- Device fingerprinting
- Suspicious activity detection

### Session Management
- JWT token-based authentication
- Configurable token expiration
- Automatic token refresh
- Session tracking and management
- Device-based session control

### Two-Factor Authentication (Future)
- SMS-based 2FA
- Email-based 2FA
- Authenticator app support
- Backup codes

## Validation Rules

### Registration
- `name`: Required, string, 2-50 characters
- `email`: Required, valid email, unique
- `password`: Required, minimum 8 characters, complexity requirements
- `phone`: Required, valid phone number format
- `referrer_code`: Optional, must exist if provided
- `terms_accepted`: Required, must be true
- `privacy_accepted`: Required, must be true

### Login
- `email`: Required, valid email format
- `password`: Required
- `remember`: Optional, boolean
- `device_name`: Optional, string, max 100 characters

### Password Change
- `current_password`: Required, must match current password
- `password`: Required, complexity requirements, different from current
- `password_confirmation`: Required, must match password
- `otp_code`: Required, valid OTP

### Profile Update
- `name`: Optional, string, 2-50 characters
- `phone`: Optional, valid phone number format
- `date_of_birth`: Optional, valid date, user must be 18+
- `address`: Optional, string, max 200 characters
- `occupation`: Optional, string, max 100 characters

## Error Responses

### Invalid Credentials
```json
{
  "success": false,
  "message": "Invalid credentials",
  "code": "INVALID_CREDENTIALS"
}
```

### Account Locked
```json
{
  "success": false,
  "message": "Account temporarily locked due to multiple failed login attempts",
  "data": {
    "locked_until": "2025-08-07T11:30:00Z",
    "attempts_remaining": 0
  },
  "code": "ACCOUNT_LOCKED"
}
```

### Email Not Verified
```json
{
  "success": false,
  "message": "Please verify your email address before proceeding",
  "data": {
    "verification_required": true,
    "email": "john@example.com"
  },
  "code": "EMAIL_NOT_VERIFIED"
}
```

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  }
}
```

### Token Expired
```json
{
  "success": false,
  "message": "Token has expired",
  "code": "TOKEN_EXPIRED"
}
```

### Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated",
  "code": "UNAUTHENTICATED"
}
```