# Storage Migration: DigitalOcean Spaces to Wasabi

**Last Updated:** June 12, 2026

## Overview

This guide provides a safe, step-by-step process to migrate your storage from DigitalOcean Spaces to Wasabi without downtime or data loss.

## Why Migrate to Wasabi?

- **Cost Savings**: Up to 80% cheaper than DigitalOcean Spaces
- **No Egress Fees**: Free data transfer out (Wasabi's key differentiator)
- **S3 Compatible**: Drop-in replacement, same API
- **Performance**: Comparable or better performance
- **Pricing Transparency**: Simple, predictable pricing

## Prerequisites

1. **Wasabi Account**: Sign up at https://wasabi.com
2. **Wasabi Bucket**: Create a new bucket in desired region
3. **Access Credentials**: Get Wasabi Access Key ID and Secret Key
4. **Backup**: Full backup of current DigitalOcean Spaces data
5. **Testing Environment**: Recommended to test migration first

## Migration Strategy Options

### Option 1: Dual-Write Migration (Recommended - Zero Downtime)

Write to both storage providers during transition, then switch reads to Wasabi.

**Pros:**
- Zero downtime
- Easy rollback
- Gradual transition
- Test in production safely

**Cons:**
- Temporary double storage costs
- More complex implementation

### Option 2: Big Bang Migration

Sync all data, then switch entirely to Wasabi.

**Pros:**
- Simpler implementation
- Faster transition
- No dual-write complexity

**Cons:**
- Requires maintenance window
- Higher risk
- Harder to rollback

### Option 3: Hybrid Approach (Best for Large Datasets)

Keep old files on DO Spaces, new files on Wasabi, migrate gradually.

**Pros:**
- Lowest risk
- No downtime
- Migrate at your own pace
- Lower immediate costs

**Cons:**
- Longer transition period
- Code needs to check both locations

---

## Step-by-Step Migration Guide

### Phase 1: Setup Wasabi

#### 1.1 Create Wasabi Bucket

```bash
# Choose region close to your users:
# us-east-1 (N. Virginia)
# us-east-2 (N. Virginia)
# us-west-1 (Oregon)
# eu-central-1 (Amsterdam)
# ap-northeast-1 (Tokyo)
```

#### 1.2 Configure Bucket Policies

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Sid": "PublicReadGetObject",
      "Effect": "Allow",
      "Principal": "*",
      "Action": "s3:GetObject",
      "Resource": "arn:aws:s3:::YOUR-BUCKET-NAME/*"
    }
  ]
}
```

#### 1.3 Enable CORS (if needed)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
  <CORSRule>
    <AllowedOrigin>*</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedMethod>POST</AllowedMethod>
    <AllowedMethod>PUT</AllowedMethod>
    <AllowedMethod>DELETE</AllowedMethod>
    <AllowedMethod>HEAD</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
    <MaxAgeSeconds>3000</MaxAgeSeconds>
  </CORSRule>
</CORSConfiguration>
```

### Phase 2: Configure Laravel

#### 2.1 Add Wasabi Disk Configuration

Update `config/filesystems.php`:

```php
'disks' => [
    // ... existing disks ...

    'wasabi' => [
        'driver' => 's3',
        'key' => env('WASABI_ACCESS_KEY_ID'),
        'secret' => env('WASABI_SECRET_ACCESS_KEY'),
        'region' => env('WASABI_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('WASABI_BUCKET'),
        'url' => env('WASABI_URL'),
        'endpoint' => env('WASABI_ENDPOINT', 'https://s3.us-east-1.wasabisys.com'),
        'use_path_style_endpoint' => false,
        'throw' => true,
        'report' => true,
    ],

    // Keep existing do_spaces configuration for now
    'do_spaces' => [
        // ... existing configuration ...
    ],
],
```

#### 2.2 Update Environment Variables

Add to `.env`:

