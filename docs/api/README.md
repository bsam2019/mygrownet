# VBIF Reward System API Documentation

## Overview

The VBIF (Village Banking Investment Fund) Reward System API provides comprehensive endpoints for managing investments, referrals, matrix positions, withdrawals, and dashboard analytics. This documentation covers all available endpoints with detailed request/response examples and authentication requirements.

## Base URL

```
https://your-domain.com/api
```

## Authentication

All API endpoints require authentication using Laravel Sanctum tokens or session-based authentication for Inertia.js requests.

### Headers Required

```http
Authorization: Bearer {your-token}
Content-Type: application/json
Accept: application/json
```

## API Sections

- [Dashboard API](./dashboard.md) - Real-time dashboard data and metrics
- [Investment API](./investments.md) - Investment management and tier operations
- [Referral API](./referrals.md) - Referral system and matrix management
- [Withdrawal API](./withdrawals.md) - Withdrawal requests and policy management
- [OTP API](./otp.md) - OTP verification system
- [Authentication API](./authentication.md) - User authentication and authorization

## Response Format

All API responses follow a consistent format:

### Success Response
```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Optional success message"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["Validation error messages"]
  },
  "code": "ERROR_CODE"
}
```

## Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

## Rate Limiting

API endpoints are rate limited to prevent abuse:
- General endpoints: 60 requests per minute
- OTP endpoints: 5 requests per minute
- Authentication endpoints: 10 requests per minute

## Pagination

List endpoints support pagination with the following parameters:

```http
GET /api/endpoint?page=1&per_page=15
```

Response includes pagination metadata:

```json
{
  "data": [...],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 75
}
```

## Error Handling

The API uses standard HTTP status codes and provides detailed error messages. Common error scenarios:

### Validation Errors (422)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "amount": ["The amount field is required."],
    "tier_id": ["The selected tier id is invalid."]
  }
}
```

### Authentication Errors (401)
```json
{
  "success": false,
  "message": "Unauthenticated.",
  "code": "UNAUTHENTICATED"
}
```

### Authorization Errors (403)
```json
{
  "success": false,
  "message": "This action is unauthorized.",
  "code": "UNAUTHORIZED"
}
```

## Integration Examples

### JavaScript/Frontend Integration

```javascript
// Using fetch API
const response = await fetch('/dashboard/real-time-earnings', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

const data = await response.json();
if (data.success) {
  console.log('Earnings:', data.data.earnings);
}
```

### PHP Integration

```php
use Illuminate\Support\Facades\Http;

$response = Http::withToken($token)
    ->get('/dashboard/real-time-earnings');

if ($response->successful()) {
    $earnings = $response->json()['data']['earnings'];
}
```

## Testing

All endpoints include comprehensive test coverage. Run tests with:

```bash
./vendor/bin/pest tests/Feature/Api/
```

## Support

For API support and questions:
- Email: api-support@vbif.com
- Documentation Issues: Create an issue in the project repository
- Feature Requests: Contact the development team

## Changelog

### Version 1.0.0
- Initial API release
- Dashboard endpoints
- Investment management
- Referral system
- Withdrawal management
- OTP verification