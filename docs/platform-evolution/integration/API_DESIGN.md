# Platform API Design

## Layers

| Layer | Audience | Auth | Rate Limit | Base Path |
|---|---|---|---|---|
| Platform API | Service-to-service within monolith | Internal token | None | `/api/platform/` |
| Public API | Mobile apps, first-party clients | Sanctum token | 60/min | `/api/v1/` |
| Partner API | Third-party integrations | API key + OAuth2 | 10/min | `/api/v1/` |
| Admin API | Internal tools, dashboards | Session + role check | None | `/admin/api/` |

## Versioning

URL-prefix: `/api/v1/`, `/api/v2/`. Bump only on breaking changes. Maintain one version back for 6 months.

## Authentication

- **Public/Partner**: Laravel Sanctum tokens. `Authorization: Bearer {token}` header.
- **Platform (internal)**: Pre-shared key in `config/platform.php`, passed via `X-Platform-Token` header.
- **Admin**: Same session as web.

## Error Format

All errors return a consistent envelope:

```json
{
    "error": {
        "code": "RESOURCE_NOT_FOUND",
        "message": "The requested organization was not found.",
        "details": {
            "organization_id": ["The specified organization does not exist."]
        }
    }
}
```

HTTP status codes: 400 (validation), 401 (unauthenticated), 403 (unauthorized), 404 (not found), 422 (unprocessable), 429 (rate limited), 500 (server error).

## API Contracts

### Organizations

| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/organizations` | List user's organizations |
| POST | `/api/v1/organizations` | Create organization |
| GET | `/api/v1/organizations/{org}` | Get organization details |
| PUT | `/api/v1/organizations/{org}` | Update organization |
| DELETE | `/api/v1/organizations/{org}` | Archive organization |

### Applications

| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/applications` | List available applications |
| GET | `/api/v1/applications/{slug}` | Get application details |

### Members

| Method | Path | Description |
|---|---|---|
| GET | `/api/v1/organizations/{org}/members` | List members |
| POST | `/api/v1/organizations/{org}/members` | Add member |
| DELETE | `/api/v1/organizations/{org}/members/{user}` | Remove member |

## Rate Limiting

Tiered via `RateLimiter` facade. Response headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`.