```env
# Wasabi Configuration
WASABI_ACCESS_KEY_ID=your_wasabi_access_key
WASABI_SECRET_ACCESS_KEY=your_wasabi_secret_key
WASABI_DEFAULT_REGION=us-east-1
WASABI_BUCKET=your-bucket-name
WASABI_URL=https://your-bucket-name.s3.us-east-1.wasabisys.com
WASABI_ENDPOINT=https://s3.us-east-1.wasabisys.com

# Wasabi Region Endpoints:
# us-east-1: https://s3.us-east-1.wasabisys.com
# us-east-2: https://s3.us-east-2.wasabisys.com
# us-west-1: https://s3.us-west-1.wasabisys.com
# eu-central-1: https://s3.eu-central-1.wasabisys.com
# ap-northeast-1: https://s3.ap-northeast-1.wasabisys.com

# Migration Control (for gradual rollout)
STORAGE_MIGRATION_MODE=dual_write
# Options: do_spaces_only, dual_write, wasabi_only
```

### Phase 3: Implement Dual-Write Strategy

#### 3.1 Create Storage Migration Service

```bash
php artisan make:class Domain/Storage/Services/StorageMigrationService
```

Create `app/Domain/Storage/Services/StorageMigrationService.php`:

```php
<?php

namespace App\Domain\Storage\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class StorageMigrationService
{
    protected string $mode;
    protected string $primaryDisk;
    protected string $secondaryDisk;

    public function __construct()
    {
        $this->mode = config('storage.migration_mode', 'do_spaces_only');
        $this->primaryDisk = $this->mode === 'wasabi_only' ? 'wasabi' : 'do_spaces';
        $this->secondaryDisk = $this->mode === 'dual_write' ? 
            ($this->primaryDisk === 'do_spaces' ? 'wasabi' : 'do_spaces') : null;
    }

    /**
     * Store file with dual-write support
     */
    public function store(UploadedFile $file, string $path, array $options = []): string
    {
        // Store on primary disk
        $storedPath = Storage::disk($this->primaryDisk)->putFile($path, $file, $options);

        // Also store on secondary disk if in dual-write mode
        if ($this->mode === 'dual_write' && $this->secondaryDisk) {
            try {
                Storage::disk($this->secondaryDisk)->putFileAs(
                    dirname($storedPath),
                    $file,
                    basename($storedPath),
                    $options
                );
            } catch (\Exception $e) {
                // Log but don't fail - secondary write is best-effort
                \Log::warning("Secondary disk write failed: {$e->getMessage()}");
            }
        }

        return $storedPath;
    }

    /**
     * Get file URL with fallback support
     */
    public function url(string $path): string
    {
        // Try primary disk
        if (Storage::disk($this->primaryDisk)->exists($path)) {
            return Storage::disk($this->primaryDisk)->url($path);
        }

        // Fallback to secondary disk in dual-write mode
        if ($this->mode === 'dual_write' && $this->secondaryDisk) {
            if (Storage::disk($this->secondaryDisk)->exists($path)) {
                return Storage::disk($this->secondaryDisk)->url($path);
            }
        }

        // Fallback to old disk if migrating
        if ($this->mode === 'wasabi_only' && Storage::disk('do_spaces')->exists($path)) {
            return Storage::disk('do_spaces')->url($path);
        }

        throw new \RuntimeException("File not found: {$path}");
    }

    /**
     * Check if file exists on any disk
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->primaryDisk)->exists($path) ||
               ($this->secondaryDisk && Storage::disk($this->secondaryDisk)->exists($path)) ||
               ($this->mode === 'wasabi_only' && Storage::disk('do_spaces')->exists($path));
    }

    /**
     * Delete file from all disks
     */
    public function delete(string $path): bool
    {
        $deleted = Storage::disk($this->primaryDisk)->delete($path);

        if ($this->secondaryDisk) {
            Storage::disk($this->secondaryDisk)->delete($path);
        }

        // Clean up from old disk too
        if ($this->mode === 'wasabi_only') {
            Storage::disk('do_spaces')->delete($path);
        }

        return $deleted;
    }

    /**
     * Get current migration mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get primary disk name
     */
    public function getPrimaryDisk(): string
    {
        return $this->primaryDisk;
    }
}
```

