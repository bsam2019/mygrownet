# GrowStream User Access Guide

**Last Updated:** March 11, 2026  
**Status:** Production Ready

---

## How to Access GrowStream

### For Regular Users

#### 1. Browse Videos
Access the video library at:
```
https://yourdomain.com/growstream/browse
```

Features:
- Filter by category, content type, access level
- Search videos by title/description
- Sort by latest, popular, or trending
- View video cards with thumbnails and metadata

#### 2. Watch Videos
Click any video to view details at:
```
https://yourdomain.com/growstream/video/{slug}
```

Features:
- Full-featured video player with controls
- Progress tracking (auto-saves every 5 seconds)
- Related videos suggestions
- Add to watchlist
- View creator information

#### 3. My Videos Dashboard
Access your personal video dashboard at:
```
https://yourdomain.com/growstream/my-videos
```

Features:
- **Continue Watching**: Resume videos where you left off
- **Watchlist**: Videos you've saved for later
- **Watch History**: All videos you've watched

#### 4. Series
Browse and watch video series at:
```
https://yourdomain.com/growstream/series/{slug}
```

Features:
- View all episodes grouped by season
- Track progress across episodes
- Binge-watch entire series

#### 5. Home Page
Discover featured content at:
```
https://yourdomain.com/growstream
```

Features:
- Featured videos carousel
- Trending videos
- Recently added content
- Personalized recommendations (coming soon)

---

### For Administrators

#### Access Admin Panel
Navigate to the admin sidebar and expand **GrowStream** section:

```
Admin Sidebar → GrowStream
```

Or directly access:
- Videos: `/growstream/admin/videos`
- Analytics: `/growstream/admin/analytics`
- Creators: `/growstream/admin/creators`

#### Admin Features

**1. Video Management** (`/growstream/admin/videos`)
- Upload new videos
- Edit video metadata
- Publish/unpublish videos
- Bulk actions (publish, unpublish, delete, feature)
- Filter by status, published state
- Search videos

**2. Analytics Dashboard** (`/growstream/admin/analytics`)
- Platform overview (total videos, views, watch time)
- Top performing videos
- Creator statistics
- Engagement metrics
- Revenue analytics (placeholder)

**3. Creator Management** (`/growstream/admin/creators`)
- View all content creators
- Verify/unverify creators
- Suspend/unsuspend accounts
- Set upload limits
- Configure revenue share percentages
- Track creator performance

---

## Navigation Integration

### Admin Sidebar
GrowStream appears in the admin sidebar between **GrowBackup** and **Marketplace**:

```
Admin Sidebar
├── ...
├── GrowBackup
├── GrowStream ← NEW
│   ├── Videos
│   ├── Analytics
│   └── Creators
├── Marketplace
└── ...
```

### User Navigation
Add GrowStream links to your main navigation:

**Option 1: Add to AppHeader** (`resources/js/components/AppHeader.vue`)
```typescript
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'GrowStream',
        href: '/growstream',
        icon: VideoIcon,
    },
];
```

**Option 2: Add to App Launcher**
Include GrowStream in your app launcher grid for easy access.

**Option 3: Direct Links**
Add links in your dashboard or homepage:
```vue
<Link href="/growstream" class="...">
    <VideoIcon class="h-6 w-6" />
    <span>Watch Videos</span>
</Link>
```

---

## API Access

### Public API Endpoints
```
GET  /api/v1/growstream/videos              - List videos
GET  /api/v1/growstream/videos/featured     - Featured videos
GET  /api/v1/growstream/videos/trending     - Trending videos
GET  /api/v1/growstream/videos/{slug}       - Video details
GET  /api/v1/growstream/series              - List series
GET  /api/v1/growstream/series/{slug}       - Series details
GET  /api/v1/growstream/categories          - All categories
POST /api/v1/growstream/watch/authorize     - Get playback URL
POST /api/v1/growstream/watch/progress      - Update progress
GET  /api/v1/growstream/continue-watching   - Continue watching
GET  /api/v1/growstream/watchlist           - User watchlist
```

### Admin API Endpoints
```
GET    /api/v1/growstream/admin/videos              - List all videos
POST   /api/v1/growstream/admin/videos/upload       - Upload video
PUT    /api/v1/growstream/admin/videos/{id}         - Update video
DELETE /api/v1/growstream/admin/videos/{id}         - Delete video
POST   /api/v1/growstream/admin/videos/bulk-action  - Bulk actions
GET    /api/v1/growstream/admin/analytics/overview  - Analytics
GET    /api/v1/growstream/admin/creators            - List creators
POST   /api/v1/growstream/admin/creators/{id}/verify - Verify creator
```

---

## Quick Start for Users

1. **Browse Videos**: Go to `/growstream/browse`
2. **Click a Video**: Watch and enjoy
3. **Save for Later**: Click "Add to Watchlist"
4. **Resume Watching**: Go to `/growstream/my-videos`

## Quick Start for Admins

1. **Upload Video**: Admin → GrowStream → Videos → Upload Video
2. **Fill Details**: Title, description, content type, access level
3. **Publish**: Click publish when processing is complete
4. **Monitor**: Check analytics dashboard for performance

---

## Mobile Access

All pages are fully responsive and work on:
- Desktop browsers
- Tablets
- Mobile phones
- Progressive Web App (PWA) ready

---

## Permissions

### User Roles
- **Guest**: Can browse free videos only
- **Basic Member**: Access to basic tier videos
- **Premium Member**: Access to premium videos
- **Institutional**: Access to institutional content

### Admin Roles
- **Admin**: Full access to all GrowStream features
- **Content Manager**: Can manage videos and creators
- **Analyst**: Read-only access to analytics

---

## Troubleshooting

### Can't See GrowStream in Navigation
1. Check if you're logged in
2. Verify your user role has access
3. Clear browser cache
4. Check if routes are registered

### Videos Not Playing
1. Check video upload status (should be "ready")
2. Verify video is published
3. Check your access level matches video requirements
4. Ensure DigitalOcean Spaces is configured

### Admin Panel Not Accessible
1. Verify you have admin role
2. Check route permissions
3. Ensure you're accessing via `/growstream/admin/*`

### Database Errors

#### "Column 'featured_at' not found"
This was fixed by adding the missing `featured_at` column. If you encounter this:

1. Run the migration:
```bash
php artisan migrate
```

2. The migration adds:
   - `featured_at` timestamp column
   - Index on `featured_at`
   - Logic to set timestamp when videos are featured

#### Other Column Issues
If you encounter missing column errors:
1. Ensure all GrowStream migrations have run: `php artisan migrate`
2. Check migration status: `php artisan migrate:status`
3. If needed, refresh migrations: `php artisan migrate:refresh --seed`

### Empty Data on Pages
If pages load but show no content:
1. Run the seeder: `php artisan db:seed --class=GrowStreamSeeder`
2. Upload some test videos via admin panel
3. Publish videos to make them visible
4. Check database has data: `php artisan growstream:stats`

---

## Next Steps

1. **Customize Navigation**: Add GrowStream links to your main navigation
2. **Configure Access**: Set up user roles and permissions
3. **Upload Content**: Start adding videos via admin panel
4. **Promote**: Share GrowStream links with your users
5. **Monitor**: Track usage via analytics dashboard

---

## Support

For technical issues or questions:
- Check `docs/GrowStream/DEPLOYMENT_GUIDE.md` for setup
- Review `docs/GrowStream/ADMIN_API_REFERENCE.md` for API details
- See `docs/GrowStream/IMPLEMENTATION_STATUS.md` for features

