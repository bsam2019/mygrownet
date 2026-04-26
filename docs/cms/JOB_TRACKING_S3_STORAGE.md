# Job Tracking S3 Storage Implementation

**Date:** April 19, 2026  
**Status:** ✅ Complete

## Overview

Updated the CMS file upload system to use DigitalOcean Spaces (S3-compatible storage) instead of local storage. This ensures all uploaded files (job attachments, customer documents, company logos, and expense receipts) are stored in cloud storage for better scalability, reliability, and accessibility.

## Changes Made

### 1. Job Attachments (`JobController.php`)

**File:** `app/Http/Controllers/CMS/JobController.php`

**Before:**
```php
$path = $file->store("cms/companies/{$companyId}/jobs/{$job->id}/attachments", 'public');
$job->attachments()->create([
    'file_path' => '/storage/' . $path,
    // ...
]);
```

**After:**
```php
// Generate S3 key with UUID
$s3Key = "cms/companies/{$companyId}/jobs/{$job->id}/attachments/{$uuid}_{$sanitizedFilename}";

// Store to S3 (DigitalOcean Spaces)
Storage::disk('s3')->put($s3Key, file_get_contents($file->getRealPath()), [
    'ContentType' => $file->getClientMimeType(),
    'visibility' => 'private',
]);

$job->attachments()->create([
    'file_path' => $s3Key, // Store S3 key instead of local path
    // ...
]);
```

### 2. Job Attachment Model (`JobAttachmentModel.php`)

**File:** `app/Infrastructure/Persistence/Eloquent/CMS/JobAttachmentModel.php`

**Added:**
- `download_url` accessor that generates presigned URLs for S3 files
- Backward compatibility for old local files (checks if path starts with `/storage/`)
- Presigned URLs valid for 1 hour

```php
protected $appends = ['download_url'];

public function getDownloadUrlAttribute(): string
{
    // If old local file, return as-is
    if (str_starts_with($this->file_path, '/storage/')) {
        return $this->file_path;
    }

    // Generate presigned URL for S3 files
    return Storage::disk('s3')->temporaryUrl($this->file_path, now()->addHour());
}
```

### 3. Customer Documents (`CustomerController.php`)

**File:** `app/Http/Controllers/CMS/CustomerController.php`

**Before:**
```php
$path = $file->store("cms/companies/{$companyId}/customers/{$customer->id}/documents", 'public');
$customer->documents()->create([
    'file_path' => '/storage/' . $path,
    // ...
]);
```

**After:**
```php
// Generate S3 key with UUID
$s3Key = "cms/companies/{$companyId}/customers/{$customer->id}/documents/{$uuid}_{$sanitizedFilename}";

// Store to S3
Storage::disk('s3')->put($s3Key, file_get_contents($file->getRealPath()), [
    'ContentType' => $file->getClientMimeType(),
    'visibility' => 'private',
]);

$customer->documents()->create([
    'file_path' => $s3Key,
    // ...
]);
```

### 4. Customer Document Model (`CustomerDocumentModel.php`)

**File:** `app/Infrastructure/Persistence/Eloquent/CMS/CustomerDocumentModel.php`

**Added:**
- `download_url` accessor (same pattern as JobAttachmentModel)
- Backward compatibility for old local files

### 5. Company Logo (`CompanySettingsService.php`)

**File:** `app/Domain/CMS/Core/Services/CompanySettingsService.php`

**Before:**
```php
$path = $file->store('cms/logos', 'public');
$company->update(['logo_path' => $path]);
```

**After:**
```php
// Generate S3 key with UUID
$s3Key = "cms/companies/{$companyId}/logo/{$uuid}_{$sanitizedFilename}";

// Store to S3 with public visibility (for logo display)
Storage::disk('s3')->put($s3Key, file_get_contents($file->getRealPath()), [
    'ContentType' => $file->getClientMimeType(),
    'visibility' => 'public',
]);

$company->update(['logo_path' => $s3Key]);
```

### 6. Company Model (`CompanyModel.php`)

**File:** `app/Infrastructure/Persistence/Eloquent/CMS/CompanyModel.php`

**Added:**
- `logo_url` accessor that generates public URLs for S3 logos
- Backward compatibility for old local files