#### 3.2 Add Storage Configuration

Create `config/storage.php`:

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Migration Configuration
    |--------------------------------------------------------------------------
    */

    'migration_mode' => env('STORAGE_MIGRATION_MODE', 'do_spaces_only'),

    'disks' => [
        'primary' => env('STORAGE_PRIMARY_DISK', 'do_spaces'),
        'secondary' => env('STORAGE_SECONDARY_DISK', 'wasabi'),
    ],
];
```

### Phase 4: Data Migration

#### 4.1 Create Migration Command

```bash
php artisan make:command Storage:MigrateToWasabi
```

Create `app/Console/Commands/MigrateStorageToWasabi.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToWasabi extends Command
{
    protected $signature = 'storage:migrate-to-wasabi 
                            {--path= : Specific path to migrate}
                            {--batch-size=100 : Number of files per batch}
                            {--dry-run : Run without actually copying}
                            {--verify : Verify copied files}';

    protected $description = 'Migrate files from DigitalOcean Spaces to Wasabi';

    public function handle()
    {
        $path = $this->option('path') ?? '';
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');
        $verify = $this->option('verify');

        $this->info("Starting migration from DigitalOcean Spaces to Wasabi...");
        
        if ($dryRun) {
            $this->warn("DRY RUN MODE - No files will be copied");
        }

        $sourceDisk = Storage::disk('do_spaces');
        $targetDisk = Storage::disk('wasabi');

        // Get all files
        $files = $sourceDisk->allFiles($path);
        $total = count($files);
        $this->info("Found {$total} files to migrate");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $migrated = 0;
        $errors = 0;

        foreach (array_chunk($files, $batchSize) as $batch) {
            foreach ($batch as $file) {
                try {
                    // Check if already exists on target
                    if ($targetDisk->exists($file)) {
                        if ($verify) {
                            // Verify file sizes match
                            $sourceSize = $sourceDisk->size($file);
                            $targetSize = $targetDisk->size($file);
                            
                            if ($sourceSize !== $targetSize) {
                                $this->warn("\nSize mismatch for {$file}: source={$sourceSize}, target={$targetSize}");
                                $errors++;
                            }
                        }
                        $bar->advance();
                        continue;
                    }

                    if (!$dryRun) {
                        // Copy file
                        $content = $sourceDisk->get($file);
                        $targetDisk->put($file, $content, [
                            'visibility' => 'public',
                            'CacheControl' => 'max-age=31536000',
                        ]);

                        if ($verify) {
                            // Verify the copy
                            $sourceSize = $sourceDisk->size($file);
                            $targetSize = $targetDisk->size($file);
                            
                            if ($sourceSize !== $targetSize) {
                                throw new \Exception("Verification failed: size mismatch");
                            }
                        }
                    }

                    $migrated++;
                } catch (\Exception $e) {
                    $this->error("\nFailed to migrate {$file}: {$e->getMessage()}");
                    $errors++;
                }

                $bar->advance();
            }

            // Small delay to avoid rate limiting
            usleep(100000); // 0.1 seconds
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Migration completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Files', $total],
                ['Successfully Migrated', $migrated],
                ['Errors', $errors],
            ]
        );

        return $errors === 0 ? 0 : 1;
    }
}
```

#### 4.2 Run Migration

```bash
# Test with dry run first
php artisan storage:migrate-to-wasabi --dry-run

# Migrate specific path
php artisan storage:migrate-to-wasabi --path=images --verify

# Full migration with verification
php artisan storage:migrate-to-wasabi --verify --batch-size=50
```

### Phase 5: Update Application Code

#### 5.1 Update File Upload Services

Replace direct disk usage with `StorageMigrationService`:

```php
// Before
Storage::disk('do_spaces')->put($path, $file);

