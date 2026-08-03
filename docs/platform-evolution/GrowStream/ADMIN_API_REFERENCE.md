# GrowStream Admin API Reference

**Last Updated:** March 11, 2026  
**Base URL:** `/api/v1/growstream/admin`  
**Authentication:** Required (`auth:sanctum` + `role:admin`)

---

## Video Management

### List Videos
```
GET /admin/videos
```
**Query Parameters:**
- `status` - Filter by upload status
- `is_published` - Filter by published status (boolean)
- `search` - Search in title and description
- `sort_by` - Sort field (default: created_at)
- `sort_order` - Sort direction (default: desc)
- `per_page` - Items per page (default: 20)

### Upload Video
```
POST /admin/videos/upload
```
**Body:**
- `video` (file, required) - Video file (mp4, mov, avi, mkv, webm)
- `title` (string, required)
- `description` (string, required)
- `content_type` (string, required) - movie|series|episode|lesson|short|workshop|webinar
- `access_level` (string, required) - free|basic|premium|institutional
- `long_description` (string, optional)
- `language` (string, optional, default: en)
- `content_rating` (string, optional, default: NR)
- `skill_level` (string, optional)

### Get Form Data
```
GET /admin/videos/form-data
```
Returns categories, series, content types, access levels, ratings, and skill levels.

### Get Video Details
```
GET /admin/videos/{video}
```

### Update Video
```
PUT /admin/videos/{video}
```
**Body:** Same as upload (all fields optional)
- `category_ids` (array) - Category IDs to attach
- `tags` (array) - Tag names to attach

### Publish Video
```
POST /admin/videos/{video}/publish
```

### Unpublish Video
```
POST /admin/videos/{video}/unpublish
```

### Delete Video
```
DELETE /admin/videos/{video}
```

### Bulk Actions
```
POST /admin/videos/bulk-action
```
**Body:**
- `action` (string, required) - publish|unpublish|delete|feature|unfeature
- `video_ids` (array, required) - Array of video IDs

---

## Series Management

### List Series
```
GET /admin/series
```
**Query Parameters:**
- `is_published` - Filter by published status
- `search` - Search in title and description
- `sort_by` - Sort field (default: created_at)
- `sort_order` - Sort direction (default: desc)
- `per_page` - Items per page (default: 20)

### Create Series
```
POST /admin/series
```
**Body:**
- `title` (string, required)
- `description` (string, required)
- `content_type` (string, required) - series|course|workshop_series
- `access_level` (string, required) - free|basic|premium|institutional
- `long_description` (string, optional)
- `total_seasons` (integer, optional, default: 1)
- `poster_url` (string, optional)
- `trailer_url` (string, optional)
- `release_year` (integer, optional)
- `language` (string, optional, default: en)
- `content_rating` (string, optional, default: NR)
- `category_ids` (array, optional)

### Get Series Details
```
GET /admin/series/{series}
```
Returns series with all episodes ordered by season and episode number.

### Update Series
```
PUT /admin/series/{series}
```
**Body:** Same as create (all fields optional)

### Publish Series
```
POST /admin/series/{series}/publish
```

### Unpublish Series
```
POST /admin/series/{series}/unpublish
```

### Delete Series
```
DELETE /admin/series/{series}
```
Note: Cannot delete series with videos attached.

### Reorder Episodes
```
POST /admin/series/{series}/reorder-episodes
```
**Body:**
```json
{
  "episodes": [
    {
      "video_id": 1,
      "season_number": 1,
      "episode_number": 1
    },
    {
      "video_id": 2,
      "season_number": 1,
      "episode_number": 2
    }
  ]
}
```

---

## Analytics

### Platform Overview
```
GET /admin/analytics/overview?period=30
```
**Query Parameters:**
- `period` (integer, optional, default: 30) - Days to analyze

**Returns:**
- Total videos, published videos, new videos
- Total views, views this period
- Total watch time, watch time this period
- Unique viewers, unique viewers this period
- Completion rate, average watch duration
- Top categories
- Daily views trend

### Video Analytics
```
GET /admin/analytics/videos?period=30
```
**Query Parameters:**
- `period` (integer, optional, default: 30)
- `sort_by` (string, optional, default: views_count)
- `sort_order` (string, optional, default: desc)
- `per_page` (integer, optional, default: 20)

Returns paginated list of videos with view counts and watch time.

### Video Details Analytics
```
GET /admin/analytics/videos/{video}?period=30
```
**Query Parameters:**
- `period` (integer, optional, default: 30)

**Returns:**
- Video details
- Stats (views, unique viewers, watch time, completion rate)
- Daily views trend
- Watch duration distribution
- Device breakdown
- Top countries

### Creator Analytics
```
GET /admin/analytics/creators?period=30
```
**Query Parameters:**
- `period` (integer, optional, default: 30)

Returns all creators with their stats (videos, views, watch time, revenue).

### Engagement Analytics
```
GET /admin/analytics/engagement?period=30
```
**Query Parameters:**
- `period` (integer, optional, default: 30)

**Returns:**
- Active users
- Average session duration
- Completion rate
- Returning viewers
- Peak viewing hours
- Content type statistics

### Revenue Analytics
```
GET /admin/analytics/revenue?period=30
```
**Query Parameters:**
- `period` (integer, optional, default: 30)

Note: Placeholder endpoint for future payment integration.

---

## Creator Management

### List Creators
```
GET /admin/creators
```
**Query Parameters:**
- `is_verified` - Filter by verification status
- `is_active` - Filter by active status
- `search` - Search in name and email
- `sort_by` - Sort field (default: created_at)
- `sort_order` - Sort direction (default: desc)
- `per_page` - Items per page (default: 20)

### Get Creator Details
```
GET /admin/creators/{creator}
```
**Returns:**
- Creator profile
- Detailed stats (videos, views, watch time, revenue)
- Recent views (last 30 days)
- Top performing videos

### Verify Creator
```
POST /admin/creators/{creator}/verify
```

### Unverify Creator
```
POST /admin/creators/{creator}/unverify
```

### Suspend Creator
```
POST /admin/creators/{creator}/suspend
```
**Body:**
- `reason` (string, required) - Suspension reason
- `unpublish_videos` (boolean, optional) - Unpublish all creator's videos

### Unsuspend Creator
```
POST /admin/creators/{creator}/unsuspend
```

### Update Creator Limits
```
PUT /admin/creators/{creator}/limits
```
**Body:**
- `max_videos` (integer, optional)
- `max_storage_gb` (integer, optional)
- `max_upload_size_mb` (integer, optional)
- `revenue_share_percentage` (numeric, optional, 0-100)

---

## Response Format

All endpoints return JSON responses in this format:

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional success message"
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "total": 100,
    "last_page": 5
  }
}
```

### Error Response
```json
{
  "success": false,
  "error": "Error message"
}
```

---

## Authentication

All admin endpoints require:
1. Valid Sanctum token (`Authorization: Bearer {token}`)
2. User with `admin` role

Example request:
```bash
curl -X GET \
  https://api.mygrownet.com/api/v1/growstream/admin/videos \
  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \
  -H 'Accept: application/json'
```

---

## Common HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created successfully
- `400 Bad Request` - Validation error or invalid request
- `401 Unauthorized` - Missing or invalid authentication
- `403 Forbidden` - User doesn't have admin role
- `404 Not Found` - Resource not found
- `500 Internal Server Error` - Server error

---

## Next Steps

1. Test all endpoints with Postman or similar tool
2. Implement frontend admin panel UI
3. Add background jobs for video processing
4. Implement thumbnail generation
5. Add search functionality enhancement
