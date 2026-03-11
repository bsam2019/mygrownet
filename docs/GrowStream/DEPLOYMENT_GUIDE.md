# GrowStream Deployment Guide

**Last Updated:** March 11, 2026  
**Status:** Production Ready  
**Version:** 1.0

---

## Prerequisites

- PHP 8.2+
- Laravel 12.0
- MySQL/PostgreSQL
- Redis (for queues)
- DigitalOcean Spaces account (or Cloudflare Stream)
- Composer
- Node.js & npm

---

## Environment Configuration

### 1. DigitalOcean Spaces Setup

Add to `.env`:

```bash
# DigitalOcean Spaces
DO_SPACES_KEY=your_spaces_key
DO_SPACES_SECRET=your_spaces_secret
DO_SPACES_REGION=nyc3
DO_SPACES_BUCKET=your_bucket_name
DO_SPACES_ENDPOINT=https://nyc3.digitaloceanspaces.com
DO_SPACES_CDN_ENDPOINT=https://your-bucket.nyc3.cdn.digitaloceanspaces.com

# GrowStream Settings
GROWSTREAM_VIDEO_PROVIDER=digitalocean
```

### 2. Queue Configuration

Add to `.env`:

```bash
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 3. File Upload Limits

Update `php.ini`:

```ini
upload_max_filesize = 2048M
post_max_size = 2048M
max_execution_time = 600
memory_limit = 512M
```

---

## Installation Steps

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Run Migrations

```bash
php artisan migrate
```

### 3. Seed Initial Data

```bash
php artisan db:seed --class=GrowStreamSeeder
```

This will create:
- 8 main categories with 32 subcategories
- 20 common tags

### 4. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=growstream-config
```

### 5. Configure Filesystem

The DigitalOcean Spaces disk is automatically configured. Verify in `config/filesystems.php`:

```php
'digitalocean' => [
    'driver' => 's3',
    'key' => env('DO_SPACES_KEY'),
    'secret' => env('DO_SPACES_SECRET'),
    'region' => env('DO_SPACES_REGION', 'nyc3'),
    'bucket' => env('DO_SPACES_BUCKET'),
    'endpoint' => env('DO_SPACES_ENDPOINT'),
    'url' => env('DO_SPACES_CDN_ENDPOINT'),
    'use_path_style_endpoint' => false,
],
```

---

## Queue Workers

### Start Queue Workers

You need to run queue workers for different priorities:

```bash
# High priority (video processing)
php artisan queue:work --queue=high --tries=3 --timeout=600

# Default priority (thumbnails, general tasks)
php artisan queue:work --queue=default --tries=2 --timeout=300

# Low priority (analytics)
php artisan queue:work --queue=low --tries=2 --timeout=120
```

### Using Supervisor (Recommended)

Create `/etc/supervisor/conf.d/growstream-worker.conf`:

```ini
[program:growstream-high]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=high --tries=3 --timeout=600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-high.log

[program:growstream-default]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=default --tries=2 --timeout=300
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-default.log

[program:growstream-low]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --queue=low --tries=2 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/worker-low.log
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start growstream-high:*
sudo supervisorctl start growstream-default:*
sudo supervisorctl start growstream-low:*
```

---

## Scheduled Tasks

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Aggregate analytics daily at 2 AM
    $schedule->command('growstream:aggregate-analytics')
        ->dailyAt('02:00')
        ->withoutOverlapping();

    // Process pending videos every 30 minutes
    $schedule->command('growstream:process-pending-videos')
        ->everyThirtyMinutes()
        ->withoutOverlapping();

    // Cleanup old analytics monthly
    $schedule->command('growstream:cleanup-analytics --days=90')
        ->monthly()
        ->at('03:00');
}
```

Ensure cron is running:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## Console Commands

### View Platform Statistics

```bash
php artisan growstream:stats
```

### Manually Aggregate Analytics

```bash
# For yesterday
php artisan growstream:aggregate-analytics

# For specific date
php artisan growstream:aggregate-analytics --date=2026-03-10
```

### Process Stuck Videos

```bash
php artisan growstream:process-pending-videos
```

### Cleanup Old Data

```bash
# Keep 90 days (default)
php artisan growstream:cleanup-analytics