// After
app(StorageMigrationService::class)->store($file, $path);
```

#### 5.2 Update File URL Generation

```php
// Before
Storage::disk('do_spaces')->url($path);

// After
app(StorageMigrationService::class)->url($path);
```

### Phase 6: Gradual Rollout

#### 6.1 Phase A: Dual Write (Week 1-2)

```env
STORAGE_MIGRATION_MODE=dual_write
```

- All new files written to both DO Spaces and Wasabi
- Reads still from DO Spaces (primary)
- Monitor for any issues
- Run data migration command to sync existing files

#### 6.2 Phase B: Switch Reads (Week 3)

```env
STORAGE_MIGRATION_MODE=dual_write
STORAGE_PRIMARY_DISK=wasabi
```

- New files still written to both
- Reads now from Wasabi (with DO fallback)
- Monitor performance and error rates

#### 6.3 Phase C: Wasabi Only (Week 4+)

```env
STORAGE_MIGRATION_MODE=wasabi_only
FILESYSTEM_DISK=wasabi
```

- All operations on Wasabi
- DO Spaces kept as backup (read-only fallback)
- Verify everything works

#### 6.4 Phase D: Decommission DO Spaces (Month 2+)

Once confident:
1. Download final backup from DO Spaces
2. Cancel DO Spaces subscription
3. Remove DO Spaces configuration from code

### Phase 7: Update GrowStream Video Provider

Update `config/growstream.php`:

```php
'providers' => [
    'wasabi' => [
        'key' => env('WASABI_ACCESS_KEY_ID'),
        'secret' => env('WASABI_SECRET_ACCESS_KEY'),
        'region' => env('WASABI_DEFAULT_REGION'),
        'bucket' => env('WASABI_BUCKET'),
        'endpoint' => env('WASABI_ENDPOINT'),
        'cdn_endpoint' => env('WASABI_URL'),
    ],
    // Keep digitalocean for backward compatibility during migration
    'digitalocean' => [
        // ... existing config ...
    ],
],

'default_provider' => env('VIDEO_STORAGE_PROVIDER', 'wasabi'),
```

Update `app/Domain/GrowStream/Infrastructure/Providers/WasabiProvider.php`:

```php
<?php

namespace App\Domain\GrowStream\Infrastructure\Providers;

