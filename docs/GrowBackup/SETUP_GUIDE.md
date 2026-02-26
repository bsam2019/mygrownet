# MyGrowNet Storage - Complete Setup Guide

**Last Updated:** February 22, 2026
**Status:** Production Ready

## Overview

Complete setup guide for MyGrowNet Storage (GrowBackup). For quick 5-minute setup, see [QUICKSTART.md](QUICKSTART.md).

## Prerequisites

- Laravel 12, PHP 8.2+
- Database configured
- S3-compatible storage (DigitalOcean Spaces, Wasabi, or AWS S3)

## Setup Steps

### 1. Database (Already Complete)

Migrations and seeders already run:
- 5 tables created
- 4 storage plans seeded (Starter, Basic, Growth, Pro)

### 2. S3 Configuration

**DigitalOcean Spaces** (Development) - See [DIGITALOCEAN_SETUP.md](DIGITALOCEAN_SETUP.md)
```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=nyc3
AWS_BUCKET=mygrownet-storage
AWS_ENDPOINT=https://nyc3.digitaloceanspaces.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**Wasabi** (Production)
```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=mygrownet-storage
AWS_ENDPOINT=https://s3.wasabisys.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

**AWS S3**
```env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=mygrownet-storage
```

### 3. Provision Users

```bash
# Default (Starter plan)
php artisan storage:provision-user user@example.com

# Specific plan
php artisan storage:provision-user user@example.com --plan=basic
```

### 4. Access UI

Navigate to `/storage` after login.

## API Endpoints

```http
# Folders
GET    /api/storage/folders?parent_id={id}
POST   /api/storage/folders
PATCH  /api/storage/folders/{id}
POST   /api/storage/folders/{id}/move
DELETE /api/storage/folders/{id}

# Files
POST   /api/storage/files/upload-init
POST   /api/storage/files/upload-complete
GET    /api/storage/files/{id}/download
PATCH  /api/storage/files/{id}
POST   /api/storage/files/{id}/move
DELETE /api/storage/files/{id}

# Usage
GET    /api/storage/usage
```

## Troubleshooting

**S3 Connection Failed**
- Verify `.env` credentials
- Test: `php artisan tinker` → `Storage::disk('s3')->put('test.txt', 'test')`

**Quota Exceeded**
```bash
# Check plan
php artisan tinker
\App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription::where('user_id', 1)->with('storagePlan')->first();

# Upgrade
php artisan storage:provision-user user@email.com --plan=basic
```

**Usage Not Updating**
```php
// Reconcile manually
$userId = 1;
$totalSize = \App\Infrastructure\Storage\Persistence\Eloquent\StorageFile::forUser($userId)->notDeleted()->sum('size_bytes');
$filesCount = \App\Infrastructure\Storage\Persistence\Eloquent\StorageFile::forUser($userId)->notDeleted()->count();

\App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage::updateOrCreate(
    ['user_id' => $userId],
    ['used_bytes' => $totalSize, 'files_count' => $filesCount]
);
```

## Production Checklist

- [ ] Switch to Wasabi/production S3
- [ ] Configure CORS for bucket
- [ ] Set up monitoring
- [ ] Configure database backups
- [ ] Set up quota alerts
- [ ] Test from production domain
- [ ] Configure CDN (optional)
- [ ] Set up automated reconciliation

## Related Docs

- [QUICKSTART.md](QUICKSTART.md) - 5-minute setup
- [DIGITALOCEAN_SETUP.md](DIGITALOCEAN_SETUP.md) - DigitalOcean config
- [CONCEPT.md](CONCEPT.md) - Architecture
- [IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md) - Status

## Changelog

### 2026-02-22
- Consolidated documentation
- Added web route
- Simplified troubleshooting

### 2026-02-21
- Initial guide created
- Database setup complete