# Keep 30 days
php artisan growstream:cleanup-analytics --days=30
```

---

## API Endpoints

### Public Endpoints

Base URL: `/api/v1/growstream`

- `GET /videos` - List videos
- `GET /videos/featured` - Featured videos
- `GET /videos/trending` - Trending videos
- `GET /videos/{slug}` - Video details
- `GET /series` - List series
- `GET /series/{slug}` - Series details
- `GET /categories` - List categories
- `GET /categories/{slug}/videos` - Videos by category

### Authenticated Endpoints

Requires: `auth:sanctum`

- `POST /watch/authorize` - Get signed playback URL
- `POST /watch/progress` - Update watch progress
- `GET /watch/history` - Watch history
- `GET /continue-watching` - Continue watching list
- `GET /watchlist` - User's watchlist
- `POST /watchlist` - Add to watchlist
- `DELETE /watchlist/{id}` - Remove from watchlist

### Admin Endpoints

Requires: `auth:sanctum` + `role:admin`

Base URL: `/api/v1/growstream/admin`

See `ADMIN_API_REFERENCE.md` for complete documentation.

---

## Testing

### Test Video Upload

```bash
curl -X POST http://localhost/api/v1/growstream/admin/videos/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "video=@/path/to/video.mp4" \
  -F "title=Test Video" \
  -F "description=Test Description" \
  -F "content_type=lesson" \
  -F "access_level=free"
```

### Test Video Listing

```bash
curl http://localhost/api/v1/growstream/videos
```

### Test Analytics

```bash
curl http://localhost/api/v1/growstream/admin/analytics/overview \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Monitoring

### Queue Monitoring

Check queue status:

```bash
php artisan queue:monitor high,default,low
```

### Failed Jobs

View failed jobs:

```bash
php artisan queue:failed
```

Retry failed jobs:

```bash
php artisan queue:retry all
```

### Logs

Monitor logs:

```bash
tail -f storage/logs/laravel.log
```

---

## Performance Optimization

### 1. Enable Caching

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Optimize Composer

```bash
composer install --optimize-autoloader --no-dev
```

### 3. Enable OPcache

Add to `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### 4. Database Indexing

Ensure all migrations have run - indexes are already defined.

### 5. CDN Configuration

Use DigitalOcean Spaces CDN endpoint for video delivery:

```bash
DO_SPACES_CDN_ENDPOINT=https://your-bucket.nyc3.cdn.digitaloceanspaces.com
```

---

## Security

### 1. Rate Limiting

Configure in `config/growstream.php`:

```php
'rate_limiting' => [
    'enabled' => true,
    'max_requests' => 30,
    'per_minutes' => 1,
],
```

### 2. Signed URLs

Playback URLs are automatically signed with 24-hour expiration.

### 3. CORS Configuration

Update `config/cors.php` if needed for frontend access.

### 4. Authentication

Ensure Sanctum is properly configured for API authentication.

---

## Troubleshooting

### Videos Stuck in Processing

```bash
php artisan growstream:process-pending-videos
```

### Queue Not Processing

Check queue workers:

```bash
sudo supervisorctl status
```

Restart workers:

```bash
sudo supervisorctl restart growstream-high:*
```

### Upload Failures

Check:
1. PHP upload limits
2. DigitalOcean Spaces credentials
3. Disk space
4. Logs: `storage/logs/laravel.log`

### Analytics Not Updating

Run manually:

```bash
php artisan growstream:aggregate-analytics
```

Check scheduled tasks:

```bash
php artisan schedule:list
```

---

## Backup Strategy

### 1. Database Backups

```bash
# Daily backup
mysqldump -u user -p database > backup-$(date +%Y%m%d).sql
```

### 2. DigitalOcean Spaces

Enable versioning in Spaces settings for automatic backups.

### 3. Configuration

Backup `.env` and `config/` directory.

---

## Scaling Considerations

### Horizontal Scaling

- Run multiple queue workers across servers
- Use Redis Sentinel for queue reliability
- Load balance API servers

### Database Optimization

- Read replicas for analytics queries
- Partition large tables (views, watch_history)
- Archive old data regularly

### CDN

- Use DigitalOcean Spaces CDN
- Consider Cloudflare for additional caching

---

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Run diagnostics: `php artisan growstream:stats`
- Review documentation in `docs/GrowStream/`

---

**Next Steps:**
1. Complete frontend implementation
2. Add comprehensive tests
3. Performance testing
4. Security audit