use App\Domain\GrowStream\Contracts\VideoProviderInterface;
use App\Domain\GrowStream\DTOs\ProviderVideoResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class WasabiProvider implements VideoProviderInterface
{
    protected string $disk = 'wasabi';

    public function upload(UploadedFile $file, array $metadata = []): ProviderVideoResponse
    {
        $path = 'videos/' . uniqid() . '.' . $file->getClientOriginalExtension();
        
        Storage::disk($this->disk)->putFileAs(
            dirname($path),
            $file,
            basename($path),
            ['visibility' => 'public']
        );

        return new ProviderVideoResponse(
            success: true,
            url: Storage::disk($this->disk)->url($path),
            path: $path,
            provider: 'wasabi',
            metadata: array_merge($metadata, [
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ])
        );
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function url(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}
```

---

## Testing Checklist

### Before Migration

- [ ] Backup all DigitalOcean Spaces data
- [ ] Test Wasabi bucket accessibility
- [ ] Verify credentials work
- [ ] Test file upload/download from Wasabi
- [ ] Review current storage usage patterns

### During Migration

- [ ] Monitor error logs
- [ ] Check file upload success rates
- [ ] Verify file URLs are accessible
- [ ] Test image/video playback
- [ ] Monitor application performance
- [ ] Check CDN/caching behavior

### After Migration

- [ ] Verify all files accessible
- [ ] Test all file upload features
- [ ] Check user-facing features (profile images, uploads, etc.)
- [ ] Verify GrowStream video playback
- [ ] Monitor costs (should decrease)
- [ ] Performance benchmarks
- [ ] Remove DO Spaces credentials (after confirmation period)

---

## Rollback Plan

If issues occur during migration:

### Quick Rollback (Dual-Write Mode)

```env
# Switch back to DO Spaces as primary
STORAGE_MIGRATION_MODE=dual_write
STORAGE_PRIMARY_DISK=do_spaces
```

### Full Rollback (Before Phase C)

```env
STORAGE_MIGRATION_MODE=do_spaces_only
FILESYSTEM_DISK=do_spaces
```

### Emergency Rollback (After Phase C)

1. Change environment variables back to DO Spaces
2. Deploy immediately
3. Re-sync any new files from Wasabi to DO Spaces

---

## Cost Comparison

### DigitalOcean Spaces
- $5/month for 250GB storage
- $0.01/GB for storage above 250GB
- $0.01/GB for bandwidth (outbound)

### Wasabi
- $6.99/month per TB (first TB)
- $0.0059/GB/month ($5.99/TB)
- **No egress fees** (huge savings!)
- Minimum storage duration: 90 days

### Example Calculation (500GB storage, 1TB/month bandwidth)

**DigitalOcean Spaces:**
- Storage: $5 + (250GB × $0.01) = $7.50
- Bandwidth: 1000GB × $0.01 = $10.00
- **Total: $17.50/month**

**Wasabi:**
- Storage: 500GB × $0.0059 = $2.95
- Bandwidth: $0.00
- **Total: $2.95/month**

**Savings: $14.55/month (83% reduction!)**

---

## Monitoring & Alerts

### Key Metrics to Monitor

1. **Upload Success Rate**: Should remain >99%
2. **File Access Errors**: Should remain <0.1%
3. **Response Times**: Should be comparable or better
4. **Storage Costs**: Should decrease after full migration
5. **Bandwidth Usage**: Track egress savings

### Recommended Alerts

```php
// Monitor storage operations
if ($errorRate > 1) {
    alert('Storage error rate elevated');
}

if ($uploadFailures > 10) {
    alert('Multiple upload failures detected');
}
```

---

## Common Issues & Solutions

### Issue: Slow Upload Speeds

**Solution:** Choose Wasabi region closest to your application servers.

### Issue: CORS Errors

**Solution:** Verify CORS configuration on Wasabi bucket.

### Issue: Files Not Accessible

**Solution:** Check bucket policy and file permissions (should be public).

### Issue: Different URLs

**Solution:** Use the StorageMigrationService which handles URL generation with fallbacks.

### Issue: Missing Files After Migration

**Solution:** Run verification command:
```bash
php artisan storage:migrate-to-wasabi --verify --path=images
```

---

## Support & Resources

- **Wasabi Documentation**: https://docs.wasabi.com/
- **Wasabi Support**: support@wasabi.com
- **AWS S3 SDK Documentation**: https://docs.aws.amazon.com/sdk-for-php/
- **Laravel Storage Documentation**: https://laravel.com/docs/filesystem

---

## Migration Timeline (Recommended)

| Week | Phase | Actions |
|------|-------|---------|
| 1 | Setup | Create Wasabi account, configure Laravel, test uploads |
| 2 | Dual Write | Enable dual-write mode, migrate existing files |
| 3 | Switch Reads | Make Wasabi primary for reads, monitor closely |
| 4 | Testing | Extensive testing, performance monitoring |
| 5-8 | Stabilization | Monitor, fix any issues, verify all features work |
| 9+ | Decommission | Remove DO Spaces, clean up code |

---

## Conclusion

This migration can save significant costs while maintaining or improving performance. The key is to:

1. **Test thoroughly** before going live
2. **Migrate gradually** using dual-write strategy
3. **Monitor closely** during transition
4. **Keep backups** until fully confident
5. **Have rollback plan** ready

The hybrid approach with dual-write gives you the flexibility to move at your own pace with minimal risk.