```php
protected $appends = ['logo_url'];

public function getLogoUrlAttribute(): ?string
{
    if (!$this->logo_path) {
        return null;
    }

    // If old local file
    if (str_starts_with($this->logo_path, 'cms/logos/')) {
        return asset('storage/' . $this->logo_path);
    }

    // Generate public URL for S3 files
    return Storage::disk('s3')->url($this->logo_path);
}
```

### 7. Vue Component Updates

**Files Updated:**
- `resources/js/pages/CMS/Jobs/Show.vue` - Use `download_url` for attachments
- `resources/js/pages/CMS/Customers/Show.vue` - Use `download_url` for documents
- `resources/js/Layouts/CMSLayout.vue` - Use `logo_url` for company logo (2 places)
- `resources/js/pages/CMS/Settings/Index.vue` - Use `logo_url` for logo preview

**Before:**
```vue
<a :href="attachment.file_path" target="_blank">View</a>
<img :src="`/storage/${company.logo_path}`" />
```

**After:**
```vue
<a :href="attachment.download_url" target="_blank">View</a>
<img :src="company.logo_url" />
```

## Storage Pattern

All CMS file uploads now follow this pattern:

1. **Generate S3 Key:**
   ```php
   $uuid = \Illuminate\Support\Str::uuid();
   $sanitizedFilename = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($filename));
   $s3Key = "cms/companies/{$companyId}/{context}/{$uuid}_{$sanitizedFilename}";
   ```

2. **Upload to S3:**
   ```php
   Storage::disk('s3')->put($s3Key, file_get_contents($file->getRealPath()), [
       'ContentType' => $file->getClientMimeType(),
       'visibility' => 'private', // or 'public' for logos
   ]);
   ```

3. **Store S3 Key in Database:**
   ```php
   $model->update(['file_path' => $s3Key]);
   ```

4. **Generate URLs on Access:**
   - **Private files:** Presigned URLs (valid for 1 hour)
   - **Public files:** Direct S3 URLs

## Backward Compatibility

All models check if the file path is an old local path and handle it appropriately:

```php
if (str_starts_with($this->file_path, '/storage/') || str_starts_with($this->file_path, 'cms/logos/')) {
    // Handle old local file
    return asset('storage/' . $this->file_path);
}

// Handle new S3 file
return Storage::disk('s3')->temporaryUrl($this->file_path, now()->addHour());
```

## Files Already Using S3

**Expense Receipts** were already correctly using S3 storage via `ExpenseService::uploadReceipt()` method. No changes needed.

## S3 Configuration

The system uses the `s3` disk configured in `config/filesystems.php`:

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'), // DigitalOcean Spaces endpoint
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
],
```

## Benefits

1. **Scalability:** No local disk space limitations
2. **Reliability:** Cloud storage with redundancy
3. **Performance:** CDN-backed delivery
4. **Security:** Presigned URLs for private files
5. **Portability:** Files accessible from any server instance
6. **Backup:** Automatic backups via DigitalOcean Spaces

## Testing Checklist

- [ ] Upload job attachment - verify stored in S3
- [ ] View job attachment - verify presigned URL works
- [ ] Upload customer document - verify stored in S3
- [ ] View customer document - verify presigned URL works
- [ ] Upload company logo - verify stored in S3
- [ ] View company logo in sidebar - verify public URL works
- [ ] Upload expense receipt - verify already using S3
- [ ] Test with old local files - verify backward compatibility

## Migration Notes

Existing files in local storage will continue to work due to backward compatibility checks. To migrate old files to S3:

1. Create a migration command that:
   - Reads all records with local file paths
   - Uploads files to S3
   - Updates database records with S3 keys
   - Optionally deletes local files

2. Run migration during maintenance window

## Related Files

- `app/Application/Storage/UseCases/UploadFileUseCase.php` - GrowStorage reference implementation
- `app/Infrastructure/Storage/S3/S3StorageService.php` - S3 service methods
- `config/filesystems.php` - S3 disk configuration

---

**Implementation Complete:** All CMS file uploads now use DigitalOcean Spaces (S3) storage following the same pattern as GrowStorage.
